<script setup>
import Topnavigationbar from './partials/Topnavigationbar.vue'
import Footer from './partials/Footer.vue'
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Topnavigationbar />

    <main class="flex-1">
      <slot />
    </main>

    <Footer />
  </div>
</template>
