<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import ProductReviewsPanel from '@/Frontend/components/ProductReviewsPanel.vue'

type BreadcrumbItem = { label: string; href: string | null }
type ShellData = { name?: string | null; breadcrumb?: BreadcrumbItem[] }
type GalleryItem = { id: string | number; src: string }

type Volume = { id: number | string; label: string }

type CosmeticVariant = {
  id: string | number
  volume_id?: number | string | null
  volume_label?: string | null
  sku?: string | null
  price_lkr: number
  old_price_lkr?: number | null
  final_price_lkr?: number | null
  stock_count?: number | null
  in_stock?: boolean
  status?: string | null
  discount_label?: string | null
}

type ProductPayload = {
  id: number | string
  name: string
  slug?: string | null
  sku?: string | null
  short_description?: string | null
  long_description?: string | null
  brand?: { id?: number | string | null; name?: string | null; logo_url?: string | null } | null
  category?: { id?: number | string | null; name?: string | null } | null
  subcategory?: { id?: number | string | null; name?: string | null } | null
  breadcrumb?: BreadcrumbItem[]
  main_image?: string | null
  gallery: GalleryItem[]
  volumes: Volume[]
  variants: CosmeticVariant[]
  size_chart_image?: string | null
  base_price?: number | null
  old_price?: number | null
  current_price?: number | null
  has_discount?: boolean
  discount_label?: string | null
  default_volume_id?: number | string | null
  stock_count?: number | null
  in_stock?: boolean
  reviews_count?: number | null
  reviews_avg_rating?: number | null
}

type TabKey = 'description' | 'size-chart' | 'delivery' | 'reviews'

const props = defineProps<{
  loading: boolean
  error: string | null
  product: ProductPayload | null
  shell?: ShellData | null
}>()

const emit = defineEmits<{
  (e: 'retry'): void
}>()

const selectedVolumeId = ref<number | string | null>(null)
const activeImage = ref<string | null>(null)
const quantity = ref(1)
const activeTab = ref<TabKey>('description')
const flashMessage = ref('')

const reviewsFetchUrl = computed(() => {
  const key = props.product?.slug || props.product?.id
  if (!key) return ''
  return `/cosmetic-products/${key}/reviews`
})

const breadcrumbItems = computed(() => {
  return props.product?.breadcrumb || props.shell?.breadcrumb || [
    { label: 'Home', href: '/' },
    { label: 'Cosmetic Products', href: '/cosmetic-products' },
    { label: props.shell?.name || 'Product', href: null },
  ]
})

const volumes = computed(() => props.product?.volumes ?? [])
const productVariants = computed(() => props.product?.variants ?? [])

const thumbnailImages = computed(() => {
  const gallery = props.product?.gallery ?? []
  const mainImage = props.product?.main_image

  if (mainImage && !gallery.some((item) => item.src === mainImage)) {
    return [{ id: 'main-image', src: mainImage }, ...gallery].slice(0, 6)
  }

  return gallery.slice(0, 6)
})

const currentVariant = computed<CosmeticVariant | null>(() => {
  const exactInStock = productVariants.value.find((variant) => {
    return variant.volume_id === selectedVolumeId.value && variant.status !== 'inactive' && variant.in_stock
  })
  if (exactInStock) return exactInStock

  const exactActive = productVariants.value.find((variant) => {
    return variant.volume_id === selectedVolumeId.value && variant.status !== 'inactive'
  })
  if (exactActive) return exactActive

  return productVariants.value.find((variant) => variant.in_stock) || productVariants.value.find((variant) => variant.status !== 'inactive') || productVariants.value[0] || null
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

const stockCount = computed(() => Number(currentVariant.value?.stock_count ?? props.product?.stock_count ?? 0))

const isInStock = computed(() => {
  if (currentVariant.value) {
    return !!currentVariant.value.in_stock && stockCount.value > 0
  }

  return !!props.product?.in_stock && Number(props.product?.stock_count ?? 0) > 0
})

const availabilityText = computed(() => (isInStock.value ? 'In Stock' : 'Out of Stock'))

const selectedVolumeLabel = computed(() => {
  const matched = volumes.value.find((item) => item.id === selectedVolumeId.value)
  return matched?.label || currentVariant.value?.volume_label || 'N/A'
})

const canIncreaseQty = computed(() => {
  return isInStock.value && quantity.value < Math.max(1, stockCount.value)
})

const displayImage = computed(() => {
  return activeImage.value || props.product?.main_image || props.product?.gallery?.[0]?.src || ''
})

const sizeChartImage = computed(() => props.product?.size_chart_image || '/assets/images/volume-chart.webp')

const currentProductUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return window.location.pathname
  }

  const key = props.product?.slug || props.product?.id
  return props.product && key
    ? route('frontend.cosmetic-products.show', { product: key })
    : null
})

