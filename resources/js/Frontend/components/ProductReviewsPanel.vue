<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import StarRating from '@/Frontend/components/StarRating.vue'

type ReviewItem = {
  id: number | string
  rating: number | null
  customer_name: string | null
  short_description: string | null
  long_description: string | null
  image_urls: string[]
  created_at: string | null
  created_at_iso: string | null
}

type Summary = {
  reviews_count: number
  avg_rating: number | null
}

type Cached = {
  summary: Summary
  reviews: ReviewItem[]
}

const cache = new Map<string, Cached>()
const inflight = new Map<string, Promise<Cached>>()

const props = withDefaults(
  defineProps<{
    fetchUrl: string
    active: boolean
    initialCount?: number
    initialAvg?: number | null
  }>(),
  {
    initialCount: 0,
    initialAvg: null,
  }
)

const loading = ref(false)
const loaded = ref(false)
const error = ref<string | null>(null)
const sort = ref<'recent' | 'highest' | 'lowest'>('recent')

const summary = ref<Summary>({
  reviews_count: props.initialCount ?? 0,
  avg_rating: props.initialAvg ?? null,
})

const reviews = ref<ReviewItem[]>([])

const sortedReviews = computed(() => {
  const list = [...reviews.value]

  if (sort.value === 'highest') {
    return list.sort((a, b) => (b.rating ?? -1) - (a.rating ?? -1))
  }

  if (sort.value === 'lowest') {
    return list.sort((a, b) => (a.rating ?? 999) - (b.rating ?? 999))
  }

  return list.sort((a, b) => {
    const aIso = a.created_at_iso || ''
    const bIso = b.created_at_iso || ''
    return bIso.localeCompare(aIso)
  })
})

async function load() {
  if (!props.fetchUrl) return
  if (loaded.value || loading.value) return

  if (cache.has(props.fetchUrl)) {
    const cached = cache.get(props.fetchUrl)!
    summary.value = cached.summary
    reviews.value = cached.reviews
    loaded.value = true
    return
  }

  if (inflight.has(props.fetchUrl)) {
    try {
      loading.value = true
      const cached = await inflight.get(props.fetchUrl)!
      summary.value = cached.summary
      reviews.value = cached.reviews
      loaded.value = true
    } catch (e) {
      error.value = 'Failed to load reviews. Please try again.'
    } finally {
      loading.value = false
    }

    return
  }

  loading.value = true
  error.value = null

  const promise = fetch(props.fetchUrl, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
    .then(async (res) => {
      if (!res.ok) throw new Error('Failed')
      const data = await res.json()
      const next: Cached = {
        summary: {
          reviews_count: Number(data?.summary?.reviews_count ?? 0),
          avg_rating:
            data?.summary?.avg_rating === null || typeof data?.summary?.avg_rating === 'undefined'
              ? null
              : Number(data?.summary?.avg_rating),
        },
        reviews: Array.isArray(data?.reviews) ? data.reviews : [],
      }
      return next
    })

  inflight.set(props.fetchUrl, promise)

  try {
    const next = await promise
    cache.set(props.fetchUrl, next)
    summary.value = next.summary
    reviews.value = next.reviews
    loaded.value = true
  } catch (e) {
    console.error('Reviews load error:', e)
    error.value = 'Failed to load reviews. Please try again.'
  } finally {
    inflight.delete(props.fetchUrl)
    loading.value = false
  }
}

function retry() {
  loaded.value = false
  cache.delete(props.fetchUrl)
  inflight.delete(props.fetchUrl)
  load()
}

watch(
  () => props.active,
  (active) => {
    if (active) {
      load()
    }
  },
  { immediate: true }
)

watch(
  () => props.fetchUrl,
  () => {
    loaded.value = false
    error.value = null
    reviews.value = []
    summary.value = {
      reviews_count: props.initialCount ?? 0,
      avg_rating: props.initialAvg ?? null,
    }

    if (props.active) {
      load()
    }
  }
)
</script>

<template>
  <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white">
    <div class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <div>
        <div class="flex items-center gap-2">
          <StarRating
            :rating="summary.avg_rating"
            :count="summary.reviews_count"
            :showCount="false"
            size="md"
          />
          <span class="text-sm text-slate-500">
            {{ summary.reviews_count }} review{{ summary.reviews_count === 1 ? '' : 's' }}
          </span>
        </div>
        <div class="mt-1 text-sm text-slate-500">
          Customer reviews
        </div>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-sm text-slate-500">Sort:</span>
        <select
          v-model="sort"
          class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-900"
        >
          <option value="recent">Most Recent</option>
          <option value="highest">Highest Rating</option>
          <option value="lowest">Lowest Rating</option>
        </select>
      </div>
    </div>

    <div class="px-4 py-5 sm:px-6">
      <div v-if="loading" class="space-y-4">
        <div v-for="n in 3" :key="`review-skel-${n}`" class="space-y-3 rounded-2xl border border-slate-200 p-4">
          <div class="h-4 w-32 animate-pulse rounded bg-slate-100" />
          <div class="h-4 w-2/3 animate-pulse rounded bg-slate-100" />
          <div class="h-4 w-full animate-pulse rounded bg-slate-100" />
        </div>
      </div>

      <div
        v-else-if="error"
        class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
      >
        <div class="font-semibold">Failed to load reviews</div>
        <div class="mt-1 text-red-600">{{ error }}</div>
        <button
          type="button"
          class="mt-3 inline-flex items-center rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
          @click="retry"
        >
          Retry
        </button>
      </div>

      <div v-else-if="!sortedReviews.length" class="py-10 text-center text-sm text-slate-500">
        No reviews yet.
      </div>

      <div v-else class="divide-y divide-slate-200">
        <article v-for="review in sortedReviews" :key="review.id" class="py-6 first:pt-0 last:pb-0">
          <div class="flex items-start justify-between gap-4">
            <StarRating :rating="review.rating" :showValue="false" size="md" />
            <div class="text-xs text-slate-400">
              {{ review.created_at || '' }}
            </div>
          </div>

          <div class="mt-3 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500">
              <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true">
                <path
                  d="M12 12c2.761 0 5-2.239 5-5S14.761 2 12 2 7 4.239 7 7s2.239 5 5 5zm0 2c-4.418 0-8 2.239-8 5v1c0 .552.448 1 1 1h14c.552 0 1-.448 1-1v-1c0-2.761-3.582-5-8-5z"
                />
              </svg>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <div class="font-semibold text-slate-900">
                {{ review.customer_name || 'Anonymous' }}
              </div>
              <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                Customer
              </span>
            </div>
          </div>

          <div v-if="review.short_description" class="mt-3 text-sm font-semibold text-slate-900">
            {{ review.short_description }}
          </div>

          <div v-if="review.long_description" class="mt-1 whitespace-pre-line text-sm leading-7 text-slate-600">
            {{ review.long_description }}
          </div>

          <div v-if="review.image_urls?.length" class="mt-4 flex flex-wrap gap-2">
            <img
              v-for="(url, idx) in review.image_urls"
              :key="`${review.id}-img-${idx}`"
              :src="url"
              alt="Review image"
              class="h-16 w-16 rounded-2xl border border-slate-200 object-cover"
              loading="lazy"
            />
          </div>
        </article>
      </div>
    </div>
  </div>
</template>

