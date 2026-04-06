<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

type ShowcaseSlide = {
  id: number | string
  badge: string
  title: string
  description: string
  image: string
  alt: string
}

const props = withDefaults(
  defineProps<{
    height?: string
    autoplayMs?: number
    slides?: ShowcaseSlide[]
  }>(),
  {
    height: '680px',
    autoplayMs: 5000,
    slides: () => [
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
    ],
  },
)

const currentIndex = ref(0)
const loading = ref(true)
const error = ref('')

let sliderTimer: number | null = null

const currentSlide = computed(() => props.slides[currentIndex.value] ?? props.slides[0])

const preloadImages = async () => {
  loading.value = true
  error.value = ''

  try {
    await Promise.all(
      props.slides.map(
        (slide) =>
          new Promise<void>((resolve, reject) => {
            const img = new Image()
            img.onload = () => resolve()
            img.onerror = () => reject(new Error(`Failed to load image: ${slide.image}`))
            img.src = slide.image
          }),
      ),
    )

    loading.value = false
  } catch (err) {
    console.error(err)
    error.value = 'Failed to load showcase images.'
    loading.value = false
  }
}

const nextSlide = () => {
  if (!props.slides.length) return
  currentIndex.value = (currentIndex.value + 1) % props.slides.length
}

const goToSlide = (index: number) => {
  currentIndex.value = index
  restartAutoplay()
}

const startAutoplay = () => {
  stopAutoplay()

  if (props.slides.length <= 1) return

  sliderTimer = window.setInterval(() => {
    nextSlide()
  }, props.autoplayMs)
}

const stopAutoplay = () => {
  if (sliderTimer !== null) {
    window.clearInterval(sliderTimer)
    sliderTimer = null
  }
}

const restartAutoplay = () => {
  startAutoplay()
}

onMounted(async () => {
  await preloadImages()

  if (!error.value) {
    startAutoplay()
  }
})

onBeforeUnmount(() => {
  stopAutoplay()
})

/*
  Old 3D / Lottie approach intentionally removed for performance.
  Kept as comment reference only.

import { onMounted, onBeforeUnmount, ref } from 'vue'
import * as THREE from 'three'
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js'
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js'

const DOTLOTTIE_SCRIPT_SRC =
  'https://unpkg.com/@lottiefiles/dotlottie-wc@0.9.3/dist/dotlottie-wc.js'

const DOTLOTTIE_ANIMATION_SRC =
  'https://lottie.host/6b1767c9-aa5a-4979-a1a6-ca0995bbf626/mqJgNxzu51.lottie'
*/
</script>

<template>
  <section
    class="relative overflow-hidden bg-white"
    :style="{ '--shoe-stage-height': height }"
  >
    <!-- original decorative background kept commented from old 3D version -->
    <!--
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_left_top,rgba(17,24,39,0.04),transparent_28%)]" />
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_right_center,rgba(17,24,39,0.035),transparent_26%)]" />
      <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(17,24,39,0.02),transparent_18%,transparent_82%,rgba(17,24,39,0.02))]" />
    </div>
    -->

    <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-1 lg:px-8 lg:py-1">
      <div class="grid items-center gap-10 lg:grid-cols-[minmax(0,1.08fr)_minmax(0,0.92fr)] xl:gap-16">
        <div class="order-1">
          <div
            class="shoe-showcase__stage-wrap relative mr-auto w-full"
            @mouseenter="stopAutoplay"
            @mouseleave="startAutoplay"
          >
            <div class="shoe-showcase__stage">
              <Transition name="shoe-image" mode="out-in">
                <img
                  v-if="!loading && !error"
                  :key="currentSlide.id"
                  :src="currentSlide.image"
                  :alt="currentSlide.alt"
                  class="shoe-showcase__image"
                  draggable="false"
                >
              </Transition>
            </div>

            <div
              v-if="loading"
              class="shoe-showcase__loading pointer-events-none absolute inset-0 z-20"
              role="status"
              aria-live="polite"
            >
              <div class="shoe-showcase__loading-card">
                <div class="shoe-showcase__spinner" />
                <p class="shoe-showcase__loading-text">Loading ...</p>
              </div>
            </div>

            <div
              v-if="error"
              class="absolute inset-0 z-30 flex items-center justify-center px-6 text-center"
            >
              <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 shadow-sm">
                {{ error }}
              </div>
            </div>

            <!-- <div
              v-if="!loading && !error && props.slides.length > 1"
              class="shoe-showcase__dots"
            >
              <button
                v-for="(slide, index) in props.slides"
                :key="slide.id"
                type="button"
                class="shoe-showcase__dot"
                :class="{ 'shoe-showcase__dot--active': index === currentIndex }"
                :aria-label="`Go to slide ${index + 1}`"
                @click="goToSlide(index)"
              />
            </div> -->
          </div>
        </div>

        <div class="order-2 overflow-hidden">
          <Transition name="shoe-copy" mode="out-in">
            <div :key="currentSlide.id">
              <div
                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 shadow-sm sm:text-xs"
              >
                {{ currentSlide.badge }}
              </div>

              <h2
                class="mt-5 max-w-[11ch] text-[44px] font-semibold leading-[0.92] tracking-[-0.05em] text-slate-900 sm:text-[56px] lg:text-[70px] xl:text-[86px]"
              >
                {{ currentSlide.title }}
              </h2>

              <p class="mt-5 max-w-[60ch] text-[15px] leading-7 text-slate-600 sm:text-base sm:leading-8">
                {{ currentSlide.description }}
              </p>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.shoe-showcase__stage-wrap {
  width: 100%;
  max-width: 100%;
}

