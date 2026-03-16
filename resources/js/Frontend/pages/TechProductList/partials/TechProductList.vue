<script setup lang="ts">
import { computed } from 'vue'

type ProductColor = {
  id: number | string
  name: string
  color_code?: string | null
  image_url?: string | null
}

type TechProductCard = {
  id: number | string
  name: string
  category_name?: string | null
  brand_name?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  regular_price: number | null
  display_price: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  colors?: ProductColor[]
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
  products: TechProductCard[]
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

const colorFallbackMap: Record<string, string> = {
  black: '#111111',
  white: '#ffffff',
  red: '#b91c1c',
  blue: '#1d4ed8',
  green: '#15803d',
  yellow: '#eab308',
  gold: '#c9a227',
  silver: '#c0c0c0',
  gray: '#9ca3af',
  grey: '#9ca3af',
  pink: '#ec4899',
  purple: '#7c3aed',
  orange: '#ea580c',
  brown: '#7c4a2d',
  beige: '#d6c2a1',
  cream: '#f3eadb',
  graphite: '#4b5563',
  midnight: '#1f2937',
  starlight: '#f5deb3',
}

function normalizeName(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
}

function colorSwatchStyle(color: ProductColor) {
  if (color.color_code && /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(color.color_code)) {
    return {
      backgroundColor: color.color_code,
    }
  }

  const key = normalizeName(color.name)
  return {
    backgroundColor: colorFallbackMap[key] || '#d4d4d8',
  }
}
</script>

<template>
  <div class="space-y-6">
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
          No tech products found
        </h3>
        <p class="mt-2 text-sm text-neutral-500">
          Try another category, brand, search, or price range.
        </p>
      </div>

      <div
        v-else
        class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3 xl:grid-cols-3 lg:gap-5"
      >
        <article
          v-for="product in products"
          :key="product.id"
          class="product-card group overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm sm:rounded-[24px]"
        >
          <div class="relative overflow-hidden bg-white">
            <div class="absolute left-3 top-3 z-20 flex flex-col gap-1.5 sm:left-4 sm:top-4 sm:gap-2">
              <span
                v-if="product.has_discount && product.discount_label"
                class="inline-flex w-fit items-center rounded-md bg-[#ef5a4f] px-2.5 py-1 text-[10px] font-semibold text-white shadow-sm sm:px-3 sm:text-xs"
              >
                {{ product.discount_label }}
              </span>

              <span
                v-if="product.is_sold_out"
                class="inline-flex w-fit items-center rounded-md bg-[#bdbdbd] px-2.5 py-1 text-[10px] font-semibold text-white shadow-sm sm:px-3 sm:text-xs"
              >
                Sold Out
              </span>
            </div>

            <div class="absolute right-3 top-3 z-20 text-right sm:right-4 sm:top-4">
              <div class="text-[9px] font-semibold uppercase tracking-[0.14em] text-neutral-700 sm:text-xs sm:tracking-[0.16em]">
                {{ product.category_name || 'Category' }}
              </div>
              <div class="text-[9px] text-neutral-500 sm:text-[11px]">
                tech collection
              </div>
            </div>

            <div class="relative flex h-[180px] items-center justify-center px-3 pb-3 pt-10 sm:h-[240px] sm:px-4 sm:pb-4 sm:pt-12 xl:h-[260px]">
              <img
                :src="product.thumbnail_url || product.hover_image_url || ''"
                :alt="product.name"
                class="product-main-image max-h-full max-w-full object-contain"
                :class="{ 'opacity-0': !product.thumbnail_url && !product.hover_image_url }"
              />

              <img
                v-if="product.hover_image_url"
                :src="product.hover_image_url"
                :alt="`${product.name} hover`"
                class="product-hover-image max-h-full max-w-full object-contain"
              />
            </div>
          </div>

          <div class="bg-white p-3 sm:p-4">
            <h3 class="line-clamp-2 text-[14px] font-medium leading-snug text-neutral-900 sm:text-[16px] xl:text-[17px]">
              {{ product.name }}
            </h3>

            <div class="mt-2 space-y-1 sm:mt-3">
              <p
                v-if="product.has_discount && product.regular_price !== null && product.display_price !== null"
                class="flex flex-wrap items-center gap-1.5 text-[12px] leading-5 sm:gap-2 sm:text-[14px] sm:leading-6"
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
                class="text-[15px] font-bold text-neutral-900 sm:text-[17px]"
              >
                {{ formatPrice(product.display_price) }}
              </p>
            </div>

            <div
              v-if="product.colors && product.colors.length"
              class="mt-3 flex items-center gap-2"
            >
              <span
                v-for="color in product.colors"
                :key="color.id"
                class="h-6 w-6 rounded-full border border-neutral-300 shadow-sm"
                :style="colorSwatchStyle(color)"
                :title="color.name"
              />
            </div>
          </div>
        </article>

        <div
          v-for="index in skeletonCount"
          :key="`tech-list-skeleton-${index}`"
          class="overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm sm:rounded-[24px]"
        >
          <div class="relative h-[180px] animate-pulse bg-white sm:h-[240px] xl:h-[260px]">
            <div class="absolute left-3 top-3 h-5 w-16 rounded bg-neutral-200 sm:left-4 sm:top-4 sm:h-6 sm:w-20" />
            <div class="absolute right-3 top-3 h-4 w-14 rounded bg-neutral-200 sm:right-4 sm:top-4 sm:w-20" />
          </div>

          <div class="space-y-2 p-3 sm:space-y-3 sm:p-4">
            <div class="h-4 w-4/5 animate-pulse rounded bg-neutral-200 sm:h-5" />
            <div class="h-4 w-2/3 animate-pulse rounded bg-neutral-100 sm:h-5" />
            <div class="h-4 w-1/2 animate-pulse rounded bg-neutral-200 sm:h-5" />
            <div class="mt-2 flex gap-2">
              <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
              <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
              <div class="h-6 w-6 animate-pulse rounded-full bg-neutral-200" />
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="pagination.last_page > 1"
        class="flex flex-wrap items-center justify-center gap-2 pt-2"
      >
        <button
          type="button"
          class="rounded-2xl border bg-white px-4 py-2 text-sm font-semibold transition shadow-sm"
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
          class="min-w-[44px] rounded-2xl border bg-white px-4 py-2 text-sm font-semibold transition shadow-sm"
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
          class="rounded-2xl border bg-white px-4 py-2 text-sm font-semibold transition shadow-sm"
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
.product-card {
  transition:
    transform 0.4s ease,
    box-shadow 0.4s ease;
}

.product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 42px rgba(15, 23, 42, 0.1);
}

.product-main-image,
.product-hover-image {
  position: absolute;
  max-width: calc(100% - 1.5rem);
  max-height: calc(100% - 1.5rem);
  object-fit: contain;
  transition:
    opacity 0.4s ease,
    transform 0.4s ease;
}

.product-main-image {
  opacity: 1;
  transform: scale(1);
}

.product-hover-image {
  opacity: 0;
  transform: scale(1.02);
}

.product-card:hover .product-main-image {
  opacity: 0;
  transform: scale(1.01);
}

.product-card:hover .product-hover-image {
  opacity: 1;
  transform: scale(1);
}

@media (min-width: 640px) {
  .product-main-image,
  .product-hover-image {
    max-width: calc(100% - 2rem);
    max-height: calc(100% - 2rem);
  }
}

@media (prefers-reduced-motion: reduce) {
  .product-card,
  .product-main-image,
  .product-hover-image {
    transition: none !important;
    transform: none !important;
  }
}
</style>