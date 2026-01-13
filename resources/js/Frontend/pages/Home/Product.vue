<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

defineProps<{
  products: any
  activeCategory?: string | null
}>()

const formatPrice = (n: number) =>
  new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR', maximumFractionDigits: 0 }).format(n || 0)
</script>

<template>
  <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Products</h1>
        <p class="mt-1 text-sm text-gray-600">
          <span v-if="activeCategory">Showing category: <b class="capitalize">{{ activeCategory }}</b></span>
          <span v-else>Showing latest items</span>
        </p>
      </div>

      <Link
        :href="route('frontend.root')"
        class="text-sm font-semibold text-gray-700 hover:text-black"
        v-if="activeCategory"
      >
        Clear filter
      </Link>
    </div>

    <div class="mt-8 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="p in products.data"
        :key="p.id"
        class="rounded-2xl border bg-white overflow-hidden hover:shadow-md transition"
      >
        <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
          <img
            v-if="p.image"
            :src="p.image"
            :alt="p.name"
            class="h-full w-full object-cover"
            loading="lazy"
          />
          <div v-else class="text-xs text-gray-400">No image</div>
        </div>

        <div class="p-3">
          <div class="text-sm font-bold text-gray-900 line-clamp-2">
            {{ p.name }}
          </div>
          <div class="mt-2 text-sm font-semibold text-gray-700">
            {{ formatPrice(p.price) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="products.links?.length" class="mt-10 flex flex-wrap gap-2">
      <Link
        v-for="(l, i) in products.links"
        :key="i"
        :href="l.url || ''"
        preserve-scroll
        class="px-3 py-2 text-sm rounded-lg border"
        :class="l.active ? 'bg-black text-white border-black' : 'bg-white text-gray-700 hover:bg-gray-100'"
        v-html="l.label"
        :disabled="!l.url"
      />
    </div>
  </section>
</template>
