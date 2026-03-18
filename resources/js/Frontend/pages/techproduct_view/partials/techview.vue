<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
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
  color_option_id?: number | string | null
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
let flashTimer: number | null = null

const descriptionSection = ref<HTMLElement | null>(null)
const specificationSection = ref<HTMLElement | null>(null)

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
    return { backgroundColor: color.color_code }
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

const productGallery = computed(() => props.product?.gallery ?? [])
const productColors = computed(() => props.product?.colors ?? [])
const productStorages = computed(() => props.product?.storage_options ?? [])
const productVariants = computed(() => props.product?.variants ?? [])

const galleryForSelection = computed(() => {
  const items = productGallery.value

  if (!selectedColorId.value) {
    return items
  }

  const prioritized = [
    ...items.filter((item) => item.color_option_id === selectedColorId.value),
    ...items.filter((item) => item.color_option_id !== selectedColorId.value),
  ]

  return prioritized
})

const matchingVariants = computed(() => {
  return productVariants.value.filter((variant) => {
    const sameColor = selectedColorId.value ? variant.color_option_id === selectedColorId.value : true
    const sameStorage = selectedStorageId.value ? variant.storage_option_id === selectedStorageId.value : true
    return sameColor && sameStorage
  })
})

const currentVariant = computed<ProductVariant | null>(() => {
  const exact = matchingVariants.value.find((variant) => variant.status !== 'inactive')
  if (exact) return exact

  const byStorage = productVariants.value.find((variant) => {
    return variant.storage_option_id === selectedStorageId.value && variant.status !== 'inactive'
  })
  if (byStorage) return byStorage

  const byColor = productVariants.value.find((variant) => {
    return variant.color_option_id === selectedColorId.value && variant.status !== 'inactive'
  })
  if (byColor) return byColor

  return productVariants.value.find((variant) => variant.in_stock) || productVariants.value[0] || null
})

const currentPrice = computed(() => {
  return currentVariant.value?.final_price_lkr ?? props.product?.current_price ?? props.product?.base_price ?? 0
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
  return Number(currentVariant.value?.stock_count ?? 0)
})

const isInStock = computed(() => {
  if (currentVariant.value) {
    return !!currentVariant.value.in_stock && stockCount.value > 0
  }

  return stockCount.value > 0
})

const availabilityText = computed(() => (isInStock.value ? 'In Stock' : 'Out of Stock'))

const storageLabel = computed(() => {
  const matched = productStorages.value.find((item) => item.id === selectedStorageId.value)
  return matched?.label || 'N/A'
})

const selectedColorName = computed(() => {
  const matched = productColors.value.find((item) => item.id === selectedColorId.value)
  return matched?.name || 'Default'
})

const canIncreaseQty = computed(() => {
  return isInStock.value && quantity.value < Math.max(1, stockCount.value)
})

function showMessage(message: string) {
  flashMessage.value = message

  if (flashTimer) {
    window.clearTimeout(flashTimer)
  }

  flashTimer = window.setTimeout(() => {
    flashMessage.value = ''
  }, 2200)
}

function ensureSelections() {
  if (!props.product) return

  if (!selectedColorId.value) {
    selectedColorId.value = props.product.default_color_id ?? productColors.value[0]?.id ?? null
  }

  if (!selectedStorageId.value) {
    selectedStorageId.value = props.product.default_storage_id ?? productStorages.value[0]?.id ?? null
  }

  const safeVariant = currentVariant.value

  if (safeVariant?.color_option_id) {
    selectedColorId.value = safeVariant.color_option_id
  }

  if (safeVariant?.storage_option_id) {
    selectedStorageId.value = safeVariant.storage_option_id
  }

  const firstImage = galleryForSelection.value[0]?.src || null
  if (!activeImage.value && firstImage) {
    activeImage.value = firstImage
  }
}

function selectColor(colorId: number | string) {
  selectedColorId.value = colorId

  const colorImage = galleryForSelection.value.find((item) => item.color_option_id === colorId)?.src
  if (colorImage) {
    activeImage.value = colorImage
  }
}

