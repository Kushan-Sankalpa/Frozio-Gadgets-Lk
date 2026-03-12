<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

type ShoeCategory = {
  id: number | string
  name: string
  image_url: string | null
  status?: string | null
}

const props = defineProps<{
  categories: ShoeCategory[]
}>()

const visibleCategories = computed(() => (props.categories ?? []).slice(0, 3))
</script>

<template>
  <section
    v-if="visibleCategories.length"
    class="mx-auto max-w-7xl px-3 py-8 sm:px-6 sm:py-14 lg:px-8"
  >
    <div class="mb-5 sm:mb-8">
      <h2 class="text-2xl font-semibold tracking-[-0.03em] text-gray-900 sm:text-4xl">
        Shoe Categories
      </h2>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:gap-6">
      <Link
        v-for="(category, index) in visibleCategories"
        :key="category.id"
        :href="route('frontend.root', { shoe_category: category.name })"
        class="shoe-card block"
        :class="{ 'shoe-card--no-image': !category.image_url }"
        :style="{
          '--card-delay': `${index * 120}ms`,
          '--float-duration': `${5.4 + (index % 3) * 0.5}s`,
        }"
      >
        <div
          class="shoe-card__bg"
          :style="category.image_url ? { backgroundImage: `url(${category.image_url})` } : undefined"
        />

        <div class="shoe-card__overlay" />
        <div class="shoe-card__glow" />

        <div class="relative z-10 flex h-full flex-col justify-between p-5 sm:p-6 xl:p-8">
          <div>
            <div
              class="mb-3 inline-flex rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-white/80 backdrop-blur-md sm:text-xs"
            >
              Collection
            </div>

            <h3
              class="max-w-[90%] text-[24px] font-semibold leading-[0.95] tracking-[-0.04em] text-white drop-shadow-[0_12px_28px_rgba(0,0,0,0.38)] sm:text-[30px] xl:text-[40px]"
            >
              {{ category.name }}
            </h3>
          </div>

          <div class="mt-8 flex items-end justify-between gap-3">
            <div
              class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white backdrop-blur-[6px] sm:px-5 sm:py-2.5 sm:text-xs xl:text-sm"
            >
              <span>View All</span>
              <span class="shoe-card__arrow text-sm leading-none xl:text-base">›</span>
            </div>

            <div
              v-if="!category.image_url"
              class="rounded-full border border-white/16 bg-white/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-white/80 backdrop-blur"
            >
              No Image
            </div>
          </div>
        </div>
      </Link>
    </div>
  </section>
</template>

<style scoped>
.shoe-card {
  position: relative;
  overflow: hidden;
  min-height: 280px;
  border-radius: 22px;
  background: #111827;
  isolation: isolate;
  translate: 0 0;
  box-shadow: 0 14px 38px rgba(15, 23, 42, 0.1);
  transition:
    transform 0.7s cubic-bezier(0.22, 1, 0.36, 1),
    box-shadow 0.7s cubic-bezier(0.22, 1, 0.36, 1);
  animation:
    fadeUp 0.8s cubic-bezier(0.22, 1, 0.36, 1) both,
    floatCard var(--float-duration, 5.5s) ease-in-out infinite;
  animation-delay:
    var(--card-delay, 0ms),
    calc(var(--card-delay, 0ms) + 0.85s);
}

.shoe-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 28px 72px rgba(15, 23, 42, 0.18);
}

.shoe-card__bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  opacity: 0.68;
  transform: scale(1);
  filter: saturate(1.08);
  transition:
    transform 0.9s cubic-bezier(0.22, 1, 0.36, 1),
    opacity 0.9s cubic-bezier(0.22, 1, 0.36, 1),
    filter 0.9s cubic-bezier(0.22, 1, 0.36, 1);
}

.shoe-card__overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      180deg,
      rgba(15, 23, 42, 0.14) 0%,
      rgba(15, 23, 42, 0.16) 26%,
      rgba(15, 23, 42, 0.34) 66%,
      rgba(15, 23, 42, 0.62) 100%
    );
}

.shoe-card__glow {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 42%),
    radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.08), transparent 40%);
  pointer-events: none;
}

.shoe-card--no-image .shoe-card__bg {
  background:
    linear-gradient(135deg, rgba(55, 65, 81, 0.96), rgba(17, 24, 39, 1));
  opacity: 1;
  filter: none;
}

.shoe-card__arrow {
  display: inline-block;
  transform: translateX(0);
  transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

.shoe-card:hover .shoe-card__bg {
  transform: scale(1.08);
  opacity: 0.76;
  filter: saturate(1.16);
}

.shoe-card:hover .shoe-card__arrow {
  transform: translateX(4px);
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(22px) scale(0.985);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes floatCard {
  0%,
  100% {
    translate: 0 0;
  }
  50% {
    translate: 0 -14px;
  }
}

@media (min-width: 640px) {
  .shoe-card {
    min-height: 340px;
    border-radius: 28px;
  }
}

@media (min-width: 1280px) {
  .shoe-card {
    min-height: 430px;
    border-radius: 34px;
  }
}

@media (max-width: 420px) {
  .shoe-card__bg {
    opacity: 0.7;
  }

  .shoe-card__overlay {
    background:
      linear-gradient(
        180deg,
        rgba(15, 23, 42, 0.18) 0%,
        rgba(15, 23, 42, 0.18) 28%,
        rgba(15, 23, 42, 0.38) 66%,
        rgba(15, 23, 42, 0.65) 100%
      );
  }
}

@media (prefers-reduced-motion: reduce) {
  .shoe-card,
  .shoe-card__bg,
  .shoe-card__arrow {
    animation: none !important;
    transition: none !important;
    transform: none !important;
    translate: 0 0 !important;
  }
}
</style>