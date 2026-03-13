<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

type Banner = {
  id: number | string
  name?: string
  description?: string | null
  video_url: string | null
}

const props = defineProps<{
  banners: Banner[]
}>()

const slides = computed(() => (props.banners ?? []).filter(b => !!b?.video_url) as Required<Banner>[])
const hasSlides = computed(() => slides.value.length > 0)

const sectionRef = ref<HTMLElement | null>(null)
const trackRef = ref<HTMLDivElement | null>(null)
const active = ref(0)

const videoEls = ref<Array<HTMLVideoElement | null>>([])

const setVideoRef = (el: HTMLVideoElement | null, idx: number) => {
  videoEls.value[idx] = el
}

const readyMap = ref<Record<number, boolean>>({})
const shouldRenderVideos = ref(false)

let rafId: number | null = null
let autoplayTimer: number | null = null
let resumeTimer: number | null = null
let loadVideosTimer: number | null = null
let sectionObserver: IntersectionObserver | null = null

const userInteracting = ref(false)
const hasScheduledLoad = ref(false)

const setReady = (idx: number) => {
  readyMap.value = {
    ...readyMap.value,
    [idx]: true,
  }
}

const isReady = (idx: number) => !!readyMap.value[idx]

const clamp = (n: number, min: number, max: number) => Math.max(min, Math.min(max, n))

const scrollToIndex = (idx: number, behavior: ScrollBehavior = 'smooth') => {
  const el = trackRef.value
  if (!el) return
  const count = slides.value.length
  if (count === 0) return

  const i = clamp(idx, 0, count - 1)
  const w = el.clientWidth || 1
  el.scrollTo({ left: i * w, behavior })
  active.value = i
}

const next = () => {
  const count = slides.value.length
  if (count <= 1) return
  const i = (active.value + 1) % count
  scrollToIndex(i)
}

const prev = () => {
  const count = slides.value.length
  if (count <= 1) return
  const i = (active.value - 1 + count) % count
  scrollToIndex(i)
}

const onScroll = () => {
  const el = trackRef.value
  if (!el) return

  if (rafId) cancelAnimationFrame(rafId)
  rafId = requestAnimationFrame(() => {
    const w = el.clientWidth || 1
    const idx = clamp(Math.round(el.scrollLeft / w), 0, Math.max(0, slides.value.length - 1))
    if (idx !== active.value) active.value = idx
  })
}

const pauseAllExceptActive = async () => {
  if (!shouldRenderVideos.value) return

  const count = slides.value.length
  if (count === 0) return

  for (let i = 0; i < count; i++) {
    const v = videoEls.value[i]
    if (!v) continue

    v.muted = true
    v.playsInline = true
    v.loop = true

    if (i === active.value) {
      try {
        const p = v.play()
        // @ts-ignore
        if (p?.catch) p.catch(() => {})
      } catch {
        //
      }
    } else {
      try {
        v.pause()
        v.currentTime = 0
      } catch {
        //
      }
    }
  }
}

const stopAutoplay = () => {
  if (autoplayTimer) window.clearInterval(autoplayTimer)
  autoplayTimer = null
}

const startAutoplay = () => {
  stopAutoplay()
  if (!shouldRenderVideos.value) return
  if (slides.value.length <= 1) return

  autoplayTimer = window.setInterval(() => {
    if (userInteracting.value) return
    next()
  }, 6500)
}

const markInteraction = () => {
  userInteracting.value = true
  stopAutoplay()

  if (resumeTimer) window.clearTimeout(resumeTimer)
  resumeTimer = window.setTimeout(() => {
    userInteracting.value = false
    startAutoplay()
  }, 9000)
}

const handleResize = () => {
  scrollToIndex(active.value, 'auto')
}

const loadLottieScript = () => {
  if (typeof window === 'undefined') return
  if (document.querySelector('script[data-dotlottie-loader="true"]')) return
  if (customElements.get('dotlottie-wc')) return

  const script = document.createElement('script')
  script.src = 'https://unpkg.com/@lottiefiles/dotlottie-wc@0.9.3/dist/dotlottie-wc.js'
  script.type = 'module'
  script.setAttribute('data-dotlottie-loader', 'true')
  document.body.appendChild(script)
}

const scheduleVideoLoading = () => {
  if (hasScheduledLoad.value) return
  hasScheduledLoad.value = true

  if (loadVideosTimer) {
    window.clearTimeout(loadVideosTimer)
    loadVideosTimer = null
  }

  loadVideosTimer = window.setTimeout(async () => {
    shouldRenderVideos.value = true
    await nextTick()
    await pauseAllExceptActive()
    startAutoplay()
  }, 5000)
}

