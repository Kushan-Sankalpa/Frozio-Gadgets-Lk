<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'

type BreadcrumbItem = {
  label: string
  href: string | null
}

type ShellData = {
  name?: string | null
  breadcrumb?: BreadcrumbItem[]
}

type GalleryItem = {
  id: string | number
  src: string
}

type ProductColor = {
  id: number | string
  name: string
  color_code?: string | null
  image_url?: string | null
}

type StorageOption = {
  id: number | string
  label: string
}

type ProductVariant = {
  id: string | number
  color_option_id?: number | string | null
  storage_option_id?: number | string | null
  sku?: string | null
  price_lkr: number
  old_price_lkr?: number | null
  final_price_lkr?: number | null
  stock_count?: number | null
  in_stock?: boolean
  status?: string | null
  discount_label?: string | null
}

type SpecRow = {
  label: string
  value: string
}

type ProductPayload = {
  id: number | string
  name: string
  sku?: string | null
  short_description?: string | null
  long_description?: string | null
  brand?: {
    id?: number | string | null
    name?: string | null
    logo_url?: string | null
  } | null
  category?: {
    id?: number | string | null
    name?: string | null
  } | null
  breadcrumb?: BreadcrumbItem[]
  main_image?: string | null
  gallery: GalleryItem[]
  colors: ProductColor[]
  storage_options: StorageOption[]
  variants: ProductVariant[]
  warranty_label?: string | null
  base_price?: number | null
  old_price?: number | null
  current_price?: number | null
  has_discount?: boolean
  discount_label?: string | null
  specifications: SpecRow[]
  default_color_id?: number | string | null
  default_storage_id?: number | string | null
  stock_count?: number | null
  in_stock?: boolean
}

const props = defineProps<{
  loading: boolean
  error: string | null
  product: ProductPayload | null
  shell?: ShellData | null
}>()

const emit = defineEmits<{
  (e: 'retry'): void
}>()

