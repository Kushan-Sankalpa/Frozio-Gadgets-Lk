<script setup lang="ts">
import FeaturedProducts from '@/Frontend/pages/Home/FeaturedProducts.vue'
</script>

<template>
  <FeaturedProducts
    endpoint="/home/featured-cosmetics"
    title="Featured Cosmetics"
    subtitle="Handpicked cosmetics chosen for formula, quality, and daily results."
    explore-href="#cosmetic-product-list-section"
    label-mode="brand"
    card-tagline="cosmetic collection"
  />
</template>

