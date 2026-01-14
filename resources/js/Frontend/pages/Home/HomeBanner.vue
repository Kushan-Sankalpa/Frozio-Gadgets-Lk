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

const trackRef = ref<HTMLDivElement | null>(null)
const active = ref(0)

// Keep video refs (same order as slides)
const videoEls = ref<Array<HTMLVideoElement | null>>([])

const setVideoRef = (el: HTMLVideoElement | null, idx: number) => {
  videoEls.value[idx] = el
}

let rafId: number | null = null
let autoplayTimer: number | null = null
let resumeTimer: number | null = null
const userInteracting = ref(false)

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
  const count = slides.value.length
  if (count === 0) return

  for (let i = 0; i < count; i++) {
    const v = videoEls.value[i]
    if (!v) continue

    // Ensure autoplay is allowed
    v.muted = true
    v.playsInline = true
    v.loop = true

    if (i === active.value) {
      try {
        const p = v.play()
        // @ts-ignore
        if (p?.catch) p.catch(() => {})
      } catch {
        // ignore
      }
    } else {
      try {
        v.pause()
        v.currentTime = 0
      } catch {
        // ignore
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
  // keep the current slide aligned after resize
  scrollToIndex(active.value, 'auto')
}

onMounted(async () => {
  await nextTick()
  if (hasSlides.value) {
    scrollToIndex(0, 'auto')
    await pauseAllExceptActive()
    startAutoplay()
  }
  window.addEventListener('resize', handleResize, { passive: true })
})

onBeforeUnmount(() => {
  stopAutoplay()
  if (resumeTimer) window.clearTimeout(resumeTimer)
  if (rafId) cancelAnimationFrame(rafId)
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
    await nextTick()
    scrollToIndex(0, 'auto')
    await pauseAllExceptActive()
    startAutoplay()
  },
  { deep: true }
)
</script>

<template>
  <section v-if="hasSlides" class="w-full">
    <div class="relative w-full">
      <!-- Track -->
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
          <!-- Aspect control: feels like a hero banner on desktop, not too tall on mobile -->
          <div class="relative w-full aspect-[16/9] sm:aspect-[21/9] md:aspect-[24/9] overflow-hidden">
            <video
              :ref="(el) => setVideoRef(el as HTMLVideoElement | null, idx)"
              :src="b.video_url || ''"
              class="h-full w-full object-cover"
              muted
              playsinline
              loop
              preload="metadata"
            />
            <!-- soft overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/15 to-black/10" />

            <!-- Caption (optional) -->
            <div
              v-if="b.name || b.description"
              class="absolute bottom-0 left-0 right-0 p-4 sm:p-6 md:p-8"
            >
              <div v-if="b.name" class="text-white font-semibold text-base sm:text-lg md:text-xl drop-shadow">
                {{ b.name }}
              </div>
              <div
                v-if="b.description"
                class="mt-1 text-white/85 text-sm sm:text-base md:text-lg line-clamp-2 drop-shadow"
              >
                {{ b.description }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Arrows -->
      <button
        v-if="slides.length > 1"
        type="button"
        aria-label="Previous slide"
        class="absolute left-3 top-1/2 -translate-y-1/2 z-10 rounded-full bg-black/40 hover:bg-black/55 backdrop-blur px-3 py-3 text-white"
        @click="prev"
      >
        <span class="text-lg leading-none">‹</span>
      </button>

      <button
        v-if="slides.length > 1"
        type="button"
        aria-label="Next slide"
        class="absolute right-3 top-1/2 -translate-y-1/2 z-10 rounded-full bg-black/40 hover:bg-black/55 backdrop-blur px-3 py-3 text-white"
        @click="next"
      >
        <span class="text-lg leading-none">›</span>
      </button>

      <!-- Dots -->
      <div
        v-if="slides.length > 1"
        class="absolute bottom-3 left-0 right-0 z-10 flex items-center justify-center gap-2"
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
</style>
