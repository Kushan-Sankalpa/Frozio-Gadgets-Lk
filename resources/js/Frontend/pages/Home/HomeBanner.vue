<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

type Banner = {
  id: number | string
  name?: string
  description?: string | null
  desktop_image_url?: string | null
  mobile_image_url?: string | null
  video_url?: string | null
}

const props = defineProps<{
  banners: Banner[]
}>()

const active = ref(0)

let autoplayTimer: number | null = null
let resumeTimer: number | null = null

const slides = computed(() =>
  (props.banners ?? [])
    .map((banner) => ({
      ...banner,
      image_url: banner.desktop_image_url || banner.mobile_image_url || null,
    }))
    .filter((banner) => !!banner.image_url || !!banner.video_url)
)

const hasSlides = computed(() => slides.value.length > 0)

function goTo(index: number) {
  if (!slides.value.length) return
  active.value = (index + slides.value.length) % slides.value.length
}

function next() {
  goTo(active.value + 1)
}

function prev() {
  goTo(active.value - 1)
}

function stopAutoplay() {
  if (autoplayTimer !== null) {
    window.clearInterval(autoplayTimer)
    autoplayTimer = null
  }
}

function stopResumeTimer() {
  if (resumeTimer !== null) {
    window.clearTimeout(resumeTimer)
    resumeTimer = null
  }
}

function startAutoplay() {
  stopAutoplay()

  if (slides.value.length <= 1) return

  autoplayTimer = window.setInterval(() => {
    next()
  }, 5000)
}

function markInteraction() {
  stopAutoplay()
  stopResumeTimer()

  resumeTimer = window.setTimeout(() => {
    startAutoplay()
  }, 7000)
}

onMounted(() => {
  startAutoplay()
})

onBeforeUnmount(() => {
  stopAutoplay()
  stopResumeTimer()
})

watch(
  () => slides.value.length,
  () => {
    if (active.value >= slides.value.length) {
      active.value = 0
    }

    startAutoplay()
  },
)
</script>

<template>
  <section v-if="hasSlides" class="w-full">
    <div
      class="relative overflow-hidden"
      @pointerdown="markInteraction"
      @touchstart.passive="markInteraction"
      @wheel.passive="markInteraction"
    >
      <div
        class="flex transition-transform duration-700 ease-in-out"
        :style="{ transform: `translateX(-${active * 100}%)` }"
      >
        <div
          v-for="b in slides"
          :key="b.id"
          class="min-w-full shrink-0"
        >
          <div
            class="relative h-[clamp(360px,115vw,520px)] w-full overflow-hidden md:h-[clamp(560px,60vw,620px)]"
          >
            <img
              v-if="b.image_url"
              :src="b.image_url"
              :alt="b.name || 'Home banner'"
              class="block h-full w-full object-cover object-center"
              loading="eager"
              decoding="async"
            />

            <video
              v-else-if="b.video_url"
              :src="b.video_url"
              class="block h-full w-full object-cover object-center"
              autoplay
              muted
              playsinline
              loop
            />

            <div class="absolute inset-0 bg-gradient-to-t via-black/10 to-black/5" />

            <div
              v-if="b.name || b.description"
              class="absolute inset-x-0 bottom-0 p-4 sm:p-6 md:p-8"
            >
              <div class="mx-auto max-w-7xl">
                <h2
                  v-if="b.name"
                  class="text-xl font-semibold text-white sm:text-2xl md:text-3xl"
                >
                  {{ b.name }}
                </h2>

                <p
                  v-if="b.description"
                  class="mt-2 max-w-2xl text-sm leading-6 text-white/90 sm:text-base"
                >
                  {{ b.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <button
        v-if="slides.length > 1"
        type="button"
        aria-label="Previous slide"
        class="absolute left-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/40 px-3 py-3 text-white backdrop-blur transition hover:bg-black/55"
        @click="prev"
      >
        <span class="text-lg leading-none">‹</span>
      </button>

      <button
        v-if="slides.length > 1"
        type="button"
        aria-label="Next slide"
        class="absolute right-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-black/40 px-3 py-3 text-white backdrop-blur transition hover:bg-black/55"
        @click="next"
      >
        <span class="text-lg leading-none">›</span>
      </button>

      <div
        v-if="slides.length > 1"
        class="absolute bottom-4 left-0 right-0 z-20 flex items-center justify-center gap-2"
      >
        <button
          v-for="(_, i) in slides"
          :key="i"
          type="button"
          :aria-label="`Go to slide ${i + 1}`"
          class="h-2.5 w-2.5 rounded-full transition"
          :class="i === active ? 'bg-white' : 'bg-white/40 hover:bg-white/60'"
          @click="goTo(i)"
        />
      </div>
    </div>
  </section>
</template>