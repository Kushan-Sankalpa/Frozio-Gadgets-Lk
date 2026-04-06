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
    height: '800px',
    autoplayMs: 5000,
    slides: () => [
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
        title: 'Meet Samsung S36 Ultra',
        description:
          'Step into the next generation with the Samsung S36 Ultra. Built with a sleek modern profile, immersive display presence, and a confident premium finish, it delivers a standout flagship feel crafted for users who want power, style, and everyday impact.',
        image: '/assets/images/s24.webp',
        alt: 'Samsung S26 Ultra',
      },
    ],
  },
)

const currentIndex = ref(0)
const loading = ref(true)
const error = ref('')
const loadedImages = ref<string[]>([])

let sliderTimer: number | null = null

const currentSlide = computed(() => props.slides[currentIndex.value] ?? props.slides[0])

const preloadImages = async () => {
  loading.value = true
  error.value = ''

  try {
    const promises = props.slides.map(
      (slide) =>
        new Promise<string>((resolve, reject) => {
          const img = new Image()
          img.onload = () => resolve(slide.image)
          img.onerror = () => reject(new Error(`Failed to load image: ${slide.image}`))
          img.src = slide.image
        }),
    )

    loadedImages.value = await Promise.all(promises)
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
  if (!error.value) startAutoplay()
})

onBeforeUnmount(() => {
  stopAutoplay()
})
</script>

<template>
  <section
    class="phone-showcase relative w-full overflow-hidden bg-black text-white"
    :style="{ '--phone-stage-height': height }"
  >
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_left_top,rgba(255,255,255,0.06),transparent_28%)]" />
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_right_center,rgba(255,255,255,0.05),transparent_26%)]" />
      <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.02),transparent_18%,transparent_82%,rgba(255,255,255,0.02))]" />
    </div>

    <div class="phone-showcase__shell relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="phone-showcase__grid grid items-center gap-10 lg:grid-cols-[minmax(0,0.88fr)_minmax(0,1.12fr)] xl:gap-16">
        <div class="order-2 lg:order-1">
          <div class="phone-showcase__copy-stage">
            <Transition name="showcase-copy" mode="out-in">
              <div :key="currentSlide.id" class="phone-showcase__copy-slide">
                <div
                  class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.05] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/75 sm:text-xs"
                >
                  {{ currentSlide.badge }}
                </div>

                <h2
                  class="mt-5 max-w-[11ch] text-[42px] font-semibold leading-[0.9] tracking-[-0.05em] text-white sm:text-[58px] lg:text-[68px] xl:text-[84px]"
                >
                  {{ currentSlide.title }}
                </h2>

                <p class="mt-5 max-w-[60ch] text-[15px] leading-7 text-white/72 sm:text-base sm:leading-8">
                  {{ currentSlide.description }}
                </p>
              </div>
            </Transition>
          </div>
        </div>

        <div class="order-1 lg:order-2">
          <div
            class="phone-showcase__stage-wrap relative ml-auto w-full"
            @mouseenter="stopAutoplay"
            @mouseleave="startAutoplay"
          >
            <div class="phone-showcase__stage">
              <Transition name="showcase-image" mode="out-in">
                <img
                  v-if="!loading && !error"
                  :key="currentSlide.id"
                  :src="currentSlide.image"
                  :alt="currentSlide.alt"
                  class="phone-showcase__image"
                  draggable="false"
                >
              </Transition>
            </div>

            <div
              v-if="loading"
              class="phone-showcase__loading pointer-events-none absolute inset-0 z-20"
              role="status"
              aria-live="polite"
            >
              <div class="phone-showcase__loading-card">
                <div class="phone-showcase__spinner" />
                <p class="phone-showcase__loading-text">Loading ...</p>
              </div>
            </div>

            <div
              v-if="error"
              class="absolute inset-0 z-30 flex items-center justify-center px-6 text-center"
            >
              <div class="rounded-2xl border border-red-300/25 bg-red-500/15 px-5 py-4 text-sm text-white">
                {{ error }}
              </div>
            </div>

            <!--
            <div
              v-if="!loading && !error && props.slides.length > 1"
              class="phone-showcase__dots"
            >
              <button
                v-for="(slide, index) in props.slides"
                :key="slide.id"
                type="button"
                class="phone-showcase__dot"
                :class="{ 'phone-showcase__dot--active': index === currentIndex }"
                :aria-label="`Go to slide ${index + 1}`"
                @click="goToSlide(index)"
              />
            </div>
            -->
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.phone-showcase {
  min-height: 820px;
}

