
<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import TopNavigator from '@/Frontend/layouts/partials/Topnavigationbar.vue'
import { useCart } from '../composables/useCart'

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
</script>

<template>
  <div class="min-h-screen bg-[#f8f8fa] text-slate-950">
    <TopNavigator />

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

      <div
        v-else
        class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]"
      >
        <section class="space-y-4">
          <article
            v-for="item in items"
            :key="item.key"
            class="rounded-[30px] border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
          >
            <div class="flex flex-col gap-4 sm:flex-row">
              <div class="flex h-[140px] w-full shrink-0 items-center justify-center overflow-hidden rounded-[24px] bg-slate-50 sm:w-[140px]">
                <img
                  v-if="item.image"
                  :src="item.image"
                  :alt="item.name"
                  class="h-full w-full object-contain p-4"
                />
                <div
                  v-else
                  class="flex h-full w-full items-center justify-center text-xs font-semibold uppercase tracking-[0.14em] text-slate-400"
                >
                  No Image
                </div>
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                  <div class="min-w-0">
                    <Link
                      v-if="item.url"
                      :href="item.url"
                      class="text-lg font-semibold leading-7 text-slate-950 transition hover:text-slate-700"
                    >
                      {{ item.name }}
                    </Link>

                    <h2 v-else class="text-lg font-semibold leading-7 text-slate-950">
                      {{ item.name }}
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2">
                      <span
                        v-if="item.colorName"
                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                      >
                        {{ item.colorName }}
                      </span>

                      <span
                        v-if="item.storageLabel"
                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"
                      >
                        {{ item.storageLabel }}
                      </span>
                    </div>
                  </div>

                  <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-red-200 hover:text-red-600"
                    @click="removeItem(item.key)"
                  >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        fill-rule="evenodd"
                        d="M6 8a.75.75 0 011.5 0v6a.75.75 0 01-1.5 0V8zm6.75-.75A.75.75 0 0012 8v6a.75.75 0 001.5 0V8a.75.75 0 00-.75-.75z"
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

                <div class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-end sm:justify-between">
                  <div>
                    <p
                      v-if="item.oldPrice && item.oldPrice > item.price"
                      class="text-sm text-slate-400 line-through"
                    >
                      {{ formatPrice(item.oldPrice) }}
                    </p>
                    <p class="text-xl font-semibold text-slate-950">
                      {{ formatPrice(item.price) }}
                    </p>
                  </div>

                  <div class="flex flex-wrap items-center justify-between gap-4 sm:justify-end">
                    <div class="inline-flex items-center gap-4 rounded-full border border-slate-200 px-4 py-2.5">
                      <button
                        type="button"
                        class="text-2xl leading-none text-slate-700 transition hover:text-slate-950"
                        @click="decrementQuantity(item.key)"
                      >
                        −
                      </button>
                      <span class="min-w-[24px] text-center text-base font-semibold text-slate-950">
                        {{ item.quantity }}
                      </span>
                      <button
                        type="button"
                        class="text-2xl leading-none text-slate-700 transition hover:text-slate-950"
                        @click="incrementQuantity(item.key)"
                      >
                        +
                      </button>
                    </div>

                    <p class="text-lg font-semibold text-slate-950">
                      {{ formatPrice(item.price * item.quantity) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </article>
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
    </main>
  </div>
</template>