onMounted(async () => {
  loadLottieScript()

  await nextTick()

  if (hasSlides.value) {
    scrollToIndex(0, 'auto')
  }

  if ('IntersectionObserver' in window && sectionRef.value) {
    sectionObserver = new IntersectionObserver(
      (entries) => {
        const entry = entries[0]
        if (entry?.isIntersecting) {
          scheduleVideoLoading()

          if (sectionObserver) {
            sectionObserver.disconnect()
            sectionObserver = null
          }
        }
      },
      {
        root: null,
        rootMargin: '0px',
        threshold: 0.35,
      }
    )

    sectionObserver.observe(sectionRef.value)
  } else {
    scheduleVideoLoading()
  }

  window.addEventListener('resize', handleResize, { passive: true })
})

onBeforeUnmount(() => {
  stopAutoplay()

  if (resumeTimer) window.clearTimeout(resumeTimer)
  if (loadVideosTimer) window.clearTimeout(loadVideosTimer)
  if (rafId) cancelAnimationFrame(rafId)

  if (sectionObserver) {
    sectionObserver.disconnect()
    sectionObserver = null
  }

  window.removeEventListener('resize', handleResize)
})

watch(active, async () => {
  await nextTick()
  await pauseAllExceptActive()
})

watch(
  slides,
  async () => {
    active.value = 0
    readyMap.value = {}
    shouldRenderVideos.value = false
    hasScheduledLoad.value = false

    if (loadVideosTimer) {
      window.clearTimeout(loadVideosTimer)
      loadVideosTimer = null
    }

    await nextTick()
    scrollToIndex(0, 'auto')
  },
  { deep: true }
)
</script>

<template>
  <section v-if="hasSlides" ref="sectionRef" class="w-full">
    <div class="relative w-full">
      <div
        ref="trackRef"
        class="no-scrollbar flex w-full overflow-x-auto snap-x snap-mandatory scroll-smooth"
        @scroll.passive="onScroll"
        @pointerdown="markInteraction"
        @touchstart.passive="markInteraction"
        @wheel.passive="markInteraction"
      >
        <div
          v-for="(b, idx) in slides"
          :key="b.id"
          class="relative w-full flex-shrink-0 snap-center bg-black"
        >
          <div class="relative w-full aspect-[16/9] sm:aspect-[21/9] md:aspect-[24/9] overflow-hidden bg-black">
            <div
              v-if="!isReady(idx)"
              class="absolute inset-0 z-20 flex items-center justify-center bg-black"
            >
              <dotlottie-wc
                src="https://lottie.host/3aaa9185-2876-4f24-a9dd-7bcb4b6126ff/DqmYrIP5av.lottie"
                class="banner-loader"
                autoplay
                loop
              ></dotlottie-wc>
            </div>

            <video
              v-if="shouldRenderVideos"
              :ref="(el) => setVideoRef(el as HTMLVideoElement | null, idx)"
              :src="b.video_url || ''"
              class="h-full w-full object-cover transition-opacity duration-500"
              :class="isReady(idx) ? 'opacity-100' : 'opacity-0'"
              muted
              playsinline
              loop
              preload="none"
              @loadeddata="setReady(idx)"
              @canplay="setReady(idx)"
            />

            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/15 to-black/10" />

            <div
              v-if="b.name || b.description"
              class="absolute bottom-0 left-0 right-0 p-4 sm:p-6 md:p-8"
            >
            </div>
          </div>
        </div>
      </div>

      <button
        v-if="slides.length > 1"
        type="button"
        aria-label="Previous slide"
        class="absolute left-3 top-1/2 -translate-y-1/2 z-30 rounded-full bg-black/40 hover:bg-black/55 backdrop-blur px-3 py-3 text-white"
        @click="prev"
      >
        <span class="text-lg leading-none">‹</span>
      </button>

      <button
        v-if="slides.length > 1"
        type="button"
        aria-label="Next slide"
        class="absolute right-3 top-1/2 -translate-y-1/2 z-30 rounded-full bg-black/40 hover:bg-black/55 backdrop-blur px-3 py-3 text-white"
        @click="next"
      >
        <span class="text-lg leading-none">›</span>
      </button>

      <div
        v-if="slides.length > 1"
        class="absolute bottom-3 left-0 right-0 z-30 flex items-center justify-center gap-2"
      >
        <button
          v-for="(_, i) in slides"
          :key="i"
          type="button"
          :aria-label="`Go to slide ${i + 1}`"
          class="h-2.5 w-2.5 rounded-full transition"
          :class="i === active ? 'bg-white' : 'bg-white/40 hover:bg-white/60'"
          @click="scrollToIndex(i)"
        />
      </div>
    </div>
  </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.banner-loader {
  width: 110px;
  height: 110px;
}

@media (min-width: 640px) {
  .banner-loader {
    width: 160px;
    height: 160px;
  }
}

@media (min-width: 1024px) {
  .banner-loader {
    width: 220px;
    height: 220px;
  }
}
</style>