.phone-showcase__shell {
  min-height: 820px;
  display: flex;
  align-items: center;
  padding-top: 20px;
  padding-bottom: 20px;
}

.phone-showcase__grid {
  width: 100%;
}

.phone-showcase__copy-stage {
  position: relative;
  height: 360px;
  overflow: hidden;
}

.phone-showcase__copy-slide {
  position: absolute;
  inset: 0;
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.phone-showcase__stage-wrap {
  width: 100%;
  max-width: 100%;
}

.phone-showcase__stage {
  position: relative;
  width: 100%;
  height: clamp(400px, 96vw, 640px);
  min-height: clamp(400px, 96vw, 640px);
  overflow: hidden;
  border-radius: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.phone-showcase__image {
  display: block;
  width: 100%;
  height: 100%;
  max-width: 92%;
  max-height: 92%;
  object-fit: contain;
  object-position: center;
  user-select: none;
  -webkit-user-drag: none;
  will-change: transform, opacity;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  transform: translateZ(0);
}

.phone-showcase__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.18);
  border-radius: 28px;
}

.phone-showcase__loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.85rem;
  width: min(88%, 340px);
}

.phone-showcase__spinner {
  width: 56px;
  height: 56px;
  border-radius: 999px;
  border: 3px solid rgba(255, 255, 255, 0.16);
  border-top-color: rgba(255, 255, 255, 0.95);
  animation: showcase-spin 0.9s linear infinite;
}

.phone-showcase__loading-text {
  margin: 0;
  text-align: center;
  font-size: clamp(13px, 2.8vw, 16px);
  font-weight: 600;
  letter-spacing: 0.02em;
  color: rgba(255, 255, 255, 0.92);
}

.phone-showcase__dots {
  position: absolute;
  bottom: 18px;
  left: 50%;
  z-index: 30;
  display: flex;
  gap: 10px;
  transform: translateX(-50%);
}

.phone-showcase__dot {
  width: 10px;
  height: 10px;
  border: 0;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.28);
  cursor: pointer;
  transition:
    transform 0.35s ease,
    background-color 0.35s ease,
    box-shadow 0.35s ease;
}

.phone-showcase__dot:hover {
  transform: scale(1.15);
  background: rgba(255, 255, 255, 0.55);
}

.phone-showcase__dot--active {
  background: #fff;
  box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.08);
}

.showcase-copy-enter-active,
.showcase-copy-leave-active {
  transition:
    opacity 0.8s ease,
    transform 0.8s ease;
}

.showcase-copy-enter-from {
  opacity: 0;
  transform: translateY(26px);
}

.showcase-copy-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.showcase-image-enter-active,
.showcase-image-leave-active {
  transition:
    opacity 0.95s ease,
    transform 0.95s cubic-bezier(0.22, 1, 0.36, 1);
}

.showcase-image-enter-from {
  opacity: 0;
  transform: scale(0.96) translateY(16px);
}

.showcase-image-leave-to {
  opacity: 0;
  transform: scale(1.03) translateY(-12px);
}

@keyframes showcase-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 1023px) {
  .phone-showcase,
  .phone-showcase__shell {
    min-height: auto;
  }

  .phone-showcase__shell {
    display: block;
    padding-top: 32px;
    padding-bottom: 32px;
  }

  .phone-showcase__copy-stage {
    height: 320px;
    margin-top: 8px;
  }
}

@media (max-width: 640px) {
  .phone-showcase__stage {
    height: clamp(360px, 92vw, 500px);
    min-height: clamp(360px, 92vw, 500px);
    border-radius: 22px;
  }

  .phone-showcase__image {
    max-width: 94%;
    max-height: 94%;
  }

  .phone-showcase__copy-stage {
    height: 300px;
  }

  .phone-showcase__spinner {
    width: 46px;
    height: 46px;
  }

  .phone-showcase__dot {
    width: 9px;
    height: 9px;
  }
}

@media (min-width: 1024px) {
  .phone-showcase__stage-wrap {
    max-width: 760px;
  }

  .phone-showcase__stage {
    height: var(--phone-stage-height);
    min-height: var(--phone-stage-height);
  }
}
</style>