function selectStorage(storageId: number | string) {
  selectedStorageId.value = storageId
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

async function scrollToSection(target: 'description' | 'specifications') {
  activeTab.value = target
  await nextTick()

  const element = target === 'description' ? descriptionSection.value : specificationSection.value
  element?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

watch(() => props.product, () => {
  activeImage.value = null
  selectedColorId.value = null
  selectedStorageId.value = null
  quantity.value = 1
  ensureSelections()
}, { immediate: true })

watch([selectedColorId, selectedStorageId], () => {
  const maxQty = Math.max(1, stockCount.value)
  quantity.value = Math.min(quantity.value, maxQty)

  if (!activeImage.value && galleryForSelection.value[0]?.src) {
    activeImage.value = galleryForSelection.value[0].src
  }
})

onMounted(() => {
  ensureSelections()
})


onBeforeUnmount(() => {
  if (flashTimer) {
    window.clearTimeout(flashTimer)
  }
})
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
    <nav aria-label="Breadcrumb" class="mb-5">
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

    <div class="mb-6 rounded-[24px] border border-slate-200 bg-white/90 p-2 shadow-sm backdrop-blur sm:p-3">
      <div class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          class="rounded-full px-4 py-2 text-sm font-semibold transition"
          :class="activeTab === 'description'
            ? 'bg-slate-900 text-white shadow-sm'
            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
          @click="scrollToSection('description')"
        >
          Description
        </button>

        <button
          type="button"
          class="rounded-full px-4 py-2 text-sm font-semibold transition"
          :class="activeTab === 'specifications'
            ? 'bg-slate-900 text-white shadow-sm'
            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
          @click="scrollToSection('specifications')"
        >
          Specifications
        </button>
      </div>
    </div>

    <div
      v-if="flashMessage"
      class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
    >
      {{ flashMessage }}
    </div>

    <div
      v-if="error && !loading"
      class="rounded-[28px] border border-red-200 bg-red-50 px-6 py-14 text-center"
    >
      <h2 class="text-xl font-semibold text-red-700">Failed to load product details</h2>
      <p class="mt-2 text-sm text-red-500">{{ error }}</p>
      <button
        type="button"
        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
        @click="emit('retry')"
      >
        Retry
      </button>
    </div>

    <template v-else>
      <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] xl:gap-8">
        <section class="rounded-[32px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5 lg:sticky lg:top-5 lg:h-fit">
          <template v-if="loading || !product">
            <div class="grid gap-4 lg:grid-cols-[92px_minmax(0,1fr)]">
              <div class="order-2 flex gap-3 overflow-x-auto lg:order-1 lg:flex-col">
                <div
                  v-for="index in 5"
                  :key="`thumb-skeleton-${index}`"
                  class="h-20 min-w-20 animate-pulse rounded-2xl bg-slate-100"
                />
              </div>
              <div class="order-1 rounded-[28px] bg-gradient-to-br from-slate-50 to-slate-100 p-6 lg:order-2">
                <div class="flex h-[320px] animate-pulse items-center justify-center rounded-[24px] bg-white/80 sm:h-[420px]" />
              </div>
            </div>
          </template>

          <template v-else>
            <div class="grid gap-4 lg:grid-cols-[92px_minmax(0,1fr)]">
              <div class="order-2 flex gap-3 overflow-x-auto lg:order-1 lg:flex-col lg:overflow-visible">
                <button
                  v-for="image in galleryForSelection"
                  :key="image.id"
                  type="button"
                  class="group relative h-20 min-w-20 overflow-hidden rounded-2xl border bg-white transition sm:h-24 sm:min-w-24"
                  :class="activeImage === image.src
                    ? 'border-slate-900 shadow-md'
                    : 'border-slate-200 hover:border-slate-400'"
                  @click="activeImage = image.src"
                >
                  <img
                    :src="image.src"
                    alt="Product thumbnail"
                    class="h-full w-full object-contain p-2 transition duration-300 group-hover:scale-[1.04]"
                  />
                </button>
              </div>

              <div class="order-1 rounded-[28px] bg-[radial-gradient(circle_at_top,#f8fafc,white_60%)] p-4 sm:p-6 lg:order-2">
                <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                  <div v-if="hasDiscount && currentDiscountLabel" class="inline-flex items-center rounded-full bg-[#ef5a4f] px-3.5 py-1.5 text-xs font-bold text-white shadow-sm">
                    {{ currentDiscountLabel }}
                  </div>

                  <div class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
                    :class="isInStock ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-600'">
                    {{ availabilityText }}
                  </div>
                </div>

                <div class="flex min-h-[320px] items-center justify-center sm:min-h-[420px]">
                  <img
                    :src="activeImage || galleryForSelection[0]?.src || ''"
                    :alt="product.name"
                    class="max-h-[420px] w-full object-contain transition duration-500 ease-out hover:scale-[1.03]"
                  />
                </div>
              </div>
            </div>
          </template>
        </section>

        <section class="rounded-[32px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
          <template v-if="loading || !product">
            <div class="space-y-4">
              <div class="h-6 w-32 animate-pulse rounded-full bg-slate-100" />
              <div class="h-10 w-4/5 animate-pulse rounded-xl bg-slate-200" />
              <div class="h-5 w-full animate-pulse rounded bg-slate-100" />
              <div class="h-5 w-5/6 animate-pulse rounded bg-slate-100" />
              <div class="h-16 animate-pulse rounded-[24px] bg-slate-100" />
              <div class="grid gap-3 sm:grid-cols-2">
                <div class="h-24 animate-pulse rounded-[24px] bg-slate-100" />
                <div class="h-24 animate-pulse rounded-[24px] bg-slate-100" />
              </div>
              <div class="h-24 animate-pulse rounded-[24px] bg-slate-100" />
              <div class="h-14 animate-pulse rounded-[20px] bg-slate-200" />
              <div class="grid gap-3 sm:grid-cols-2">
                <div class="h-14 animate-pulse rounded-[20px] bg-slate-100" />
                <div class="h-14 animate-pulse rounded-[20px] bg-slate-100" />
              </div>
            </div>
          </template>

          <template v-else>
            <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-600">
              <span>{{ product.category?.name || 'Tech Product' }}</span>
            </div>

            <h1 class="text-2xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-3xl lg:text-[2.1rem]">
              {{ product.name }}
            </h1>

            <p v-if="product.short_description" class="mt-3 text-sm leading-7 text-slate-600 sm:text-[15px]">
              {{ product.short_description }}
            </p>

            <div class="mt-5 flex flex-wrap items-center gap-3 rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-4">
              <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Price</p>
                <div class="mt-1 flex flex-wrap items-end gap-2">
                  <span v-if="hasDiscount && currentOldPrice" class="text-base font-semibold text-slate-400 line-through sm:text-lg">
                    {{ formatPrice(currentOldPrice) }}
                  </span>
                  <span class="text-2xl font-bold text-slate-950 sm:text-3xl">
                    {{ formatPrice(currentPrice) }}
                  </span>
                </div>
              </div>

              <div class="ml-auto flex items-center gap-3 rounded-2xl bg-white px-3 py-2 shadow-sm">
                <div v-if="product.brand?.logo_url" class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white">
                  <img :src="product.brand.logo_url" :alt="product.brand?.name || 'Brand logo'" class="h-full w-full object-contain p-2" />
                </div>
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Brand</p>
                  <p class="text-sm font-semibold text-slate-900">{{ product.brand?.name || 'Unknown Brand' }}</p>
                </div>
              </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
              <div class="rounded-[24px] border border-slate-200 bg-white px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Availability</p>
                <p class="mt-2 text-sm font-semibold" :class="isInStock ? 'text-emerald-700' : 'text-red-600'">
                  {{ availabilityText }}
                </p>
                <p class="mt-1 text-xs text-slate-500">
                  {{ stockCount > 0 ? `${stockCount} units available` : 'Currently unavailable' }}
                </p>
              </div>

              <div class="rounded-[24px] border border-slate-200 bg-white px-4 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Warranty</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ product.warranty_label || 'No warranty info' }}</p>
              </div>

              <div class="rounded-[24px] border border-slate-200 bg-white px-4 py-4 sm:col-span-2 xl:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Selected Storage</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ storageLabel }}</p>
                <p class="mt-1 text-xs text-slate-500">Color: {{ selectedColorName }}</p>
              </div>
            </div>

            <div v-if="productColors.length" class="mt-6">
              <p class="text-sm font-semibold text-slate-900">Available Colors</p>
              <div class="mt-3 flex flex-wrap gap-3">
                <button
                  v-for="color in productColors"
                  :key="color.id"
                  type="button"
                  class="group flex items-center gap-3 rounded-full border px-3 py-2 transition"
                  :class="selectedColorId === color.id
                    ? 'border-slate-900 bg-slate-900 text-white shadow-sm'
                    : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400'"
                  @click="selectColor(color.id)"
                >
                  <span
                    class="h-7 w-7 rounded-full border border-black/10 shadow-inner"
                    :style="colorSwatchStyle(color)"
                  />
                  <span class="text-sm font-medium">{{ color.name }}</span>
                </button>
              </div>
            </div>

            <div v-if="productStorages.length" class="mt-6">
              <p class="text-sm font-semibold text-slate-900">Storage</p>
              <div class="mt-3 flex flex-wrap gap-3">
                <button
                  v-for="storage in productStorages"
                  :key="storage.id"
                  type="button"
                  class="rounded-2xl border px-4 py-3 text-sm font-semibold transition"
                  :class="selectedStorageId === storage.id
                    ? 'border-slate-900 bg-slate-900 text-white shadow-sm'
                    : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400'"
                  @click="selectStorage(storage.id)"
                >
                  {{ storage.label }}
                </button>
              </div>
            </div>

            <div class="mt-6 rounded-[24px] border border-slate-200 bg-slate-50 p-4">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm font-semibold text-slate-900">Quantity</p>
                  <p class="mt-1 text-xs text-slate-500">Adjust the quantity before purchase.</p>
                </div>

                <div class="inline-flex items-center rounded-2xl border border-slate-200 bg-white p-1 shadow-sm">
                  <button
                    type="button"
                    class="flex h-11 w-11 items-center justify-center rounded-xl text-xl font-semibold text-slate-700 transition hover:bg-slate-100"
                    @click="decreaseQuantity"
                  >
                    −
                  </button>
                  <div class="min-w-[64px] text-center text-lg font-bold text-slate-950">{{ quantity }}</div>
                  <button
                    type="button"
                    class="flex h-11 w-11 items-center justify-center rounded-xl text-xl font-semibold text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:text-slate-300"
                    :disabled="!canIncreaseQty"
                    @click="increaseQuantity"
                  >
                    +
                  </button>
                </div>
              </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
              <button
                type="button"
                class="inline-flex min-h-[56px] items-center justify-center rounded-[20px] border border-slate-200 bg-white px-5 py-4 text-sm font-semibold text-slate-900 transition hover:border-slate-900 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!isInStock"
                @click="addToCart"
              >
                Add to Cart
              </button>

              <button
                type="button"
                class="inline-flex min-h-[56px] items-center justify-center rounded-[20px] bg-slate-900 px-5 py-4 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                :disabled="!isInStock"
                @click="buyNow"
              >
                Buy Now
              </button>
            </div>

            <div class="mt-5 rounded-[24px] border border-dashed border-slate-200 px-4 py-4 text-sm text-slate-500">
              <p><span class="font-semibold text-slate-800">SKU:</span> {{ currentVariant?.sku || product.sku || 'N/A' }}</p>
              <p class="mt-1"><span class="font-semibold text-slate-800">Variant:</span> {{ selectedColorName }} / {{ storageLabel }}</p>
            </div>
          </template>
        </section>
      </div>

      <section ref="descriptionSection" class="mt-6 rounded-[32px] border border-slate-200 bg-white p-5 shadow-sm sm:mt-8 sm:p-6">
        <template v-if="loading || !product">
          <div class="space-y-3">
            <div class="h-7 w-48 animate-pulse rounded bg-slate-200" />
            <div class="h-5 w-full animate-pulse rounded bg-slate-100" />
            <div class="h-5 w-11/12 animate-pulse rounded bg-slate-100" />
            <div class="h-5 w-10/12 animate-pulse rounded bg-slate-100" />
            <div class="h-5 w-8/12 animate-pulse rounded bg-slate-100" />
          </div>
        </template>

        <template v-else>
          <h2 class="text-xl font-semibold tracking-[-0.02em] text-slate-950 sm:text-2xl">Description</h2>
          <div
            class="prose prose-slate mt-4 max-w-none text-sm leading-8 sm:text-[15px]"
            v-html="product.long_description || product.short_description || '<p>No description available.</p>'"
          />
        </template>
      </section>

      <section ref="specificationSection" class="mt-6 rounded-[32px] border border-slate-200 bg-white p-5 shadow-sm sm:mt-8 sm:p-6">
        <template v-if="loading || !product">
          <div class="space-y-3">
            <div class="h-7 w-52 animate-pulse rounded bg-slate-200" />
            <div
              v-for="index in 8"
              :key="`spec-skeleton-${index}`"
              class="grid grid-cols-[160px_minmax(0,1fr)] gap-3 rounded-2xl border border-slate-100 px-4 py-4"
            >
              <div class="h-4 animate-pulse rounded bg-slate-200" />
              <div class="h-4 animate-pulse rounded bg-slate-100" />
            </div>
          </div>
        </template>

        <template v-else>
          <h2 class="text-xl font-semibold tracking-[-0.02em] text-slate-950 sm:text-2xl">Specifications</h2>

          <div class="mt-5 overflow-hidden rounded-[24px] border border-slate-200">
            <div
              v-for="(spec, index) in product.specifications"
              :key="`${spec.label}-${index}`"
              class="grid gap-3 border-b border-slate-200 px-4 py-4 text-sm last:border-b-0 sm:grid-cols-[240px_minmax(0,1fr)] sm:px-5"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-slate-50/70'"
            >
              <div class="font-semibold text-slate-700">{{ spec.label }}</div>
              <div class="text-slate-600">{{ spec.value }}</div>
            </div>
          </div>
        </template>
      </section>
    </template>
  </div>
</template>
