<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import StarRating from '@/Frontend/components/StarRating.vue'

const PAGE_SIZE = 4

type SortKey = 'recent' | 'highest' | 'lowest'

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

type PageData = {
  summary: Summary
  reviews: ReviewItem[]
}

const cache = new Map<string, Cached>()
const inflight = new Map<string, Promise<PageData>>()

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
const loadingMore = ref(false)
const loaded = ref(false)
const error = ref<string | null>(null)
const errorScope = ref<'initial' | 'more' | null>(null)

const sort = ref<SortKey>('recent')
const sortOpen = ref(false)
const sortDropdownRef = ref<HTMLElement | null>(null)

const summary = ref<Summary>({
  reviews_count: props.initialCount ?? 0,
  avg_rating: props.initialAvg ?? null,
})

const reviews = ref<ReviewItem[]>([])

const cacheKey = computed(() => {
  if (!props.fetchUrl) return ''
  return `${props.fetchUrl}::${sort.value}`
})

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

const canLoadMore = computed(() => {
  return summary.value.reviews_count > reviews.value.length
})

const remainingReviews = computed(() => {
  return Math.max(0, summary.value.reviews_count - reviews.value.length)
})

const nextLoadCount = computed(() => {
  return Math.min(PAGE_SIZE, remainingReviews.value)
})

const sortLabel = computed(() => {
  if (sort.value === 'highest') return 'Highest rating'
  if (sort.value === 'lowest') return 'Lowest rating'
  return 'Most recent'
})

function normalizeSummary(raw: any): Summary {
  return {
    reviews_count: Number(raw?.reviews_count ?? 0),
    avg_rating: raw?.avg_rating === null || typeof raw?.avg_rating === 'undefined' ? null : Number(raw?.avg_rating),
  }
}

function normalizeReview(raw: any): ReviewItem {
  return {
    id: raw?.id ?? '',
    rating: raw?.rating === null || typeof raw?.rating === 'undefined' ? null : Number(raw?.rating),
    customer_name: raw?.customer_name ?? null,
    short_description: raw?.short_description ?? null,
    long_description: raw?.long_description ?? null,
    image_urls: Array.isArray(raw?.image_urls) ? raw.image_urls.map(String).filter(Boolean) : [],
    created_at: raw?.created_at ?? null,
    created_at_iso: raw?.created_at_iso ?? null,
  }
}

function buildPagedUrl(fetchUrl: string, sortKey: SortKey, offset: number) {
  const [path, queryString] = fetchUrl.split('?')
  const params = new URLSearchParams(queryString || '')

  params.set('sort', sortKey)
  params.set('limit', String(PAGE_SIZE))
  params.set('offset', String(offset))

  const query = params.toString()
  return query ? `${path}?${query}` : path
}

function applyCached(next: Cached) {
  summary.value = next.summary
  reviews.value = next.reviews
  loaded.value = true
}

function syncFromCache() {
  const key = cacheKey.value
  if (!key) return
  const cached = cache.get(key)
  if (!cached) return
  if (loaded.value && cached.reviews.length === reviews.value.length) return
  applyCached(cached)
}

function mergeReviews(existing: ReviewItem[], incoming: ReviewItem[]) {
  const seen = new Set(existing.map((item) => String(item.id)))
  const merged = [...existing]

  for (const item of incoming) {
    const id = String(item.id)
    if (seen.has(id)) continue
    seen.add(id)
    merged.push(item)
  }

  return merged
}

async function fetchPage(fetchUrl: string, sortKey: SortKey, offset: number) {
  const key = `${fetchUrl}::${sortKey}`
  const requestKey = `${key}::${offset}`

  if (inflight.has(requestKey)) {
    return await inflight.get(requestKey)!
  }

  const url = buildPagedUrl(fetchUrl, sortKey, offset)

  const promise = fetch(url, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  }).then(async (res) => {
    if (!res.ok) throw new Error('Failed')
    const data = await res.json()
    return {
      summary: normalizeSummary(data?.summary),
      reviews: Array.isArray(data?.reviews) ? data.reviews.map(normalizeReview) : [],
    } satisfies PageData
  })

  inflight.set(requestKey, promise)

  try {
    return await promise
  } finally {
    inflight.delete(requestKey)
  }
}

