<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import { useCart } from '../composables/useCart'

defineOptions({
  layout: AppLayout,
})

type TechProductCard = {
  id: number | string
  name: string
  category_name?: string | null
  brand_name?: string | null
  thumbnail_url?: string | null
  hover_image_url?: string | null
  regular_price?: number | null
  display_price?: number | null
  has_discount?: boolean
  discount_label?: string | null
  is_sold_out?: boolean
  url?: string | null
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

const {
  items,
  subtotal,
  totalItems,
  incrementQuantity,
  decrementQuantity,
  removeItem,
} = useCart()

const estimatedDelivery = computed(() => {
  const today = new Date()
  const end = new Date(today)
  end.setDate(today.getDate() + 3)

  return end.toLocaleDateString('en-LK', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
})

const relatedProducts = ref<TechProductCard[]>([])
const loadingRelated = ref(false)
let abortController: AbortController | null = null

const productIdsSignature = computed(() => {
  return items.value
    .map((item: any) => String(item.productId ?? ''))
    .filter(Boolean)
    .sort()
    .join(',')
})

async function fetchRelatedProducts() {
  const productIds = items.value
    .map((item: any) => Number(item.productId))
    .filter((id: number) => Number.isFinite(id) && id > 0)

  if (!productIds.length) {
    relatedProducts.value = []
    return
  }

  loadingRelated.value = true

  try {
    abortController?.abort()
    abortController = new AbortController()

    const params = new URLSearchParams()
    productIds.forEach((id) => {
      params.append('product_ids[]', String(id))
    })

    const response = await fetch(
      `${route('frontend.tech-products.cart-related')}?${params.toString()}`,
      {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        signal: abortController.signal,
      },
    )

    if (!response.ok) {
      throw new Error('Failed to load related products.')
    }

    const payload = await response.json()
    relatedProducts.value = Array.isArray(payload?.products) ? payload.products.slice(0, 4) : []
  } catch (error: any) {
    if (error?.name !== 'AbortError') {
      relatedProducts.value = []
    }
  } finally {
    loadingRelated.value = false
  }
}

watch(productIdsSignature, () => {
  fetchRelatedProducts()
}, { immediate: true })

onBeforeUnmount(() => {
  abortController?.abort()
})
</script>

<template>
  <Head title="Cart" />

  <div class="min-h-screen bg-[#f8f8fa] text-slate-950">
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
      <div class="flex flex-col gap-3 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Cart Page</p>
          <h1 class="mt-2 text-3xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-[2.35rem]">
            Your Shopping Cart
          </h1>
          <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:text-[15px]">
            Review your selected products, adjust quantities, and continue to checkout when you are ready.
          </p>
        </div>

        <p class="text-sm font-medium text-slate-500">
          {{ totalItems }} item{{ totalItems === 1 ? '' : 's' }}
        </p>
      </div>

      <div
        v-if="!items.length"
        class="mt-8 rounded-[32px] border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm"
      >
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50">
          <svg class="h-8 w-8 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
            <circle cx="10" cy="20" r="1.5" />
            <circle cx="18" cy="20" r="1.5" />
          </svg>
        </div>

        <h2 class="mt-5 text-2xl font-semibold text-slate-950">Your cart is empty</h2>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-500 sm:text-[15px]">
          Browse your product collection and add the items you want. They will appear here automatically.
        </p>

        <Link
          :href="route('frontend.tech-products.index')"
          class="mt-7 inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
          Continue Shopping
        </Link>
      </div>

      <template v-else>
        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
          <section class="rounded-[30px] border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="divide-y divide-slate-200">
              <article
                v-for="item in items"
                :key="item.key"
                class="grid grid-cols-[72px_minmax(0,1fr)] gap-4 py-5 first:pt-0 last:pb-0 sm:grid-cols-[88px_minmax(0,1fr)_auto] sm:gap-6"
              >
                <div class="flex h-[72px] w-[72px] items-center justify-center overflow-hidden sm:h-[88px] sm:w-[88px]">
                  <img
                    v-if="item.image"
                    :src="item.image"
                    :alt="item.name"
                    class="max-h-full max-w-full object-contain"
                  />
                  <div
                    v-else
                    class="flex h-full w-full items-center justify-center text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-400"
                  >
                    No Image
                  </div>
                </div>

                <div class="min-w-0">
                  <div class="flex items-start justify-between gap-4 sm:hidden">
                    <div class="min-w-0">
                      <Link
                        v-if="item.url"
                        :href="item.url"
                        class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900 transition hover:text-slate-700"
                      >
                        {{ item.name }}
                      </Link>

                      <p
                        v-else
                        class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900"
                      >
                        {{ item.name }}
                      </p>
                    </div>

                    <div class="shrink-0 text-right">
                      <p
                        v-if="item.oldPrice && item.oldPrice > item.price"
                        class="text-xs text-slate-400 line-through"
                      >
                        {{ formatPrice(item.oldPrice) }}
                      </p>
                      <p class="text-[15px] font-medium text-slate-800">
                        {{ formatPrice(item.price) }}
                      </p>
                    </div>
                  </div>

                  <div class="hidden sm:block">
                    <Link
                      v-if="item.url"
                      :href="item.url"
                      class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900 transition hover:text-slate-700"
                    >
                      {{ item.name }}
                    </Link>

                    <p
                      v-else
                      class="line-clamp-2 text-[15px] font-semibold leading-7 text-slate-900"
                    >
                      {{ item.name }}
                    </p>
                  </div>

                  <p
                    v-if="item.colorName || item.storageLabel"
                    class="mt-1 text-sm text-slate-500"
                  >
                    <span v-if="item.colorName">{{ item.colorName }}</span>
                    <span v-if="item.colorName && item.storageLabel"> • </span>
                    <span v-if="item.storageLabel">{{ item.storageLabel }}</span>
                  </p>

                  <div class="mt-3 flex items-center gap-4">
                    <div class="inline-flex h-9 items-center overflow-hidden rounded-md border border-slate-300">
                      <button
                        type="button"
                        class="flex h-full w-10 items-center justify-center text-lg text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                        @click="decrementQuantity(item.key)"
                      >
                        −
                      </button>

                      <span class="flex h-full min-w-[34px] items-center justify-center px-2 text-sm font-medium text-slate-800">
                        {{ item.quantity }}
                      </span>

                      <button
                        type="button"
                        class="flex h-full w-10 items-center justify-center text-lg text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                        @click="incrementQuantity(item.key)"
                      >
                        +
                      </button>
                    </div>

                    <button
                      type="button"
                      class="inline-flex h-8 w-8 items-center justify-center text-[#ef5a4f] transition hover:opacity-70"
                      @click="removeItem(item.key)"
                      aria-label="Remove item"
                    >
                      <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path
                          fill-rule="evenodd"
                          d="M6 8a.75.75 0 011.5 0v6A.75.75 0 016 14V8zm6.75-.75A.75.75 0 0012 8v6a.75.75 0 001.5 0V8a.75.75 0 00-.75-.75z"
                          clip-rule="evenodd"
                        />
                        <path
                          fill-rule="evenodd"
                          d="M14.5 4.75V4a1 1 0 00-1-1h-7a1 1 0 00-1 1v.75H3.75a.75.75 0 000 1.5h.598l.585 8.768A2 2 0 006.93 17.9h6.14a2 2 0 001.997-1.882l.585-8.768h.598a.75.75 0 000-1.5H14.5zm-7.5-.75h6v.75h-6V4zm-.563 2.25h7.126l-.55 8.256a.5.5 0 01-.499.469H6.986a.5.5 0 01-.499-.469l-.55-8.256z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>
                </div>

                <div class="hidden shrink-0 text-right sm:block">
                  <p
                    v-if="item.oldPrice && item.oldPrice > item.price"
                    class="text-xs text-slate-400 line-through"
                  >
                    {{ formatPrice(item.oldPrice) }}
                  </p>
                  <p class="text-[16px] font-medium text-slate-800">
                    {{ formatPrice(item.price) }}
                  </p>
                </div>
              </article>
            </div>
          </section>

          <aside class="h-fit rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-xl font-semibold text-slate-950">Order Summary</h2>

            <div class="mt-6 space-y-4 border-b border-slate-200 pb-5">
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Items</span>
                <span class="font-semibold text-slate-950">{{ totalItems }}</span>
              </div>

              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Estimated delivery</span>
                <span class="font-semibold text-slate-950">{{ estimatedDelivery }}</span>
              </div>

              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Shipping</span>
                <span class="font-semibold text-emerald-600">Calculated at checkout</span>
              </div>
            </div>

            <div class="mt-5 flex items-center justify-between">
              <span class="text-base font-medium text-slate-600">Subtotal</span>
              <span class="text-2xl font-semibold text-slate-950">{{ formatPrice(subtotal) }}</span>
            </div>

            <Link
              :href="route('frontend.checkout.index')"
              class="mt-6 inline-flex min-h-[54px] w-full items-center justify-center rounded-full bg-slate-950 px-6 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
              Proceed to Checkout
            </Link>

            <Link
              :href="route('frontend.tech-products.index')"
              class="mt-3 inline-flex min-h-[52px] w-full items-center justify-center rounded-full border border-slate-900 px-6 text-sm font-semibold text-slate-950 transition hover:bg-slate-50"
            >
              Continue Shopping
            </Link>
          </aside>
        </div>

        <section v-if="loadingRelated || relatedProducts.length" class="mt-12">
          <div class="flex items-end justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                Recommendations
              </p>
              <h2 class="mt-2 text-2xl font-semibold tracking-[-0.02em] text-slate-950">
                You may also like
              </h2>
            </div>
          </div>

          <div
            v-if="loadingRelated"
            class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
          >
            <div
              v-for="n in 4"
              :key="`related-skeleton-${n}`"
              class="rounded-[28px] border border-slate-200 bg-white p-4 shadow-sm"
            >
              <div class="h-[220px] animate-pulse rounded-[22px] bg-slate-100" />
              <div class="mt-4 h-5 animate-pulse rounded bg-slate-100" />
              <div class="mt-2 h-5 w-2/3 animate-pulse rounded bg-slate-100" />
              <div class="mt-4 h-6 w-24 animate-pulse rounded bg-slate-200" />
            </div>
          </div>

          <div
            v-else
            class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
          >
            <article
              v-for="product in relatedProducts"
              :key="product.id"
              class="group overflow-hidden rounded-[28px] border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
            >
              <Link :href="product.url || `/tech-products/${product.id}`" class="block">
                <div class="flex h-[220px] items-center justify-center overflow-hidden rounded-[22px] bg-slate-50 p-4">
                  <img
                    v-if="product.thumbnail_url"
                    :src="product.thumbnail_url"
                    :alt="product.name"
                    class="max-h-full max-w-full object-contain transition duration-300 group-hover:scale-[1.03]"
                  />
                </div>

                <div class="mt-4">
                  <p v-if="product.category_name" class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                    {{ product.category_name }}
                  </p>

                  <h3 class="mt-2 line-clamp-2 text-[15px] font-semibold leading-6 text-slate-950">
                    {{ product.name }}
                  </h3>

                  <div class="mt-3 flex items-center gap-2">
                    <span
                      v-if="product.has_discount && product.regular_price"
                      class="text-sm text-slate-400 line-through"
                    >
                      {{ formatPrice(product.regular_price) }}
                    </span>

                    <span class="text-lg font-semibold text-slate-950">
                      {{ formatPrice(product.display_price) }}
                    </span>
                  </div>
                </div>
              </Link>
            </article>
          </div>
        </section>
      </template>
    </main>
  </div>
</template>