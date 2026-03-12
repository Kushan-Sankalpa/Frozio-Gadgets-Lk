<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'

type FeaturedShoe = {
  id: number | string
  name: string
  slug?: string | null
  brand_name?: string | null
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

const props = defineProps<{
  products: FeaturedShoe[]
}>()

const imageLoadTotal = ref(0)
const imageLoadDone = ref(0)
const loading = ref(true)

const visibleProducts = computed(() => (props.products ?? []).slice(0, 8))

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) {
    return 'Rs 0.00'
  }

  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function preloadImages() {
  const products = visibleProducts.value ?? []

  const urls = products
    .flatMap((product) => [product.thumbnail_url, product.hover_image_url])
    .filter((url): url is string => !!url)

  imageLoadTotal.value = urls.length
  imageLoadDone.value = 0

  if (!urls.length) {
    loading.value = false
    return
  }

  loading.value = true

  urls.forEach((url) => {
    const img = new Image()

    const markDone = () => {
      imageLoadDone.value += 1

      if (imageLoadDone.value >= imageLoadTotal.value) {
        loading.value = false
      }
    }

    img.onload = markDone
    img.onerror = markDone
    img.src = url
  })
}

watch(
  () => props.products,
  () => {
    preloadImages()
  },
  { immediate: true, deep: true }
)

onMounted(() => {
  preloadImages()
})
</script>

<template>
  <section class="mx-auto max-w-7xl px-3 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div class="mb-5 flex items-end justify-between gap-3 sm:mb-7">
      <div>
        <h2 class="text-2xl font-semibold tracking-[-0.03em] text-gray-900 sm:text-4xl">
          Featured Shoes
        </h2>
        <p class="mt-1 text-sm text-neutral-500 sm:text-base">
          Selected featured shoe products from the latest collection.
        </p>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-5">
      <div
        v-for="index in 8"
        :key="`shoe-skeleton-${index}`"
        class="overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm sm:rounded-[24px]"
      >
        <div class="relative h-[180px] animate-pulse bg-white sm:h-[240px] xl:h-[260px]">
          <div class="absolute left-3 top-3 h-5 w-16 rounded bg-neutral-200 sm:left-4 sm:top-4 sm:h-6 sm:w-20" />
          <div class="absolute left-3 top-10 h-5 w-14 rounded bg-neutral-200 sm:left-4 sm:top-12 sm:h-6 sm:w-16" />
        </div>

        <div class="space-y-2 p-3 sm:space-y-3 sm:p-4">
          <div class="h-4 w-3/4 animate-pulse rounded bg-neutral-200 sm:h-5" />
          <div class="h-4 w-full animate-pulse rounded bg-neutral-100" />
          <div class="h-4 w-2/3 animate-pulse rounded bg-neutral-200" />
        </div>
      </div>
    </div>

    <div
      v-else-if="visibleProducts.length"
      class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-5"
    >
      <article
        v-for="product in visibleProducts"
        :key="product.id"
        class="shoe-product-card group overflow-hidden rounded-[20px] border border-neutral-200 bg-white shadow-sm sm:rounded-[24px]"
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
              {{ product.brand_name || 'Featured' }}
            </div>
            <div class="text-[9px] text-neutral-500 sm:text-[11px]">
              shoe collection
            </div>
          </div>

          <div class="relative flex h-[180px] items-center justify-center px-3 pt-10 pb-3 sm:h-[240px] sm:px-4 sm:pt-12 sm:pb-4 xl:h-[260px]">
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
        </div>
      </article>
    </div>
  </section>
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
  max-width: calc(100% - 1.5rem);
  max-height: calc(100% - 1.5rem);
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

@media (min-width: 640px) {
  .shoe-main-image,
  .shoe-hover-image {
    max-width: calc(100% - 2rem);
    max-height: calc(100% - 2rem);
  }
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