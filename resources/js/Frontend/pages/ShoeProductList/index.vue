<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '../../layouts/AppLayout.vue'
import ShoeProductFilter from './partials/ShoeProductFilter.vue'
import ShoeProductList from './partials/ShoeProductList.vue'

defineOptions({
  layout: AppLayout,
})

type ShoeSubcategory = {
  id: number | string
  name: string
  image_url?: string | null
  status?: string | null
}

type ShoeCategory = {
  id: number | string
  name: string
  image_url?: string | null
  status?: string | null
  subcategories?: ShoeSubcategory[]
}

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

type Filters = {
  search?: string | null
  shoe_category?: string | null
  shoe_subcategory?: string | null
  stock?: string | null
  sale?: boolean
  sort?: string | null
  min_price?: string | number | null
  max_price?: string | number | null
  page?: number
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
  categories?: unknown[]
  shoeCategories: ShoeCategory[]
  filters: Filters
}>()

const page = usePage()

const products = ref<ShoeProductCard[]>([])
const loading = ref(false)
const loadingMore = ref(false)
const loadError = ref('')
const pagination = ref<PaginationMeta>({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0,
  from: 0,
  to: 0,
})

let batchTimer: ReturnType<typeof setTimeout> | null = null
let activeController: AbortController | null = null

const currentFilters = computed(() => ({
  search: props.filters?.search || '',
  shoe_category: props.filters?.shoe_category || '',
  shoe_subcategory: props.filters?.shoe_subcategory || '',
  stock: props.filters?.stock || '',
  sale: !!props.filters?.sale,
  sort: props.filters?.sort || 'latest',
  min_price: props.filters?.min_price ?? '',
  max_price: props.filters?.max_price ?? '',
  page: Number(props.filters?.page || 1),
}))

const heading = computed(() => {
  if (currentFilters.value.shoe_subcategory) {
    return currentFilters.value.shoe_subcategory
  }

  if (currentFilters.value.shoe_category) {
    return `${currentFilters.value.shoe_category} Shoes`
  }

  return 'All Shoe Products'
})

const breadcrumbItems = computed(() => {
  const items = [
    {
      label: 'Home',
      href: route('frontend.root'),
    },
    {
      label: 'Shoe Products',
      href: route('frontend.shoe-products.index'),
    },
  ]

  if (currentFilters.value.shoe_category) {
    items.push({
      label: currentFilters.value.shoe_category,
      href: route('frontend.shoe-products.index', {
        shoe_category: currentFilters.value.shoe_category,
      }),
    })
  }

  if (currentFilters.value.shoe_subcategory) {
    items.push({
      label: currentFilters.value.shoe_subcategory,
      href: route('frontend.shoe-products.index', {
        shoe_category: currentFilters.value.shoe_category || undefined,
        shoe_subcategory: currentFilters.value.shoe_subcategory,
      }),
    })
  }

  return items
})

function cleanParams(params: Record<string, unknown>) {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => {
      if (value === null || typeof value === 'undefined') return false
      if (typeof value === 'string' && !value.trim()) return false
      if (typeof value === 'boolean') return value
      return true
    })
  )
}

function buildApiUrl() {
  const params = new URLSearchParams()

  if (currentFilters.value.search) {
    params.set('search', currentFilters.value.search)
  }

  if (currentFilters.value.shoe_category) {
    params.set('shoe_category', currentFilters.value.shoe_category)
  }

  if (currentFilters.value.shoe_subcategory) {
    params.set('shoe_subcategory', currentFilters.value.shoe_subcategory)
  }

  if (currentFilters.value.stock) {
    params.set('stock', currentFilters.value.stock)
  }

  if (currentFilters.value.sale) {
    params.set('sale', '1')
  }

  if (currentFilters.value.sort) {
    params.set('sort', currentFilters.value.sort)
  }

  if (String(currentFilters.value.min_price).trim()) {
    params.set('min_price', String(currentFilters.value.min_price))
  }

  if (String(currentFilters.value.max_price).trim()) {
    params.set('max_price', String(currentFilters.value.max_price))
  }

  params.set('page', String(currentFilters.value.page || 1))

  return `${route('frontend.shoe-products.products')}?${params.toString()}`
}

