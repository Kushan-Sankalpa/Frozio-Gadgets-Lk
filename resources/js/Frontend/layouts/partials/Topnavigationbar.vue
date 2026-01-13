<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const open = ref(false)
const page = usePage()

const currentCategory = computed(() => {
  const url = page.url || ''
  const q = url.includes('?') ? url.split('?')[1] : ''
  return new URLSearchParams(q).get('category') || ''
})

const navItems = computed(() => [
  { label: 'Home', href: route('frontend.root'), key: '' },
  { label: 'Apple', href: route('frontend.root', { category: 'apple' }), key: 'apple' },
  { label: 'Android', href: route('frontend.root', { category: 'android' }), key: 'android' },
  { label: 'Pre Owned', href: route('frontend.root', { category: 'pre-owned' }), key: 'pre-owned' },
  { label: 'Speakers', href: route('frontend.root', { category: 'speakers' }), key: 'speakers' },
  { label: 'Accessories', href: route('frontend.root', { category: 'accessories' }), key: 'accessories' },
  { label: 'Cooling Products', href: route('frontend.root', { category: 'cooling-products' }), key: 'cooling-products' },
])

const isActive = (key: string) => {
  if (!key) return currentCategory.value === ''
  return currentCategory.value === key
}
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <!-- Logo -->
        <Link :href="route('frontend.root')" class="flex items-center gap-2">
          <img src="/assets/images/main-logo.png" alt="Logo" class="h-9 w-auto" />
          <span class="text-lg font-semibold text-gray-900">Store</span>
        </Link>

        <!-- Desktop Nav -->
        <nav class="hidden lg:flex items-center gap-1">
          <Link
            v-for="item in navItems"
            :key="item.label"
            :href="item.href"
            class="rounded-full px-4 py-2 text-sm font-semibold transition"
            :class="isActive(item.key)
              ? 'bg-black text-white'
              : 'text-gray-700 hover:bg-gray-100'"
          >
            {{ item.label }}
          </Link>
        </nav>

        <!-- Mobile button -->
        <button
          class="lg:hidden inline-flex items-center justify-center rounded-md border px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-100"
          type="button"
          @click="open = !open"
          :aria-expanded="open ? 'true' : 'false'"
        >
          Menu
        </button>
      </div>
    </div>

    <!-- Mobile Nav -->
    <div v-if="open" class="lg:hidden border-t bg-white">
      <div class="mx-auto max-w-7xl px-4 py-3 flex flex-col gap-1">
        <Link
          v-for="item in navItems"
          :key="item.label"
          :href="item.href"
          class="rounded-lg px-4 py-3 text-sm font-semibold transition"
          :class="isActive(item.key)
            ? 'bg-black text-white'
            : 'text-gray-700 hover:bg-gray-100'"
          @click="open = false"
        >
          {{ item.label }}
        </Link>
      </div>
    </div>
  </header>
</template>
