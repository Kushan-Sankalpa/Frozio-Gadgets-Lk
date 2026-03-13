<script setup lang="ts">
import { computed } from 'vue'

type ShoeProductCard = {
  id: number | string
  name: string
  slug?: string | null
  brand_name?: string | null
  category_name?: string | null
  subcategory_name?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  currency?: string | null
  regular_price: number | null
  sale_price: number | null
  display_price: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  status?: string | null
  stock_status?: string | null
}

type PaginationMeta = {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

const props = defineProps<{
  products: ShoeProductCard[]
  loading: boolean
  loadingMore: boolean
  loadError?: string | null
  pagination: PaginationMeta
}>()

const emit = defineEmits<{
  (e: 'page-change', value: number): void
  (e: 'retry'): void
}>()

const visiblePages = computed(() => {
  const current = props.pagination.current_page
  const last = props.pagination.last_page

  const start = Math.max(1, current - 2)
  const end = Math.min(last, current + 2)

  return Array.from({ length: end - start + 1 }, (_, index) => start + index)
})

const skeletonCount = computed(() => {
  if (props.loading) return 6
  if (props.loadingMore) return Math.max(0, 12 - props.products.length)
  return 0
})

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) {
    return 'Rs 0.00'
  }

  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 rounded-[28px] border border-neutral-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <div>
        <h2 class="text-lg font-semibold text-neutral-900">
          Product Results
        </h2>
        <p class="mt-1 text-sm text-neutral-500">
          Page {{ pagination.current_page }} of {{ pagination.last_page }} · {{ pagination.total }} items
        </p>
      </div>

      <div class="rounded-full bg-neutral-100 px-4 py-2 text-sm font-medium text-neutral-700">
        Showing {{ pagination.from }}–{{ pagination.to }}
      </div>
    </div>

    <div
      v-if="loadError"
      class="rounded-[28px] border border-red-200 bg-red-50 px-6 py-12 text-center"
    >
      <h3 class="text-lg font-semibold text-red-700">
        Failed to load products
      </h3>
      <p class="mt-2 text-sm text-red-500">
        Please try again.
      </p>

      <button
        type="button"
        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-neutral-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-neutral-800"
        @click="emit('retry')"
      >
        Retry
      </button>
    </div>

    <template v-else>
      <div
        v-if="!loading && !products.length"
        class="rounded-[28px] border border-neutral-200 bg-white px-6 py-14 text-center shadow-sm"
      >
        <h3 class="text-xl font-semibold text-neutral-900">
          No shoe products found
        </h3>
        <p class="mt-2 text-sm text-neutral-500">
          Try another category, subcategory, search, or price range.
        </p>
      </div>

      <div
        v-else
        class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3"
      >
        <article
          v-for="product in products"
          :key="product.id"
          class="shoe-product-card group overflow-hidden rounded-[24px] border border-neutral-200 bg-white shadow-sm"
        >
          <div class="relative overflow-hidden bg-white">
            <div class="absolute left-4 top-4 z-20 flex flex-col gap-2">
              <span
                v-if="product.has_discount && product.discount_label"
                class="inline-flex w-fit items-center rounded-md bg-[#ef5a4f] px-3 py-1 text-xs font-semibold text-white shadow-sm"
              >
                {{ product.discount_label }}
              </span>

              <span
                v-if="product.is_sold_out"
                class="inline-flex w-fit items-center rounded-md bg-[#bdbdbd] px-3 py-1 text-xs font-semibold text-white shadow-sm"
              >
                Sold Out
              </span>
            </div>

            <div class="absolute right-4 top-4 z-20 text-right">
              <div class="text-xs font-semibold uppercase tracking-[0.16em] text-neutral-700">
                {{ product.brand_name || 'Featured' }}
              </div>
              <div class="text-[11px] text-neutral-500">
                {{ product.category_name || 'shoe collection' }}
              </div>
            </div>

            <div class="relative flex h-[280px] items-center justify-center px-4 pb-4 pt-12">
              <img
                :src="product.thumbnail_url || product.hover_image_url || ''"
                :alt="product.name"
                class="shoe-main-image max-h-full max-w-full object-contain"
                :class="{ 'opacity-0': !product.thumbnail_url && !product.hover_image_url }"
              />

              <img
                v-if="product.hover_image_url"
                :src="product.hover_image_url"
                :alt="`${product.name} hover`"
                class="shoe-hover-image max-h-full max-w-full object-contain"
              />
            </div>
          </div>

          <div class="bg-white p-4">
            <div
              v-if="product.subcategory_name"
              class="mb-2 text-[11px] font-medium uppercase tracking-[0.14em] text-neutral-400"
            >
              {{ product.subcategory_name }}
            </div>

            <h3 class="line-clamp-2 text-[17px] font-medium leading-snug text-neutral-900">
              {{ product.name }}
            </h3>

            <div class="mt-3 space-y-1">
              <p
                v-if="product.has_discount && product.regular_price !== null && product.display_price !== null"
                class="flex flex-wrap items-center gap-2 text-[14px] leading-6"
              >
                <span class="font-semibold text-neutral-400 line-through">
                  {{ formatPrice(product.regular_price) }}
                </span>

                <span class="font-bold text-[#ef5a4f]">
                  {{ formatPrice(product.display_price) }}
                </span>
              </p>

              <p
                v-else
                class="text-[17px] font-bold text-neutral-900"
              >
                {{ formatPrice(product.display_price) }}
              </p>
            </div>
          </div>
        </article>

        <div
          v-for="index in skeletonCount"
          :key="`shoe-list-skeleton-${index}`"
          class="overflow-hidden rounded-[24px] border border-neutral-200 bg-white shadow-sm"
        >
          <div class="relative h-[280px] animate-pulse bg-white">
            <div class="absolute left-4 top-4 h-6 w-20 rounded bg-neutral-200" />
            <div class="absolute right-4 top-4 h-4 w-20 rounded bg-neutral-200" />
          </div>

          <div class="space-y-3 p-4">
            <div class="h-4 w-1/3 animate-pulse rounded bg-neutral-100" />
            <div class="h-5 w-4/5 animate-pulse rounded bg-neutral-200" />
            <div class="h-5 w-2/3 animate-pulse rounded bg-neutral-100" />
            <div class="h-5 w-1/2 animate-pulse rounded bg-neutral-200" />
          </div>
        </div>
      </div>

      <div
        v-if="pagination.last_page > 1"
        class="flex flex-wrap items-center justify-center gap-2 rounded-[28px] border border-neutral-200 bg-white px-4 py-5 shadow-sm"
      >
        <button
          type="button"
          class="rounded-2xl border px-4 py-2 text-sm font-semibold transition"
          :class="
            pagination.current_page === 1
              ? 'cursor-not-allowed border-neutral-200 text-neutral-300'
              : 'border-neutral-200 text-neutral-700 hover:border-neutral-900 hover:text-neutral-900'
          "
          :disabled="pagination.current_page === 1"
          @click="emit('page-change', pagination.current_page - 1)"
        >
          Prev
        </button>

        <button
          v-for="pageNumber in visiblePages"
          :key="pageNumber"
          type="button"
          class="min-w-[44px] rounded-2xl border px-4 py-2 text-sm font-semibold transition"
          :class="
            pageNumber === pagination.current_page
              ? 'border-neutral-900 bg-neutral-900 text-white'
              : 'border-neutral-200 text-neutral-700 hover:border-neutral-900 hover:text-neutral-900'
          "
          @click="emit('page-change', pageNumber)"
        >
          {{ pageNumber }}
        </button>

        <button
          type="button"
          class="rounded-2xl border px-4 py-2 text-sm font-semibold transition"
          :class="
            pagination.current_page === pagination.last_page
              ? 'cursor-not-allowed border-neutral-200 text-neutral-300'
              : 'border-neutral-200 text-neutral-700 hover:border-neutral-900 hover:text-neutral-900'
          "
          :disabled="pagination.current_page === pagination.last_page"
          @click="emit('page-change', pagination.current_page + 1)"
        >
          Next
        </button>
      </div>
    </template>
  </div>
</template>

<style scoped>
.shoe-product-card {
  transition:
    transform 0.4s ease,
    box-shadow 0.4s ease;
}

.shoe-product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 42px rgba(15, 23, 42, 0.1);
}

.shoe-main-image,
.shoe-hover-image {
  position: absolute;
  max-width: calc(100% - 2rem);
  max-height: calc(100% - 2rem);
  object-fit: contain;
  transition:
    opacity 0.4s ease,
    transform 0.4s ease;
}

.shoe-main-image {
  opacity: 1;
  transform: scale(1);
}

.shoe-hover-image {
  opacity: 0;
  transform: scale(1.02);
}

.shoe-product-card:hover .shoe-main-image {
  opacity: 0;
  transform: scale(1.01);
}

.shoe-product-card:hover .shoe-hover-image {
  opacity: 1;
  transform: scale(1);
}

@media (prefers-reduced-motion: reduce) {
  .shoe-product-card,
  .shoe-main-image,
  .shoe-hover-image {
    transition: none !important;
    transform: none !important;
  }
}
</style>