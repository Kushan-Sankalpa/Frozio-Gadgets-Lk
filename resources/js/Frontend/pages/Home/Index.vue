<script setup lang="ts">
import AppLayout from '@/Frontend/layouts/AppLayout.vue'
import Product from './Product.vue'
import HomeBanner from './HomeBanner.vue'
import TechCategories from './techcategories.vue'
import ShoeCategories from './shoecategories.vue'
import ShoeFeaturedProducts from './ShoeFeaturedProducts.vue'
// import Cosmetics from './Cosmetics.vue'
import FeaturedProducts from './FeaturedProducts.vue'
// import ThreeDPhone from '../../components/3dphone.vue'
import PhoneImageShowcase from './PhoneImageShowcase.vue'
// import ThreeDShoe from '../../components/3dshoe.vue'
import ShoeImageShowcase from './ShoeImageShowcase.vue'
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

const phoneSlides = [
  {
    id: 1,
    badge: 'Pre-Order Available',
    title: 'Introducing iPhone 17 Pro Max',
    description:
      'Discover a bold new flagship experience with the iPhone 17 Pro Max. Designed to look premium from every angle, it brings a refined silhouette, a striking pro finish, and a powerful first impression that belongs at the center of your next upgrade.',
    image: '/assets/images/ip171.webp',
    alt: 'iPhone 17 Pro Max',
  },
  {
    id: 2,
    badge: 'Coming Soon',
    title: 'Meet Samsung S26 Ultra',
    description:
      'Step into the next generation with the Samsung S26 Ultra. Built with a sleek modern profile, immersive display presence, and a confident premium finish, it delivers a standout flagship feel crafted for users who want power, style, and everyday impact.',
    image: '/assets/images/s24.webp',
    alt: 'Samsung S26 Ultra',
  },
]

const shoeSlides = [
  {
    id: 1,
    badge: 'New Arrival',
    title: 'Introducing Air Jordan 1',
    description:
      'Step into an iconic silhouette reimagined for a bold modern statement. The Air Jordan 1 combines heritage design, standout details, and unmistakable attitude in a form that continues to define sneaker culture.',
    image: '/assets/images/adizero.webp',
    alt: 'Air Jordan 1',
  },
  {
    id: 2,
    badge: 'Trending Now',
    title: 'Introducing Adidas Samba',
    description:
      'Discover a timeless streetwear essential with the Adidas Samba. Clean lines, classic proportions, and everyday versatility come together in a legendary profile that keeps its style sharp across every generation.',
    image: '/assets/images/adizero1.webp',
    alt: 'Adidas Samba',
  },
]
</script>

<!--k-->

<template>
  <HomeBanner :banners="banners" />

  <TechCategories :categories="categories" />

  <!--
  <ThreeDPhone
    model-path="/models/iphone_17_pro_max.glb"
    height="700px"
  />
  -->

  <PhoneImageShowcase
    :slides="phoneSlides"
    height="700px"
    :autoplay-ms="5000"
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

  <!--
  <ThreeDShoe
    model-path="/models/air_jordan_1.glb"
    height="680px"
  />
  -->

  <ShoeImageShowcase
    :slides="shoeSlides"
    height="680px"
    :autoplay-ms="5000"
  />

  <ShoeFeaturedProducts :products="featuredShoes" />

  <!-- <Cosmetics /> -->

  <CardSwiper />
</template>