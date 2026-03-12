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

const visibleProducts = computed(() => (props.products ?? []).slice(0, 4))

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
  <section class="mx-auto max-w-7xl px-3 py-8 sm:px-6 sm:py-14 lg:px-8">
    <div class="mb-5 flex items-end justify-between gap-3 sm:mb-8">
      <div>
        <h2 class="text-2xl font-semibold tracking-[-0.03em] text-gray-900 sm:text-4xl">
          Featured Shoes
        </h2>
        <p class="mt-1 text-sm text-neutral-500 sm:text-base">
          Selected featured shoe products from the latest collection.
        </p>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-6">
      <div
        v-for="index in 4"
        :key="`shoe-skeleton-${index}`"
        class="overflow-hidden rounded-[26px] border border-neutral-200 bg-white shadow-sm"
      >
        <div class="relative h-[320px] animate-pulse bg-neutral-100">
          <div class="absolute left-4 top-4 h-6 w-20 rounded bg-neutral-200" />
          <div class="absolute left-4 top-12 h-6 w-16 rounded bg-neutral-200" />
        </div>

        <div class="space-y-3 p-5">
          <div class="h-6 w-3/4 animate-pulse rounded bg-neutral-200" />
          <div class="h-5 w-full animate-pulse rounded bg-neutral-100" />
          <div class="h-5 w-5/6 animate-pulse rounded bg-neutral-100" />
          <div class="h-6 w-2/3 animate-pulse rounded bg-neutral-200" />
          <div class="mt-3 flex gap-2">
            <div class="h-8 w-8 animate-pulse rounded-full bg-neutral-200" />
            <div class="h-8 w-8 animate-pulse rounded-full bg-neutral-200" />
            <div class="h-8 w-8 animate-pulse rounded-full bg-neutral-200" />
          </div>
        </div>
      </div>
    </div>

    <div
      v-else-if="visibleProducts.length"
      class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-6"
    >
      <article
        v-for="product in visibleProducts"
        :key="product.id"
        class="shoe-product-card group overflow-hidden rounded-[26px] border border-neutral-200 bg-white shadow-sm"
      >
        <div class="relative overflow-hidden bg-[#f2f2f2]">
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
              shoe collection
            </div>
          </div>

          <div class="relative flex h-[320px] items-center justify-center px-5 pt-12 pb-6 sm:h-[360px]">
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

        <div class="p-5">
          <h3 class="line-clamp-2 text-[18px] font-medium leading-snug text-neutral-900">
            {{ product.name }}
          </h3>

          <div class="mt-3 space-y-1">
            <p
              v-if="product.has_discount && product.regular_price !== null && product.display_price !== null"
              class="flex flex-wrap items-center gap-2 text-[15px] leading-6"
            >
              <span class="font-semibold text-neutral-400 line-through">
                {{ formatPrice(product.regular_price) }}
              </span>

              <span class="font-bold text-[#ef5a4f]">
                {{ formatPrice(product.display_price) }}
              </span>

              <span class="font-semibold text-neutral-700">
                Save {{ formatPrice((product.regular_price ?? 0) - (product.display_price ?? 0)) }}
              </span>
            </p>

            <p
              v-else
              class="text-[18px] font-bold text-neutral-900"
            >
              {{ formatPrice(product.display_price) }}
            </p>

          </div>

          <!-- <div class="mt-4 flex items-center gap-2">
            <span class="h-8 w-8 rounded-full border-2 border-neutral-800 bg-black shadow-sm"></span>
            <span class="h-8 w-8 rounded-full border border-neutral-300 bg-white shadow-sm"></span>
            <span class="h-8 w-8 rounded-full border border-neutral-300 bg-[#b02a2a] shadow-sm"></span>
          </div> -->
        </div>
      </article>
    </div>
  </section>
</template>

<style scoped>
.shoe-product-card {
  transition:
    transform 0.45s ease,
    box-shadow 0.45s ease;
}

.shoe-product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 22px 55px rgba(15, 23, 42, 0.12);
}

.shoe-main-image,
.shoe-hover-image {
  position: absolute;
  max-width: calc(100% - 2.5rem);
  max-height: calc(100% - 2.5rem);
  object-fit: contain;
  transition:
    opacity 0.45s ease,
    transform 0.45s ease;
}

.shoe-main-image {
  opacity: 1;
  transform: scale(1);
}

.shoe-hover-image {
  opacity: 0;
  transform: scale(1.03);
}

.shoe-product-card:hover .shoe-main-image {
  opacity: 0;
  transform: scale(1.02);
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