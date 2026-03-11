<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue'
import Product from './Product.vue'
import HomeBanner from './HomeBanner.vue'
import TechCategories from './techcategories.vue'
import ThreeDPhone from '../../components/3dphone.vue'

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

  <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    <ThreeDPhone
      model-path="/models/iphone_17_pro_max.glb"
      height="650px"
    />
  </section>

  <TechCategories :categories="categories" />
  <Product :products="products" :activeCategory="activeCategory" />
</template>