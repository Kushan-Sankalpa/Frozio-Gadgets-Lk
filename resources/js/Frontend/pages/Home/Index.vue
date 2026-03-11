<script setup lang="ts">
import AppLayout from '../../layouts/AppLayout.vue'
import Product from './Product.vue'
import HomeBanner from './HomeBanner.vue'
import TechCategories from './techcategories.vue'
import ThreeDPhone from '../../components/3dphone.vue'
import ThreeDShoe from '../../components/3dshoe.vue'

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

  <ThreeDPhone
    model-path="/models/iphone_17_pro_max.glb"
    height="700px"
  />

  

  <Product :products="products" :activeCategory="activeCategory" />

  <ThreeDShoe
    model-path="/models/air_jordan_1.glb"
    height="680px"
  />
</template>