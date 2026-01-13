<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Home</h1>

    <!-- Products Grid -->
    <div v-if="products?.data?.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="p in products.data"
        :key="p.id"
        class="bg-white rounded-lg shadow overflow-hidden"
      >
        <!-- Image -->
        <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
          <img
            v-if="p.image"
            :src="p.image"
            :alt="p.name"
            class="w-full h-48 object-cover"
          />
          <div v-else class="text-gray-400 text-sm">No Image</div>
        </div>

        <!-- Content -->
        <div class="p-4">
          <h3 class="text-base font-semibold line-clamp-2">{{ p.name }}</h3>
          <p class="text-gray-800 mt-2 font-medium">{{ formatPrice(p.price) }}</p>
          <p v-if="p.created_at" class="text-xs text-gray-500 mt-2">
            Added: {{ formatDate(p.created_at) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center text-gray-500 py-12">
      No products available.
    </div>

    <!-- Pagination (Vue 3 safe) -->
    <div v-if="products?.links?.length" class="mt-8 flex flex-wrap justify-center gap-2">
      <template v-for="(link, i) in products.links" :key="i">
        <Link
          v-if="link.url"
          :href="link.url"
          class="px-3 py-2 rounded border text-sm"
          :class="link.active
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
          v-html="link.label"
        />
        <span
          v-else
          class="px-3 py-2 rounded border text-sm text-gray-400 bg-gray-100 border-gray-200 cursor-not-allowed"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  products: {
    type: Object,
    required: true,
  },
});

const formatPrice = (value) => {
  const num = Number(value || 0);
  return new Intl.NumberFormat('en-LK', {
    style: 'currency',
    currency: 'LKR',
    maximumFractionDigits: 0,
  }).format(num);
};

const formatDate = (iso) => {
  try {
    return new Date(iso).toLocaleDateString();
  } catch (e) {
    return '';
  }
};
</script>
