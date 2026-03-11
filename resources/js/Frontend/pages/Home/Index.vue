<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue'
import Product from './Product.vue'
import HomeBanner from './HomeBanner.vue'
import TechCategories from './techcategories.vue'

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
  categories: Array<{
    id: number | string
    name: string
    image_url: string | null
    status?: string | null
  }>
}>()
</script>

<template>
  <HomeBanner :banners="banners" />
  <TechCategories :categories="categories" />
  <Product :products="products" :activeCategory="activeCategory" />
</template>