async function loadInitial() {
  if (!props.fetchUrl) return
  if (loaded.value || loading.value) return

  const fetchUrl = props.fetchUrl
  const sortKey = sort.value
  const key = `${fetchUrl}::${sortKey}`

  if (cache.has(key)) {
    applyCached(cache.get(key)!)
    return
  }

  loading.value = true
  error.value = null
  errorScope.value = null

  try {
    const page = await fetchPage(fetchUrl, sortKey, 0)
    const next: Cached = { summary: page.summary, reviews: page.reviews }
    cache.set(key, next)

    if (cacheKey.value !== key) return
    applyCached(next)
  } catch (e) {
    console.error('Reviews load error:', e)
    error.value = 'Failed to load reviews. Please try again.'
    errorScope.value = 'initial'
  } finally {
    loading.value = false
  }
}

async function loadMore() {
  if (!props.fetchUrl) return
  if (!loaded.value) return
  if (!canLoadMore.value) return
  if (loading.value || loadingMore.value) return

  syncFromCache()
  if (!canLoadMore.value) return

  const fetchUrl = props.fetchUrl
  const sortKey = sort.value
  const key = `${fetchUrl}::${sortKey}`
  const offset = reviews.value.length

  loadingMore.value = true
  error.value = null
  errorScope.value = null

  try {
    const page = await fetchPage(fetchUrl, sortKey, offset)
    const merged = mergeReviews(reviews.value, page.reviews)
    const next: Cached = {
      summary: page.summary,
      reviews: merged,
    }

    cache.set(key, next)

    if (cacheKey.value !== key) return
    applyCached(next)
  } catch (e) {
    console.error('Reviews load more error:', e)
    error.value = 'Failed to load more reviews. Please try again.'
    errorScope.value = 'more'
  } finally {
    loadingMore.value = false
  }
}

function clearCacheForFetchUrl(fetchUrl: string) {
  for (const key of cache.keys()) {
    if (key.startsWith(`${fetchUrl}::`)) {
      cache.delete(key)
    }
  }

  for (const key of inflight.keys()) {
    if (key.startsWith(`${fetchUrl}::`)) {
      inflight.delete(key)
    }
  }
}

function retryInitial() {
  if (!props.fetchUrl) return

  loaded.value = false
  error.value = null
  errorScope.value = null
  reviews.value = []

  clearCacheForFetchUrl(props.fetchUrl)
  loadInitial()
}

function retryMore() {
  error.value = null
  errorScope.value = null
  loadMore()
}

function selectSort(next: SortKey) {
  sort.value = next
  sortOpen.value = false
}

function onGlobalPointerDown(event: PointerEvent) {
  if (!sortOpen.value) return
  const root = sortDropdownRef.value
  const target = event.target as Node | null
  if (!root || !target) return
  if (root.contains(target)) return
  sortOpen.value = false
}

function onGlobalKeydown(event: KeyboardEvent) {
  if (event.key !== 'Escape') return
  if (!sortOpen.value) return
  sortOpen.value = false
}