async function fetchProducts() {
  if (batchTimer) {
    clearTimeout(batchTimer)
    batchTimer = null
  }

  if (activeController) {
    activeController.abort()
    activeController = null
  }

  const controller = new AbortController()
  activeController = controller

  loading.value = true
  loadingMore.value = false
  loadError.value = ''
  products.value = []

  try {
    const response = await fetch(buildApiUrl(), {
      method: 'GET',
      signal: controller.signal,
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error('Failed to load products')
    }

    const data = await response.json()

    if (activeController !== controller) return

    const loadedProducts = Array.isArray(data?.products) ? data.products : []

    pagination.value = {
      current_page: Number(data?.pagination?.current_page || 1),
      last_page: Number(data?.pagination?.last_page || 1),
      per_page: Number(data?.pagination?.per_page || 12),
      total: Number(data?.pagination?.total || 0),
      from: Number(data?.pagination?.from || 0),
      to: Number(data?.pagination?.to || 0),
    }

    products.value = loadedProducts.slice(0, 6)
    loading.value = false

    const remainingProducts = loadedProducts.slice(6)

    if (remainingProducts.length) {
      loadingMore.value = true

      batchTimer = setTimeout(() => {
        products.value = [...products.value, ...remainingProducts]
        loadingMore.value = false
      }, 350)
    }
  } catch (error) {
    if ((error as Error)?.name === 'AbortError') return

    console.error('Shoe products fetch error:', error)
    loading.value = false
    loadingMore.value = false
    loadError.value = 'Unable to load shoe products right now.'
    products.value = []
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 12,
      total: 0,
      from: 0,
      to: 0,
    }
  }
}

function visitWithFilters(nextFilters: Partial<Filters>, replace = true) {
  const merged = {
    ...currentFilters.value,
    ...nextFilters,
  }

  if (!merged.shoe_category) {
    merged.shoe_subcategory = ''
  }

  router.get(
    route('frontend.shoe-products.index'),
    cleanParams({
      search: merged.search,
      shoe_category: merged.shoe_category,
      shoe_subcategory: merged.shoe_subcategory,
      stock: merged.stock,
      sale: merged.sale ? 1 : undefined,
      sort: merged.sort,
      min_price: merged.min_price,
      max_price: merged.max_price,
      page: merged.page || 1,
    }),
    {
      preserveState: true,
      preserveScroll: true,
      replace,
    }
  )
}

function applyFilters(nextFilters: Filters) {
  visitWithFilters(
    {
      ...nextFilters,
      page: 1,
    },
    true
  )
}

function resetFilters() {
  router.get(
    route('frontend.shoe-products.index'),
    {},
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

function changePage(pageNumber: number) {
  if (pageNumber === pagination.value.current_page) return

  visitWithFilters({ page: pageNumber }, false)

  requestAnimationFrame(() => {
    document.getElementById('shoe-product-list-section')?.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    })
  })
}

watch(
  () => page.url,
  () => {
    fetchProducts()
  },
  { immediate: true }
)

onBeforeUnmount(() => {
  if (batchTimer) {
    clearTimeout(batchTimer)
    batchTimer = null
  }

  if (activeController) {
    activeController.abort()
    activeController = null
  }
})
</script>

<template>
  <section class="bg-[#faf8f6] pb-16 pt-8 sm:pt-10 lg:pt-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <nav
        class="mb-6 flex flex-wrap items-center gap-2 text-sm text-neutral-500 sm:mb-8"
        aria-label="Breadcrumb"
      >
        <template v-for="(item, index) in breadcrumbItems" :key="`${item.label}-${index}`">
          <a
            :href="item.href"
            class="transition-colors hover:text-neutral-900"
          >
            {{ item.label }}
          </a>

          <span
            v-if="index < breadcrumbItems.length - 1"
            class="text-neutral-300"
          >
            /
          </span>
        </template>
      </nav>

      <div class="mb-8 flex flex-col gap-3 sm:mb-10 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <p class="mb-2 text-xs font-semibold uppercase tracking-[0.22em] text-neutral-400">
            Shoe Collection
          </p>

          <h1 class="text-3xl font-semibold tracking-[-0.03em] text-neutral-900 sm:text-4xl">
            {{ heading }}
          </h1>

          <p class="mt-2 max-w-2xl text-sm text-neutral-500 sm:text-base">
            Smooth shoe product listing page with instant navigation, lazy card loading, clean filters, and fast pagination.
          </p>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-600 shadow-sm">
          {{ pagination.total }} products found
        </div>
      </div>

      <div
        id="shoe-product-list-section"
        class="grid grid-cols-1 gap-8 xl:grid-cols-12"
      >
        <aside class="xl:col-span-3">
          <ShoeProductFilter
            :filters="currentFilters"
            :shoe-categories="shoeCategories"
            :loading="loading"
            @apply="applyFilters"
            @reset="resetFilters"
          />
        </aside>

        <div class="xl:col-span-9">
          <ShoeProductList
            :products="products"
            :loading="loading"
            :loading-more="loadingMore"
            :load-error="loadError"
            :pagination="pagination"
            @retry="fetchProducts"
            @page-change="changePage"
          />
        </div>
      </div>
    </div>
  </section>
</template>