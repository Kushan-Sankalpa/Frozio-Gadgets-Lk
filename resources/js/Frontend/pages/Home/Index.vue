<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue'
import Product from './Product.vue'
import HomeBanner from './HomeBanner.vue'

defineOptions({
  layout: AppLayout,
})

defineProps<{
  products: any
  activeCategory?: string | null
  banners: Array<{
    id: number | string
    name?: string
    description?: string | null
    video_url: string | null
  }>
}>()
</script>

<template>
  <!-- Full width banner -->
  <HomeBanner :banners="banners" />

  <!-- Products -->
  <Product :products="products" :activeCategory="activeCategory" />
</template>
