<script setup lang="ts">
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import Product from './Product.vue'
import HomeBanner from './HomeBanner.vue'
import TechCategories from './techcategories.vue'
import ShoeCategories from './shoecategories.vue'
import ShoeFeaturedProducts from './ShoeFeaturedProducts.vue'
import Cosmetics from './Cosmetics.vue'
import FeaturedProducts from './FeaturedProducts.vue'
import ThreeDPhone from '../../components/3dphone.vue'
import ThreeDShoe from '../../components/3dshoe.vue'
import CardSwiper from './cardsswiper.vue'

defineOptions({
  layout: AppLayout,
})



defineProps<{
  products: Array<{
    id: number | string
    name: string
    category_name?: string | null
    brand_name?: string | null
    thumbnail_url: string | null
    hover_image_url: string | null
    regular_price: number | null
    display_price: number | null
    has_discount: boolean
    discount_label?: string | null
    is_sold_out: boolean
    reviews_count?: number
    reviews_avg_rating?: number | null
    colors?: Array<{
      id: number | string
      name: string
      color_code?: string | null
      image_url?: string | null
    }>
  }>
  activeCategory?: string | null
  activeBrand?: string | null
  search?: string | null
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
  shoeCategories: Array<{
    id: number | string
    name: string
    image_url: string | null
    status?: string | null
  }>
  featuredShoes: Array<{
    id: number | string
    name: string
    slug?: string | null
    brand_name?: string | null
    thumbnail_url: string | null
    hover_image_url: string | null
    currency?: string | null
    regular_price: number | null
    sale_price: number | null
    display_price: number | null
    has_discount: boolean
    discount_label?: string | null
    is_sold_out: boolean
    status?: string | null
    stock_status?: string | null
  }>
}>()
</script>

<!--k-->

<template>
  <HomeBanner :banners="banners" />

  <TechCategories :categories="categories" />

  <ThreeDPhone
    model-path="/models/iphone_17_pro_max.glb"
    height="700px"
  />

  <FeaturedProducts
    endpoint="/featured-products"
    :active-category="activeCategory"
    :active-brand="activeBrand"
    :search="search"
  />

  <section id="products-section">
    <Product
      :products="products"
      :categories="categories"
      :activeCategory="activeCategory"
    />
  </section>

  <ShoeCategories :categories="shoeCategories" />

  <ThreeDShoe
    model-path="/models/air_jordan_1.glb"
    height="680px"
  />

  <ShoeFeaturedProducts :products="featuredShoes" />

  <Cosmetics />

  <CardSwiper />
</template>