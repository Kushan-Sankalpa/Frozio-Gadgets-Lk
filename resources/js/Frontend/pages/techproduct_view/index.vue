<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import TechView from './partials/techview.vue'

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
  productId: number | string
  shell?: ShellData | null
}>()

const loading = ref(true)
const error = ref<string | null>(null)
const product = ref<ProductPayload | null>(null)

let abortController: AbortController | null = null

const pageTitle = computed(() => {
  return product.value?.name || props.shell?.name || 'Tech Product Details'
})

async function fetchProduct() {
  loading.value = true
  error.value = null

  try {
    abortController?.abort()
    abortController = new AbortController()

    const response = await fetch(`/tech-products/${props.productId}/data`, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      signal: abortController.signal,
    })

    if (!response.ok) {
      throw new Error('Failed to load product details.')
    }

    const data = await response.json()
    product.value = data?.product ?? null
  } catch (err: unknown) {
    if ((err as Error)?.name === 'AbortError') {
      return
    }

    console.error('Tech product view fetch error:', err)
    error.value = 'Failed to load product details. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProduct()
})

onBeforeUnmount(() => {
  abortController?.abort()
})
</script>

<template>
  <Head :title="pageTitle" />

  <div class="min-h-screen bg-[#f8fafc]">
    <TechView
      :loading="loading"
      :error="error"
      :product="product"
      :shell="shell || null"
      @retry="fetchProduct"
    />
  </div>
</template>
