<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue'
import Product from './Product.vue'

defineOptions({
  layout: AppLayout,
})

defineProps<{
  products: any
  activeCategory?: string | null
}>()
</script>

<template>
  <Product :products="products" :activeCategory="activeCategory" />
</template>