.shoe-showcase__stage {
  position: relative;
  width: 100%;
  height: clamp(380px, 96vw, 620px);
  overflow: visible;
  border-radius: 0;
  border: 0;
  background: transparent;
  box-shadow: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.shoe-showcase__image {
  display: block;
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  object-position: center;
  user-select: none;
  -webkit-user-drag: none;
  will-change: transform, opacity;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  transform: translateZ(0);
}

.shoe-showcase__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.18);
}

.shoe-showcase__loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.85rem;
  width: min(88%, 340px);
}

.shoe-showcase__spinner {
  width: 56px;
  height: 56px;
  border-radius: 999px;
  border: 3px solid rgba(148, 163, 184, 0.22);
  border-top-color: rgb(51 65 85);
  animation: shoe-showcase-spin 0.9s linear infinite;
}

.shoe-showcase__loading-text {
  margin: 0;
  text-align: center;
  font-size: clamp(13px, 2.8vw, 16px);
  font-weight: 600;
  letter-spacing: 0.02em;
  color: rgb(71 85 105);
}

.shoe-showcase__dots {
  position: absolute;
  bottom: 18px;
  left: 50%;
  z-index: 30;
  display: flex;
  gap: 10px;
  transform: translateX(-50%);
}

.shoe-showcase__dot {
  width: 10px;
  height: 10px;
  border: 0;
  border-radius: 999px;
  background: rgba(51, 65, 85, 0.22);
  cursor: pointer;
  transition:
    transform 0.35s ease,
    background-color 0.35s ease,
    box-shadow 0.35s ease;
}

.shoe-showcase__dot:hover {
  transform: scale(1.15);
  background: rgba(51, 65, 85, 0.45);
}

.shoe-showcase__dot--active {
  background: rgb(15 23 42);
  box-shadow: 0 0 0 6px rgba(15, 23, 42, 0.08);
}

.shoe-copy-enter-active,
.shoe-copy-leave-active {
  transition:
    opacity 0.8s ease,
    transform 0.8s ease;
}

.shoe-copy-enter-from {
  opacity: 0;
  transform: translateY(24px);
}

.shoe-copy-leave-to {
  opacity: 0;
  transform: translateY(-18px);
}

.shoe-image-enter-active,
.shoe-image-leave-active {
  transition:
    opacity 0.95s ease,
    transform 0.95s cubic-bezier(0.22, 1, 0.36, 1);
}

.shoe-image-enter-from {
  opacity: 0;
  transform: scale(0.96) translateY(16px);
}

.shoe-image-leave-to {
  opacity: 0;
  transform: scale(1.03) translateY(-12px);
}

@keyframes shoe-showcase-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 640px) {
  .shoe-showcase__stage {
    height: clamp(340px, 92vw, 500px);
  }

  .shoe-showcase__loading-card {
    gap: 0.7rem;
  }

  .shoe-showcase__spinner {
    width: 46px;
    height: 46px;
  }

  .shoe-showcase__dots {
    bottom: 14px;
  }

  .shoe-showcase__dot {
    width: 9px;
    height: 9px;
  }
}

@media (min-width: 1024px) {
  .shoe-showcase__stage-wrap {
    max-width: 900px;
  }

  .shoe-showcase__stage {
    height: var(--shoe-stage-height);
  }
}
</style>