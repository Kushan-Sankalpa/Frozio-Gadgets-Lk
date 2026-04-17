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
import CosmeticImageShowcase from './CosmeticImageShowcase.vue'
import CosmeticsFeaturedProducts from './CosmeticsFeaturedProducts.vue'
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
      'Experience a sleek premium design, powerful performance, and a next-level flagship feel built for everyday excellence.',
    image: '/assets/images/ip171.webp',
    alt: 'iPhone 17 Pro Max',
  },
  {
    id: 2,
    badge: 'Coming Soon',
    title: 'Meet Samsung S26 Ultra',
    description:
      'A bold flagship with a stunning display, refined design, and powerful features made for work, play, and creativity.',
    image: '/assets/images/s24.webp',
    alt: 'Samsung S26 Ultra',
  },
]

const shoeSlides = [
  {
    id: 1,
    badge: 'New Arrival',
    title: 'Introducing Adidas Adizero Evo SL EXO',
    description:
      'Lightweight, fast, and built for comfort with a sleek performance-driven design.',
    image: '/assets/images/adizero.webp',
    alt: 'Adidas Adizero Evo SL EXO',
  },
]

const cosmeticSlides = [
  {
    id: 1,
    badge: 'Skin Essential',
    title: 'The Ordinary Niacinamide',
    description:
      'A lightweight everyday serum designed to help visibly reduce excess oil, refine pores, and support a smoother-looking complexion.',
    image: '/assets/images/serum5.png',
    alt: 'The Ordinary Niacinamide',
  },
  {
    id: 2,
    badge: 'Daily Hydration',
    title: 'CeraVe Moisturizer',
    description:
      'A simple daily moisturizer made to support long-lasting hydration and help keep the skin barrier feeling soft, balanced, and comfortable.',
    image: '/assets/images/ceravemois1.png',
    alt: 'CeraVe Moisturizer',
  },
  {
    id: 3,
    badge: 'Daily Protection',
    title: 'CeraVe Suncream',
    description:
      'An everyday sun protection essential created to help shield skin while maintaining a lightweight, comfortable feel throughout the day.',
    image: '/assets/images/ip171.webp',
    alt: 'CeraVe Suncream',
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

  <CosmeticImageShowcase
    :slides="cosmeticSlides"
    height="700px"
    :autoplay-ms="5000"
  />

  <CosmeticsFeaturedProducts />

  <!-- <Cosmetics /> -->

  <!-- <CardSwiper /> -->
</template>