onMounted(() => {
  window.addEventListener('pointerdown', onGlobalPointerDown)
  window.addEventListener('keydown', onGlobalKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('pointerdown', onGlobalPointerDown)
  window.removeEventListener('keydown', onGlobalKeydown)
})

watch(
  () => props.active,
  (active) => {
    if (active) {
      loadInitial()
    }
  },
  { immediate: true }
)

watch(
  () => props.fetchUrl,
  () => {
    loaded.value = false
    loading.value = false
    loadingMore.value = false
    sortOpen.value = false
    error.value = null
    errorScope.value = null
    reviews.value = []
    summary.value = {
      reviews_count: props.initialCount ?? 0,
      avg_rating: props.initialAvg ?? null,
    }

    if (props.active) {
      loadInitial()
    }
  }
)

watch(
  () => sort.value,
  () => {
    sortOpen.value = false
    loaded.value = false
    loading.value = false
    loadingMore.value = false
    error.value = null
    errorScope.value = null
    reviews.value = []

    if (props.active) {
      loadInitial()
    }
  }
)
</script>

<template>
  <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-white">
    <div
      class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
    >
      <div>
        <div class="flex items-center gap-2">
          <StarRating :rating="summary.avg_rating" :count="summary.reviews_count" :showCount="false" size="md" />
          <span class="text-sm text-slate-500">
            {{ summary.reviews_count }} review{{ summary.reviews_count === 1 ? '' : 's' }}
          </span>
        </div>
        <div class="mt-1 text-sm text-slate-500">
          Customer reviews
        </div>
      </div>

      <div class="flex items-center gap-2">
        <!-- <span class="text-sm text-slate-500">Sort:</span> -->

        <div ref="sortDropdownRef" class="relative">
          <!-- <button
            type="button"
            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-950/10 disabled:cursor-not-allowed disabled:opacity-60"
            :class="sortOpen ? 'border-slate-900' : 'border-slate-200'"
            aria-haspopup="listbox"
            :aria-expanded="sortOpen"
            :disabled="loading || loadingMore"
            @click="sortOpen = !sortOpen"
          >
            <span class="whitespace-nowrap">{{ sortLabel }}</span>
            <svg
              viewBox="0 0 20 20"
              fill="currentColor"
              class="h-4 w-4 text-slate-500 transition-transform duration-200"
              :class="sortOpen ? 'rotate-180' : ''"
              aria-hidden="true"
            >
              <path
                fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z"
                clip-rule="evenodd"
              />
            </svg>
          </button> -->

          <!-- <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-120 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
          >
            <div
              v-if="sortOpen"
              class="absolute right-0 z-20 mt-2 w-56 origin-top-right overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg ring-1 ring-black/5"
              role="listbox"
            >
              <button
                type="button"
                class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm transition hover:bg-slate-50"
                :class="sort === 'recent' ? 'bg-slate-50 font-semibold text-slate-950' : 'text-slate-700'"
                @click="selectSort('recent')"
              >
                <span>Most recent</span>
                <svg
                  v-if="sort === 'recent'"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  class="h-4 w-4 text-slate-900"
                  aria-hidden="true"
                >
                  <path
                    fill-rule="evenodd"
                    d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.07 7.07a1 1 0 0 1-1.414 0l-3.535-3.535a1 1 0 1 1 1.414-1.414l2.828 2.828 6.363-6.364a1 1 0 0 1 1.408.001z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>

              <button
                type="button"
                class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm transition hover:bg-slate-50"
                :class="sort === 'highest' ? 'bg-slate-50 font-semibold text-slate-950' : 'text-slate-700'"
                @click="selectSort('highest')"
              >
                <span>Highest rating</span>
                <svg
                  v-if="sort === 'highest'"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  class="h-4 w-4 text-slate-900"
                  aria-hidden="true"
                >
                  <path
                    fill-rule="evenodd"
                    d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.07 7.07a1 1 0 0 1-1.414 0l-3.535-3.535a1 1 0 1 1 1.414-1.414l2.828 2.828 6.363-6.364a1 1 0 0 1 1.408.001z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>

              <button
                type="button"
                class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm transition hover:bg-slate-50"
                :class="sort === 'lowest' ? 'bg-slate-50 font-semibold text-slate-950' : 'text-slate-700'"
                @click="selectSort('lowest')"
              >
                <span>Lowest rating</span>
                <svg
                  v-if="sort === 'lowest'"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  class="h-4 w-4 text-slate-900"
                  aria-hidden="true"
                >
                  <path
                    fill-rule="evenodd"
                    d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.07 7.07a1 1 0 0 1-1.414 0l-3.535-3.535a1 1 0 1 1 1.414-1.414l2.828 2.828 6.363-6.364a1 1 0 0 1 1.408.001z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>
            </div>
          </Transition> -->
        </div>
      </div>
    </div>

    <div class="px-4 py-5 sm:px-6">
      <div v-if="loading && !reviews.length" class="space-y-4">
        <div v-for="n in 3" :key="`review-skel-${n}`" class="space-y-3 rounded-2xl border border-slate-200 p-4">
          <div class="h-4 w-32 animate-pulse rounded bg-slate-100" />
          <div class="h-4 w-2/3 animate-pulse rounded bg-slate-100" />
          <div class="h-4 w-full animate-pulse rounded bg-slate-100" />
        </div>
      </div>

      <div v-else-if="error && !reviews.length" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <div class="font-semibold">Failed to load reviews</div>
        <div class="mt-1 text-red-600">{{ error }}</div>
        <button
          type="button"
          class="mt-3 inline-flex items-center rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
          @click="retryInitial"
        >
          Retry
        </button>
      </div>

      <div v-else-if="!sortedReviews.length" class="py-10 text-center text-sm text-slate-500">
        No reviews yet.
      </div>

      <div v-else>
        <div class="divide-y divide-slate-200">
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

        <div v-if="canLoadMore" class="pt-6">
          <button
            type="button"
            class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="loadingMore"
            @click="loadMore"
          >
            <span v-if="loadingMore">Loading…</span>
            <span v-else>Load {{ nextLoadCount }} more review{{ nextLoadCount === 1 ? '' : 's' }}</span>
          </button>
          <div class="mt-2 text-center text-xs text-slate-400">
            Showing {{ reviews.length }} of {{ summary.reviews_count }}
          </div>
        </div>

        <div
          v-if="error && reviews.length && errorScope === 'more'"
          class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>{{ error }}</div>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
              @click="retryMore"
            >
              Try again
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
