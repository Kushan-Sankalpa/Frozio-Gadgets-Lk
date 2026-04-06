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

const slides = computed(() =>
  (props.banners ?? []).filter(
    (b) => !!b?.desktop_image_url || !!b?.mobile_image_url || !!b?.video_url
  )
)

const hasSlides = computed(() => slides.value.length > 0)
const active = ref(0)

let autoplayTimer: number | null = null
let resumeTimer: number | null = null

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
  if (autoplayTimer) {
    window.clearInterval(autoplayTimer)
    autoplayTimer = null
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

  if (resumeTimer) {
    window.clearTimeout(resumeTimer)
  }

  resumeTimer = window.setTimeout(() => {
    startAutoplay()
  }, 7000)
}

onMounted(() => {
  startAutoplay()
})

onBeforeUnmount(() => {
  stopAutoplay()

  if (resumeTimer) {
    window.clearTimeout(resumeTimer)
    resumeTimer = null
  }
})

watch(
  () => slides.value.length,
  () => {
    active.value = 0
    startAutoplay()
  }
)
</script>

<template>
  <section v-if="hasSlides" class="w-full">
    <div
      class="relative overflow-hidden bg-black"
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
          class="min-w-full"
        >
          <div class="relative h-[430px] w-full overflow-hidden bg-black sm:h-[520px] md:h-[560px] lg:h-[620px]">
            <template v-if="b.desktop_image_url || b.mobile_image_url">
              <picture class="block h-full w-full">
                <source
                  v-if="b.mobile_image_url"
                  :srcset="b.mobile_image_url"
                  media="(max-width: 767px)"
                />
                <img
                  :src="b.desktop_image_url || b.mobile_image_url || ''"
                  :alt="b.name || 'Home banner'"
                  class="h-full w-full object-contain md:object-cover"
                  loading="eager"
                />
              </picture>
            </template>

            <video
              v-else-if="b.video_url"
              :src="b.video_url"
              class="h-full w-full object-cover"
              autoplay
              muted
              playsinline
              loop
            />

            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-black/5" />

            <div
              v-if="b.name || b.description"
              class="absolute inset-x-0 bottom-0 p-4 sm:p-6 md:p-8"
            >
              <div class="mx-auto max-w-7xl">
                <h2
                  v-if="b.name"
                  class="text-xl font-semibold text-white sm:text-2xl md:text-3xl"
                >
                  <!-- {{ b.name }} -->
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