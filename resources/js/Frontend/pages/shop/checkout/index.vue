
<script setup lang="ts">
import { computed, ref } from 'vue'
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
  clearCart,
} = useCart()

const contactForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  postal_code: '',
  delivery_note: '',
  payment_method: 'cash_on_delivery',
})

const shippingFee = computed(() => subtotal.value > 0 ? 1500 : 0)
const grandTotal = computed(() => subtotal.value + shippingFee.value)
const orderPlaced = ref(false)

function submitOrder() {
  if (!items.value.length) return
  orderPlaced.value = true
  clearCart()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <div class="min-h-screen bg-[#f8f8fa] text-slate-950">
    <TopNavigator />

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
      <div class="flex flex-col gap-3 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Checkout</p>
          <h1 class="mt-2 text-3xl font-semibold tracking-[-0.03em] text-slate-950 sm:text-[2.35rem]">
            Secure Checkout
          </h1>
          <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600 sm:text-[15px]">
            Complete your delivery details and review the order summary before placing your order.
          </p>
        </div>

        <p class="text-sm font-medium text-slate-500">
          {{ totalItems }} item{{ totalItems === 1 ? '' : 's' }}
        </p>
      </div>

      <div
        v-if="orderPlaced"
        class="mt-8 rounded-[30px] border border-emerald-200 bg-emerald-50 px-6 py-10 text-center shadow-sm"
      >
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-emerald-600 shadow-sm">
          <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
            <path
              fill-rule="evenodd"
              d="M16.704 5.29a1 1 0 010 1.415l-7.25 7.25a1 1 0 01-1.415 0l-3-3a1 1 0 111.414-1.414l2.293 2.293 6.543-6.544a1 1 0 011.415 0z"
              clip-rule="evenodd"
            />
          </svg>
        </div>

        <h2 class="mt-5 text-2xl font-semibold text-emerald-700">Order placed successfully</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-emerald-700/80 sm:text-[15px]">
          This page is ready for your real backend checkout flow. For now it confirms the order and clears the local cart.
        </p>

        <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
          <Link
            :href="route('frontend.tech-products.index')"
            class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800"
          >
            Continue Shopping
          </Link>

          <Link
            :href="route('frontend.cart.index')"
            class="inline-flex min-h-[52px] items-center justify-center rounded-full border border-slate-900 px-7 text-sm font-semibold text-slate-950 transition hover:bg-slate-50"
          >
            Back to Cart
          </Link>
        </div>
      </div>

      <div
        v-else-if="!items.length"
        class="mt-8 rounded-[32px] border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm"
      >
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50">
          <svg class="h-8 w-8 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
            <circle cx="10" cy="20" r="1.5" />
            <circle cx="18" cy="20" r="1.5" />
          </svg>
        </div>

        <h2 class="mt-5 text-2xl font-semibold text-slate-950">No items available for checkout</h2>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-500 sm:text-[15px]">
          Add products to the cart first, then return here to complete the purchase.
        </p>

        <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
          <Link
            :href="route('frontend.tech-products.index')"
            class="inline-flex min-h-[52px] items-center justify-center rounded-full bg-slate-950 px-7 text-sm font-semibold text-white transition hover:bg-slate-800"
          >
            Browse Products
          </Link>

          <Link
            :href="route('frontend.cart.index')"
            class="inline-flex min-h-[52px] items-center justify-center rounded-full border border-slate-900 px-7 text-sm font-semibold text-slate-950 transition hover:bg-slate-50"
          >
            View Cart
          </Link>
        </div>
      </div>

      <div
        v-else
        class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]"
      >
        <section class="space-y-6">
          <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-xl font-semibold text-slate-950">Contact Information</h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">First name</span>
                <input
                  v-model="contactForm.first_name"
                  type="text"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>

              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Last name</span>
                <input
                  v-model="contactForm.last_name"
                  type="text"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>

              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Email address</span>
                <input
                  v-model="contactForm.email"
                  type="email"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>

              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Phone number</span>
                <input
                  v-model="contactForm.phone"
                  type="text"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>
            </div>
          </div>

          <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-xl font-semibold text-slate-950">Shipping Address</h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
              <label class="space-y-2 sm:col-span-2">
                <span class="text-sm font-medium text-slate-700">Address line 1</span>
                <input
                  v-model="contactForm.address_line_1"
                  type="text"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>

              <label class="space-y-2 sm:col-span-2">
                <span class="text-sm font-medium text-slate-700">Address line 2</span>
                <input
                  v-model="contactForm.address_line_2"
                  type="text"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>

              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">City</span>
                <input
                  v-model="contactForm.city"
                  type="text"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>

              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Postal code</span>
                <input
                  v-model="contactForm.postal_code"
                  type="text"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>

              <label class="space-y-2 sm:col-span-2">
                <span class="text-sm font-medium text-slate-700">Delivery note</span>
                <textarea
                  v-model="contactForm.delivery_note"
                  rows="4"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-slate-400"
                />
              </label>
            </div>
          </div>

          <div class="rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-xl font-semibold text-slate-950">Payment Method</h2>

            <div class="mt-5 grid gap-3">
              <label class="flex items-center gap-3 rounded-[22px] border border-slate-200 px-4 py-4">
                <input
                  v-model="contactForm.payment_method"
                  type="radio"
                  value="cash_on_delivery"
                  class="h-4 w-4"
                />
                <span class="text-sm font-medium text-slate-800">Cash on delivery</span>
              </label>

              <label class="flex items-center gap-3 rounded-[22px] border border-slate-200 px-4 py-4">
                <input
                  v-model="contactForm.payment_method"
                  type="radio"
                  value="card_on_delivery"
                  class="h-4 w-4"
                />
                <span class="text-sm font-medium text-slate-800">Card on delivery</span>
              </label>

              <label class="flex items-center gap-3 rounded-[22px] border border-slate-200 px-4 py-4">
                <input
                  v-model="contactForm.payment_method"
                  type="radio"
                  value="bank_transfer"
                  class="h-4 w-4"
                />
                <span class="text-sm font-medium text-slate-800">Bank transfer</span>
              </label>
            </div>
          </div>
        </section>

        <aside class="h-fit rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
          <h2 class="text-xl font-semibold text-slate-950">Order Summary</h2>

          <div class="mt-5 space-y-4">
            <article
              v-for="item in items"
              :key="item.key"
              class="flex gap-3 rounded-[22px] bg-slate-50 p-3"
            >
              <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-[18px] bg-white">
                <img
                  v-if="item.image"
                  :src="item.image"
                  :alt="item.name"
                  class="h-full w-full object-contain p-2"
                />
              </div>

              <div class="min-w-0 flex-1">
                <h3 class="line-clamp-2 text-sm font-semibold leading-6 text-slate-950">
                  {{ item.name }}
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                  Qty {{ item.quantity }}
                  <span v-if="item.colorName"> • {{ item.colorName }}</span>
                  <span v-if="item.storageLabel"> • {{ item.storageLabel }}</span>
                </p>
                <p class="mt-2 text-sm font-semibold text-slate-950">
                  {{ formatPrice(item.price * item.quantity) }}
                </p>
              </div>
            </article>
          </div>

          <div class="mt-6 space-y-3 border-t border-slate-200 pt-5 text-sm">
            <div class="flex items-center justify-between">
              <span class="text-slate-500">Subtotal</span>
              <span class="font-semibold text-slate-950">{{ formatPrice(subtotal) }}</span>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-slate-500">Shipping</span>
              <span class="font-semibold text-slate-950">{{ formatPrice(shippingFee) }}</span>
            </div>

            <div class="flex items-center justify-between pt-2 text-base">
              <span class="font-medium text-slate-700">Total</span>
              <span class="text-2xl font-semibold text-slate-950">{{ formatPrice(grandTotal) }}</span>
            </div>
          </div>

          <button
            type="button"
            class="mt-6 inline-flex min-h-[54px] w-full items-center justify-center rounded-full bg-slate-950 px-6 text-sm font-semibold text-white transition hover:bg-slate-800"
            @click="submitOrder"
          >
            Place Order
          </button>

          <Link
            :href="route('frontend.cart.index')"
            class="mt-3 inline-flex min-h-[52px] w-full items-center justify-center rounded-full border border-slate-900 px-6 text-sm font-semibold text-slate-950 transition hover:bg-slate-50"
          >
            Back to Cart
          </Link>
        </aside>
      </div>
    </main>
  </div>
</template>