const selectedColorId = ref<number | string | null>(null)
const selectedStorageId = ref<number | string | null>(null)
const activeImage = ref<string | null>(null)
const quantity = ref(1)
const activeTab = ref<'description' | 'specifications'>('description')
const flashMessage = ref('')
const hoveredColorName = ref<string | null>(null)

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

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) {
    return 'Rs 0.00'
  }

  return `Rs ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
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

const breadcrumbItems = computed(() => {
  return props.product?.breadcrumb || props.shell?.breadcrumb || [
    { label: 'Home', href: '/' },
    { label: 'Tech Products', href: '/tech-products' },
    { label: props.shell?.name || 'Product', href: null },
  ]
})

const productColors = computed(() => props.product?.colors ?? [])
const productStorages = computed(() => props.product?.storage_options ?? [])
const productVariants = computed(() => props.product?.variants ?? [])
const thumbnailImages = computed(() => (props.product?.gallery ?? []).slice(0, 4))

const currentVariant = computed<ProductVariant | null>(() => {
  const exactInStock = productVariants.value.find((variant) => {
    return variant.color_option_id === selectedColorId.value
      && variant.storage_option_id === selectedStorageId.value
      && variant.status !== 'inactive'
      && variant.in_stock
  })
  if (exactInStock) return exactInStock

  const exactActive = productVariants.value.find((variant) => {
    return variant.color_option_id === selectedColorId.value
      && variant.storage_option_id === selectedStorageId.value
      && variant.status !== 'inactive'
  })
  if (exactActive) return exactActive

  const colorMatch = productVariants.value.find((variant) => {
    return variant.color_option_id === selectedColorId.value
      && variant.status !== 'inactive'
      && variant.in_stock
  })
  if (colorMatch) return colorMatch

  const storageMatch = productVariants.value.find((variant) => {
    return variant.storage_option_id === selectedStorageId.value
      && variant.status !== 'inactive'
      && variant.in_stock
  })
  if (storageMatch) return storageMatch

  return productVariants.value.find((variant) => variant.in_stock)
    || productVariants.value.find((variant) => variant.status !== 'inactive')
    || productVariants.value[0]
    || null
})

const currentPrice = computed(() => {
  return currentVariant.value?.final_price_lkr
    ?? props.product?.current_price
    ?? props.product?.base_price
    ?? 0
})

const currentOldPrice = computed(() => {
  return currentVariant.value?.old_price_lkr ?? props.product?.old_price ?? null
})

const currentDiscountLabel = computed(() => {
  return currentVariant.value?.discount_label ?? props.product?.discount_label ?? null
})

const hasDiscount = computed(() => {
  return !!currentOldPrice.value && Number(currentOldPrice.value) > Number(currentPrice.value)
})

const stockCount = computed(() => {
  return Number(currentVariant.value?.stock_count ?? props.product?.stock_count ?? 0)
})

const isInStock = computed(() => {
  if (currentVariant.value) {
    return !!currentVariant.value.in_stock && stockCount.value > 0
  }

  return !!props.product?.in_stock && Number(props.product?.stock_count ?? 0) > 0
})

const availabilityText = computed(() => (isInStock.value ? 'In Stock' : 'Out of Stock'))

const storageLabel = computed(() => {
  const matched = productStorages.value.find((item) => item.id === selectedStorageId.value)
  return matched?.label || 'N/A'
})

const selectedColorName = computed(() => {
  const matched = productColors.value.find((item) => item.id === selectedColorId.value)
  return matched?.name || ''
})

const visibleColorName = computed(() => hoveredColorName.value || selectedColorName.value || '')

const canIncreaseQty = computed(() => {
  return isInStock.value && quantity.value < Math.max(1, stockCount.value)
})

const displayImage = computed(() => {
  return activeImage.value
    || props.product?.main_image
    || thumbnailImages.value[0]?.src
    || ''
})

function showMessage(message: string) {
  flashMessage.value = message
  window.setTimeout(() => {
    if (flashMessage.value === message) {
      flashMessage.value = ''
    }
  }, 2200)
}

function ensureSelections() {
  if (!props.product) return

  const defaultVariant = currentVariant.value

  selectedColorId.value = props.product.default_color_id
    ?? defaultVariant?.color_option_id
    ?? productColors.value[0]?.id
    ?? null

  selectedStorageId.value = props.product.default_storage_id
    ?? defaultVariant?.storage_option_id
    ?? productStorages.value[0]?.id
    ?? null

  activeImage.value = props.product.main_image || thumbnailImages.value[0]?.src || null
  quantity.value = 1
}

function findVariantByColor(colorId: number | string) {
  return productVariants.value.find((variant) => {
    return variant.color_option_id === colorId
      && variant.storage_option_id === selectedStorageId.value
      && variant.status !== 'inactive'
  }) || productVariants.value.find((variant) => {
    return variant.color_option_id === colorId
      && variant.status !== 'inactive'
      && variant.in_stock
  }) || productVariants.value.find((variant) => {
    return variant.color_option_id === colorId
      && variant.status !== 'inactive'
  }) || null
}

function findVariantByStorage(storageId: number | string) {
  return productVariants.value.find((variant) => {
    return variant.storage_option_id === storageId
      && variant.color_option_id === selectedColorId.value
      && variant.status !== 'inactive'
  }) || productVariants.value.find((variant) => {
    return variant.storage_option_id === storageId
      && variant.status !== 'inactive'
      && variant.in_stock
  }) || productVariants.value.find((variant) => {
    return variant.storage_option_id === storageId
      && variant.status !== 'inactive'
  }) || null
}

function selectColor(colorId: number | string) {
  selectedColorId.value = colorId

  const matched = findVariantByColor(colorId)
  if (matched?.storage_option_id !== undefined && matched?.storage_option_id !== null) {
    selectedStorageId.value = matched.storage_option_id
  }
}

function selectStorage(storageId: number | string) {
  selectedStorageId.value = storageId

  const matched = findVariantByStorage(storageId)
  if (matched?.color_option_id !== undefined && matched?.color_option_id !== null) {
    selectedColorId.value = matched.color_option_id
  }
}

function decreaseQuantity() {
  quantity.value = Math.max(1, quantity.value - 1)
}

function increaseQuantity() {
  if (!canIncreaseQty.value) return
  quantity.value += 1
}

function addToCart() {
  if (!props.product || !currentVariant.value || !isInStock.value) return

  window.dispatchEvent(new CustomEvent('tech-product:add-to-cart', {
    detail: {
      productId: props.product.id,
      variantId: currentVariant.value.id,
      quantity: quantity.value,
    },
  }))

  showMessage('Product added to cart.')
}

function buyNow() {
  if (!props.product || !currentVariant.value || !isInStock.value) return

  window.dispatchEvent(new CustomEvent('tech-product:buy-now', {
    detail: {
      productId: props.product.id,
      variantId: currentVariant.value.id,
      quantity: quantity.value,
    },
  }))

  showMessage('Ready for checkout.')
}

watch(() => props.product, () => {
  selectedColorId.value = null
  selectedStorageId.value = null
  activeImage.value = null
  ensureSelections()
}, { immediate: true })

watch(currentVariant, (variant) => {
  if (!variant) return

  if (variant.color_option_id !== undefined && variant.color_option_id !== null) {
    selectedColorId.value = variant.color_option_id
  }

  if (variant.storage_option_id !== undefined && variant.storage_option_id !== null) {
    selectedStorageId.value = variant.storage_option_id
  }

  quantity.value = Math.min(quantity.value, Math.max(1, stockCount.value))
})
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <nav aria-label="Breadcrumb" class="mb-6">
      <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <li
          v-for="(item, index) in breadcrumbItems"
          :key="`${item.label}-${index}`"
          class="flex items-center gap-2"
        >
          <template v-if="item.href">
            <Link :href="item.href" class="transition hover:text-slate-900">
              {{ item.label }}
            </Link>
          </template>
          <template v-else>
            <span class="font-medium text-slate-900">{{ item.label }}</span>
          </template>
          <span v-if="index < breadcrumbItems.length - 1" class="text-slate-300">/</span>
        </li>
      </ol>
    </nav>

    <div
      v-if="flashMessage"
      class="mb-4 border-b border-emerald-200 pb-3 text-sm font-medium text-emerald-700"
    >
      {{ flashMessage }}
    </div>

    <div
      v-if="error && !loading"
      class="py-14 text-center"
    >
      <h2 class="text-xl font-semibold text-red-700">Failed to load product details</h2>
      <p class="mt-2 text-sm text-red-500">{{ error }}</p>
      <button
        type="button"
        class="mt-5 border-b border-slate-900 pb-1 text-sm font-semibold text-slate-900 transition hover:opacity-70"
        @click="emit('retry')"
      >
        Retry
      </button>
    </div>

    <template v-else>
      <div class="grid gap-10 lg:grid-cols-[minmax(0,1.02fr)_minmax(360px,0.98fr)] lg:gap-12">
        <section>
          <template v-if="loading || !product">
            <div class="grid gap-4 md:grid-cols-[78px_minmax(0,1fr)]">
              <div class="hidden gap-3 md:flex md:flex-col">
                <div
                  v-for="index in 4"
                  :key="`thumb-skeleton-${index}`"
                  class="h-20 animate-pulse rounded-2xl bg-slate-100"
                />
              </div>

              <div class="min-h-[360px] rounded-[28px] border border-slate-200 bg-white p-6 sm:min-h-[460px]">
                <div class="h-full w-full animate-pulse rounded-[24px] bg-slate-100" />
              </div>
            </div>

            <div class="mt-10 border-b border-slate-200" />
            <div class="pt-6">
              <div class="space-y-3">
                <div class="h-6 w-40 animate-pulse rounded bg-slate-200" />
                <div class="h-5 w-full animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-11/12 animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-10/12 animate-pulse rounded bg-slate-100" />
              </div>
            </div>
          </template>

          <template v-else>
            <div class="grid gap-4 md:grid-cols-[78px_minmax(0,1fr)]">
              <div
                v-if="thumbnailImages.length"
                class="order-2 flex gap-3 overflow-x-auto md:order-1 md:flex-col md:overflow-visible"
              >
                <button
                  v-for="image in thumbnailImages"
                  :key="image.id"
                  type="button"
                  class="h-20 min-w-20 overflow-hidden rounded-2xl border transition sm:h-[88px] sm:min-w-[88px]"
                  :class="displayImage === image.src
                    ? 'border-slate-900'
                    : 'border-slate-200 hover:border-slate-400'"
                  @click="activeImage = image.src"
                >
                  <img
                    :src="image.src"
                    alt="Product image"
                    class="h-full w-full object-contain p-2"
                  />
                </button>
              </div>

              <div
                class="order-1 flex min-h-[360px] items-center justify-center rounded-[28px] border border-slate-200 bg-white p-5 sm:min-h-[460px] sm:p-8 md:order-2"
              >
                <img
                  v-if="displayImage"
                  :src="displayImage"
                  :alt="product.name"
                  class="max-h-[440px] w-full object-contain"
                />
              </div>
            </div>

            <div class="mt-10 border-b border-slate-200">
              <div class="flex items-end gap-8">
                <button
                  type="button"
                  class="relative pb-4 text-sm sm:text-base"
                  :class="activeTab === 'description'
                    ? 'font-semibold text-slate-950'
                    : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="activeTab = 'description'"
                >
                  Description
                  <span
                    v-if="activeTab === 'description'"
                    class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                  />
                </button>

                <button
                  type="button"
                  class="relative pb-4 text-sm sm:text-base"
                  :class="activeTab === 'specifications'
                    ? 'font-semibold text-slate-950'
                    : 'font-medium text-slate-500 hover:text-slate-900'"
                  @click="activeTab = 'specifications'"
                >
                  Specifications
                  <span
                    v-if="activeTab === 'specifications'"
                    class="absolute inset-x-0 bottom-[-1px] h-[2px] bg-slate-950"
                  />
                </button>
              </div>
            </div>

            <div class="pt-6">
              <div
                v-if="activeTab === 'description'"
                class="prose prose-slate max-w-none text-sm leading-8 sm:text-[15px]"
                v-html="product.long_description || product.short_description || '<p>No description available.</p>'"
              />

              <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[520px] border-collapse">
                  <tbody>
                    <tr
                      v-for="(spec, index) in product.specifications"
                      :key="`${spec.label}-${index}`"
                      class="border-b border-slate-200"
                    >
                      <td class="w-[240px] py-4 pr-6 text-sm font-semibold text-slate-700">
                        {{ spec.label }}
                      </td>
                      <td class="py-4 text-sm text-slate-600">
                        {{ spec.value }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </template>
        </section>

        <section class="pt-1">
          <template v-if="loading || !product">
            <div class="space-y-5">
              <div class="h-4 w-24 animate-pulse rounded bg-slate-100" />
              <div class="h-10 w-4/5 animate-pulse rounded bg-slate-200" />
              <div class="h-5 w-full animate-pulse rounded bg-slate-100" />
              <div class="h-5 w-5/6 animate-pulse rounded bg-slate-100" />
              <div class="h-10 w-52 animate-pulse rounded bg-slate-200" />
              <div class="h-12 w-20 animate-pulse rounded bg-slate-100" />
              <div class="space-y-3 pt-2">
                <div class="h-5 w-64 animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-52 animate-pulse rounded bg-slate-100" />
                <div class="h-5 w-48 animate-pulse rounded bg-slate-100" />
              </div>
              <div class="flex gap-3 pt-2">
                <div class="h-8 w-8 animate-pulse rounded-full bg-slate-100" />
                <div class="h-8 w-8 animate-pulse rounded-full bg-slate-100" />
                <div class="h-8 w-8 animate-pulse rounded-full bg-slate-100" />
              </div>
            </div>
          </template>

          <template v-else>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
              {{ product.category?.name || 'Tech Product' }}
            </p>

            <h1 class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-[2.25rem]">
              {{ product.name }}
            </h1>

            <p
              v-if="product.short_description"
              class="mt-4 text-sm leading-7 text-slate-600 sm:text-[15px]"
            >
              {{ product.short_description }}
            </p>

            <div class="mt-6">
              <div class="flex flex-wrap items-center gap-3">
                <span
                  v-if="hasDiscount && currentDiscountLabel"
                  class="inline-flex items-center text-xs font-semibold uppercase tracking-[0.12em] text-[#ef5a4f]"
                >
                  {{ currentDiscountLabel }}
                </span>
              </div>

              <div class="mt-2 flex flex-wrap items-end gap-3">
                <span
                  v-if="hasDiscount && currentOldPrice"
                  class="text-lg font-medium text-slate-400 line-through"
                >
                  {{ formatPrice(currentOldPrice) }}
                </span>

                <span class="text-3xl font-bold text-slate-950 sm:text-4xl">
                  {{ formatPrice(currentPrice) }}
                </span>
              </div>

              <div v-if="product.brand?.logo_url" class="mt-4">
                <img
                  :src="product.brand.logo_url"
                  :alt="product.brand?.name || 'Brand logo'"
                  class="h-10 w-auto object-contain"
                />
              </div>
            </div>

            <ul class="mt-8 space-y-3 text-sm text-slate-700">
              <li class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span>
                  <span class="font-semibold text-slate-900">Availability:</span>
                  {{ availabilityText }}
                </span>
              </li>

              <li v-if="product.warranty_label" class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span>
                  <span class="font-semibold text-slate-900">Warranty:</span>
                  {{ product.warranty_label }}
                </span>
              </li>

              <li v-if="productStorages.length" class="flex items-start gap-3">
                <span class="mt-[7px] h-1.5 w-1.5 rounded-full bg-slate-900" />
                <span>
                  <span class="font-semibold text-slate-900">Selected Storage:</span>
                  {{ storageLabel }}
                </span>
              </li>
            </ul>

            <div v-if="productColors.length" class="mt-8">
              <p class="text-sm font-semibold text-slate-900">Available Colors</p>

              <div class="mt-4 flex flex-wrap items-center gap-3">
                <button
                  v-for="color in productColors"
                  :key="color.id"
                  type="button"
                  class="relative h-8 w-8 rounded-full transition"
                  :class="selectedColorId === color.id
                    ? 'ring-2 ring-slate-900 ring-offset-2'
                    : 'ring-1 ring-slate-300 hover:ring-slate-500'"
                  :style="colorSwatchStyle(color)"
                  :title="color.name"
                  @mouseenter="hoveredColorName = color.name"
                  @mouseleave="hoveredColorName = null"
                  @click="selectColor(color.id)"
                />
              </div>

              <p v-if="visibleColorName" class="mt-3 text-xs text-slate-500">
                {{ visibleColorName }}
              </p>
            </div>

            <div v-if="productStorages.length" class="mt-8">
              <p class="text-sm font-semibold text-slate-900">Storage</p>

              <div class="mt-3 flex flex-wrap items-center gap-5">
                <button
                  v-for="storage in productStorages"
                  :key="storage.id"
                  type="button"
                  class="border-b pb-1 text-sm transition"
                  :class="selectedStorageId === storage.id
                    ? 'border-slate-900 font-semibold text-slate-950'
                    : 'border-transparent font-medium text-slate-500 hover:border-slate-400 hover:text-slate-900'"
                  @click="selectStorage(storage.id)"
                >
                  {{ storage.label }}
                </button>
              </div>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-6 border-b border-t border-slate-200 py-5">
              <div class="flex items-center gap-4">
                <button
                  type="button"
                  class="text-2xl leading-none text-slate-700 transition hover:text-slate-950"
                  @click="decreaseQuantity"
                >
                  −
                </button>

                <span class="min-w-[28px] text-center text-lg font-semibold text-slate-950">
                  {{ quantity }}
                </span>

                <button
                  type="button"
                  class="text-2xl leading-none text-slate-700 transition hover:text-slate-950 disabled:cursor-not-allowed disabled:text-slate-300"
                  :disabled="!canIncreaseQty"
                  @click="increaseQuantity"
                >
                  +
                </button>
              </div>

              <p class="text-sm text-slate-500">
                {{ stockCount > 0 ? `${stockCount} available` : 'Currently unavailable' }}
              </p>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
              <button
                type="button"
                class="inline-flex min-h-[52px] items-center justify-center border border-slate-900 px-6 text-sm font-semibold text-slate-950 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-300"
                :disabled="!isInStock"
                @click="addToCart"
              >
                Add to Cart
              </button>

              <button
                type="button"
                class="inline-flex min-h-[52px] items-center justify-center bg-slate-950 px-6 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                :disabled="!isInStock"
                @click="buyNow"
              >
                Buy Now
              </button>
            </div>

            <div class="mt-6 text-sm text-slate-500">
              <p><span class="font-semibold text-slate-800">SKU:</span> {{ currentVariant?.sku || product.sku || 'N/A' }}</p>
            </div>
          </template>
        </section>
      </div>
    </template>
  </div>
</template>

<style scoped>
.prose :deep(p) {
  margin-top: 0;
  margin-bottom: 1rem;
}

.prose :deep(ul),
.prose :deep(ol) {
  margin-top: 0.75rem;
  margin-bottom: 1rem;
}

.prose :deep(img) {
  max-width: 100%;
  height: auto;
}

@media (prefers-reduced-motion: reduce) {
  * {
    scroll-behavior: auto !important;
    transition: none !important;
  }
}
</style>