const deliveryParagraphs = [
  'We partner with dependable courier providers to ensure each parcel reaches you securely and within the usual delivery window.',
  'Dispatches are handled from Monday through Saturday. Deliveries are not scheduled on Sundays or mercantile holidays, and shipping charges are applied separately.',
  'Orders confirmed during weekends will be processed from Monday. We always try to respect special delivery notes where possible, although that may slightly extend the standard delivery timeline.',
  'While we make every effort to send and deliver orders on time, occasional delays can happen due to circumstances outside normal operations. When that occurs, we will move your order forward as quickly as possible.',
]

function formatPrice(value: number | null | undefined) {
  if (value === null || typeof value === 'undefined' || Number.isNaN(Number(value))) {
    return 'Rs 0.00'
  }

  return `Rs ${Number(value).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

function clampRating(value: number | null | undefined) {
  const rating = Number(value ?? 0)
  if (!Number.isFinite(rating)) return 0
  return Math.max(0, Math.min(5, rating))
}

function formatRating(value: number | null | undefined) { return clampRating(value).toFixed(1) }

function starFillStyle(value: number | null | undefined, starNumber: number) {
  const rating = clampRating(value)
  const fill = Math.max(0, Math.min(1, rating - (starNumber - 1)))
  const unfilledPercent = (1 - fill) * 100
  return { clipPath: `inset(0 ${unfilledPercent}% 0 0)` }
}

function ratingAriaLabel(value: number | null | undefined, count: number | null | undefined) {
  const rating = formatRating(value)
  const reviews = Number(count ?? 0)
  if (reviews > 0) return `${rating} out of 5 stars based on ${reviews} reviews`
  return `${rating} out of 5 stars`
}

function showMessage(message: string) {
  flashMessage.value = message
  window.setTimeout(() => { if (flashMessage.value === message) flashMessage.value = '' }, 2200)
}

function ensureSelections() {
  if (!props.product) return

  const defaultVariant = currentVariant.value

  selectedVolumeId.value = props.product.default_volume_id ?? defaultVariant?.volume_id ?? volumes.value[0]?.id ?? null

  activeImage.value = props.product.main_image || thumbnailImages.value[0]?.src || null
  quantity.value = 1
}

function selectVolume(volumeId: number | string) { selectedVolumeId.value = volumeId }
function selectImage(src: string) { activeImage.value = src }
function setTab(tab: TabKey) { activeTab.value = tab }
function decreaseQuantity() { quantity.value = Math.max(1, quantity.value - 1) }
function increaseQuantity() { if (!canIncreaseQty.value) return; quantity.value += 1 }

function cartPayload() {
  if (!props.product || !currentVariant.value) return null
  return {
    productId: props.product.id,
    variantId: currentVariant.value.id,
    quantity: quantity.value,
    colorId: null,
    colorName: null,
    storageId: selectedVolumeId.value,
    storageLabel: selectedVolumeLabel.value !== 'N/A' ? selectedVolumeLabel.value : null,
    price: currentPrice.value,
    oldPrice: currentOldPrice.value,
    stockCount: stockCount.value,
    name: props.product.name,
    image: displayImage.value || props.product.main_image || null,
    url: currentProductUrl.value,
  }
}

function addToCart() {
  try {
    if (!props.product || !currentVariant.value || !isInStock.value) return
    const payload = cartPayload(); if (!payload) return
    window.dispatchEvent(new CustomEvent('cosmetic-product:add-to-cart', { detail: payload }))
    showMessage('Product added to cart.')
  } catch (error) { console.error('Error while adding product to cart:', error) }
}

function buyNow() {
  try {
    if (!props.product || !currentVariant.value || !isInStock.value) return
    const payload = cartPayload(); if (!payload) return
    window.dispatchEvent(new CustomEvent('cosmetic-product:add-to-cart', { detail: payload }))
    showMessage('Ready for checkout.')
    router.visit(route('frontend.checkout.index'))
  } catch (error) { console.error('Error while processing buy now:', error) }
}

watch(() => props.product, () => { selectedVolumeId.value = null; activeImage.value = null; ensureSelections() }, { immediate: true })
watch(currentVariant, () => { quantity.value = Math.min(quantity.value, Math.max(1, stockCount.value)) })
</script>

<template>
  <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <nav aria-label="Breadcrumb" class="mb-6 page-enter">
      <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <li v-for="(item, index) in breadcrumbItems" :key="`${item.label}-${index}`" class="flex items-center gap-2">
          <template v-if="item.href">
            <Link :href="item.href" class="transition hover:text-slate-900">{{ item.label }}</Link>
          </template>
          <template v-else>
            <span class="font-medium text-slate-900">{{ item.label }}</span>
          </template>
          <span v-if="index < breadcrumbItems.length - 1" class="text-slate-300">/</span>
        </li>
      </ol>
    </nav>

    <Transition name="fade-slide" mode="out-in">
      <div v-if="flashMessage" key="flash-message" class="mb-4 border-b border-emerald-200 pb-3 text-sm font-medium text-emerald-700">{{ flashMessage }}</div>
    </Transition>

    <div v-if="error" class="rounded-[28px] border border-red-200 bg-red-50 px-6 py-12 text-center">
      <h3 class="text-lg font-semibold text-red-700">Failed to load product</h3>
      <p class="mt-2 text-sm text-red-500">Please try again or refresh.</p>

      <button type="button" class="mt-5 inline-flex items-center justify-center rounded-2xl bg-neutral-900 px-5 py-3 text-sm font-semibold text-white" @click="emit('retry')">Retry</button>
    </div>

    <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
      <div class="lg:col-span-2">
        <div class="rounded-[28px] border border-neutral-200 bg-white p-4 sm:p-6">
          <div class="grid grid-cols-1 gap-6 md:grid-cols-6">
            <div class="md:col-span-1">
              <div class="space-y-3">
                <button v-for="img in thumbnailImages" :key="img.id" type="button" class="block w-full overflow-hidden rounded-lg border border-neutral-100 bg-neutral-50 p-2" @click="selectImage(img.src)">
                  <img :src="img.src" :alt="`thumb-${img.id}`" class="h-20 w-full object-contain" />
                </button>
              </div>
            </div>

            <div class="md:col-span-5">
              <div class="flex items-start gap-6">
                <div class="w-full rounded-lg border border-neutral-100 bg-white p-4">
                  <img :src="displayImage" :alt="props.product?.name || 'product'" class="mx-auto max-h-[520px] object-contain" />
                </div>

                <div class="hidden w-64 shrink-0 md:block"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 rounded-[28px] border border-neutral-200 bg-white p-4 sm:p-6">
          <div class="prose max-w-none text-neutral-800" v-html="props.product?.long_description || props.product?.short_description || ''"></div>
        </div>
      </div>

      <aside class="space-y-5">
        <div class="overflow-hidden rounded-[28px] border border-neutral-200 bg-white p-5">
          <h1 class="text-lg font-semibold text-neutral-900">{{ props.product?.name || 'Product' }}</h1>
          <div class="mt-2 flex items-center justify-between gap-3">
            <div>
              <div class="text-sm text-neutral-500">Brand</div>
              <div class="text-sm font-medium text-neutral-900">{{ props.product?.brand?.name || '-' }}</div>
            </div>

            <div class="text-right">
              <div class="text-sm text-neutral-500">Availability</div>
              <div :class="isInStock ? 'text-emerald-600' : 'text-red-600'" class="text-sm font-semibold">{{ availabilityText }}</div>
            </div>
          </div>

          <div class="mt-4">
            <div class="text-sm text-neutral-500">Price</div>
            <div class="mt-1 flex items-baseline gap-3">
              <div class="text-2xl font-bold text-neutral-900">{{ formatPrice(currentPrice) }}</div>
              <div v-if="hasDiscount" class="text-sm font-semibold text-neutral-400 line-through">{{ formatPrice(currentOldPrice) }}</div>
            </div>
          </div>

          <div class="mt-4">
            <label class="block text-sm font-medium text-neutral-900">Volume</label>
            <div class="mt-2 flex flex-wrap gap-2">
              <button v-for="vol in volumes" :key="vol.id" type="button" class="rounded-full border px-3.5 py-2 text-sm font-medium" :class="selectedVolumeId === vol.id ? 'border-neutral-900 bg-neutral-900 text-white' : 'border-neutral-200 bg-white text-neutral-700'" @click="selectVolume(vol.id)">{{ vol.label }}</button>
            </div>
          </div>

          <div class="mt-4 flex items-center gap-3">
            <div class="inline-flex items-center gap-2 rounded-lg border border-neutral-200 bg-neutral-50 px-2 py-1">
              <button type="button" class="h-9 w-9 rounded-md bg-white text-neutral-900" @click="decreaseQuantity">-</button>
              <div class="min-w-[44px] text-center font-medium">{{ quantity }}</div>
              <button type="button" class="h-9 w-9 rounded-md bg-white text-neutral-900" @click="increaseQuantity">+</button>
            </div>

            <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-neutral-900 px-4 py-3 text-sm font-semibold text-white" @click="addToCart">Add to cart</button>
          </div>

          <div class="mt-3 text-sm text-neutral-500">SKU: {{ props.product?.sku || '-' }}</div>
        </div>
      </aside>
    </div>

    <div class="mt-6">
      <ProductReviewsPanel v-if="props.product" :fetch-url="reviewsFetchUrl" :initial-count="props.product?.reviews_count" />
    </div>
  </div>
</template>

<style scoped>
.fade-slide-enter-active, .fade-slide-leave-active { transition: opacity 0.24s ease, transform 0.24s ease }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-6px) }
.fade-slide-enter-to, .fade-slide-leave-from { opacity: 1; transform: translateY(0) }
</style>
