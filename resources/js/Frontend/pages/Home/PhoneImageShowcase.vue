<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'

const DOTLOTTIE_SCRIPT_SRC =
  'https://unpkg.com/@lottiefiles/dotlottie-wc@0.9.3/dist/dotlottie-wc.js'

const DOTLOTTIE_ANIMATION_SRC =
  'https://lottie.host/7799d1df-13f4-4ae5-9498-c5bbc8362c42/ROZeoYHWYC.lottie'

const props = withDefaults(
  defineProps<{
    imagePath?: string
    height?: string
    alt?: string
  }>(),
{
  imagePath: '/assets/images/ip171.png',
  height: '800px',
  alt: 'iPhone 17 Pro Max',
}
)

const loading = ref(true)
const error = ref('')
const imageReady = ref(false)

let dotLottieScriptEl: HTMLScriptElement | null = null
let preloadImage: HTMLImageElement | null = null

const ensureDotLottieScript = () => {
  if (typeof window === 'undefined') return
  if (document.querySelector('script[data-dotlottie-wc="true"]')) return

  dotLottieScriptEl = document.createElement('script')
  dotLottieScriptEl.src = DOTLOTTIE_SCRIPT_SRC
  dotLottieScriptEl.type = 'module'
  dotLottieScriptEl.async = true
  dotLottieScriptEl.dataset.dotlottieWc = 'true'
  document.head.appendChild(dotLottieScriptEl)
}

const loadImage = (src: string) => {
  loading.value = true
  error.value = ''
  imageReady.value = false

  if (!src) {
    error.value = 'Failed to load image.'
    loading.value = false
    return
  }

  preloadImage = new Image()

  preloadImage.onload = () => {
    imageReady.value = true
    loading.value = false
  }

  preloadImage.onerror = () => {
    error.value = 'Failed to load image.'
    loading.value = false
    imageReady.value = false
  }

  preloadImage.src = src
}

watch(
  () => props.imagePath,
  (newPath) => {
    loadImage(newPath)
  },
  { immediate: true },
)

onMounted(() => {
  ensureDotLottieScript()
})

onBeforeUnmount(() => {
  if (preloadImage) {
    preloadImage.onload = null
    preloadImage.onerror = null
    preloadImage = null
  }
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

    <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-1">
      <div class="grid items-center gap-10 lg:grid-cols-[minmax(0,0.88fr)_minmax(0,1.12fr)] xl:gap-16">
        <div class="order-2 lg:order-1">
          <div
            class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.05] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/75 sm:text-xs"
          >
            Pre-Order Available
          </div>

          <h2
            class="mt-5 max-w-[11ch] text-[42px] font-semibold leading-[0.9] tracking-[-0.05em] text-white sm:text-[58px] lg:text-[68px] xl:text-[84px]"
          >
            Introducing iPhone 17 Pro Max
          </h2>

          <p class="mt-5 max-w-[60ch] text-[15px] leading-7 text-white/72 sm:text-base sm:leading-8">
            Discover a bold new flagship experience with the iPhone 17 Pro Max. Designed to look
            premium from every angle, it brings a refined silhouette, a striking pro finish, and a
            powerful first impression that belongs at the center of your next upgrade.
          </p>
        </div>

        <div class="order-1 lg:order-2">
          <div class="phone-showcase__stage-wrap relative ml-auto w-full">
            <div class="phone-showcase__stage">
              <img
                v-if="imageReady && !error"
                :src="imagePath"
                :alt="alt"
                class="phone-showcase__image"
                draggable="false"
              >
            </div>

            <div
              v-if="loading"
              class="phone-showcase__loading pointer-events-none absolute inset-0 z-20"
              role="status"
              aria-live="polite"
            >
              <div class="phone-showcase__loading-card">
                <component
                  :is="'dotlottie-wc'"
                  :src="DOTLOTTIE_ANIMATION_SRC"
                  class="phone-showcase__loader-anim"
                  autoplay
                  loop
                />
                <p class="phone-showcase__loading-text">Loading ...</p>
              </div>
            </div>

            <div
              v-if="error"
              class="absolute inset-0 z-30 flex items-center justify-center px-6 text-center"
            >
              <div
                class="rounded-2xl border border-red-300/25 bg-red-500/15 px-5 py-4 text-sm text-white"
              >
                {{ error }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.phone-showcase__stage-wrap {
  width: 100%;
  max-width: 100%;
}

.phone-showcase__stage {
  position: relative;
  width: 100%;
  height: clamp(400px, 96vw, 640px);
  overflow: hidden;
  background:
    radial-gradient(circle at 50% 40%, rgba(255, 255, 255, 0.05), transparent 24%),
    radial-gradient(circle at 50% 84%, rgba(255, 255, 255, 0.03), transparent 26%),
    #000;
  border-radius: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.phone-showcase__image {
  width: 100%;
  height: 100%;
  max-width: 92%;
  max-height: 92%;
  object-fit: contain;
  object-position: center;
  user-select: none;
  -webkit-user-drag: none;
  filter: drop-shadow(0 18px 42px rgba(0, 0, 0, 0.45));
}

.phone-showcase__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  backdrop-filter: blur(2px);
  background: rgba(0, 0, 0, 0.18);
  border-radius: 28px;
}

.phone-showcase__loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  width: min(88%, 340px);
}

.phone-showcase__loader-anim {
  width: clamp(118px, 30vw, 300px);
  height: clamp(118px, 30vw, 300px);
}

.phone-showcase__loading-text {
  margin: 0;
  text-align: center;
  font-size: clamp(13px, 2.8vw, 16px);
  font-weight: 600;
  letter-spacing: 0.02em;
  color: rgba(255, 255, 255, 0.92);
}

@media (max-width: 640px) {
  .phone-showcase__stage {
    height: clamp(360px, 92vw, 500px);
    border-radius: 22px;
  }

  .phone-showcase__image {
    max-width: 94%;
    max-height: 94%;
  }

  .phone-showcase__loading-card {
    gap: 0.25rem;
  }

  .phone-showcase__loader-anim {
    width: clamp(100px, 34vw, 150px);
    height: clamp(100px, 34vw, 150px);
  }
}

@media (min-width: 1024px) {
  .phone-showcase__stage-wrap {
    max-width: 760px;
  }

  .phone-showcase__stage {
    height: var(--phone-stage-height);
  }
}
</style>