<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

type Category = {
  id: number | string
  name: string
  image_url: string | null
  status?: string | null
}

const props = defineProps<{
  categories: Category[]
}>()

const visibleCategories = computed(() => (props.categories ?? []).slice(0, 4))

const cardClass = (index: number) => {
  switch (index) {
    case 0:
      return 'col-start-1 row-start-1 row-span-2 min-h-[280px] sm:min-h-[380px] xl:min-h-[460px]'
    case 1:
      return 'col-start-2 row-start-1 row-span-2 min-h-[280px] sm:min-h-[380px] xl:min-h-[460px]'
    case 2:
      return 'col-start-3 row-start-1 min-h-[132px] sm:min-h-[182px] xl:min-h-[220px]'
    case 3:
      return 'col-start-3 row-start-2 min-h-[132px] sm:min-h-[182px] xl:min-h-[220px]'
    default:
      return ''
  }
}

const titleClass = (index: number) => {
  return index < 2
    ? 'text-[14px] leading-[0.95] sm:text-[24px] xl:text-[36px]'
    : 'text-[11px] leading-[1] sm:text-[16px] xl:text-[28px]'
}

const descriptionClass = (index: number) => {
  return index < 2
    ? 'mt-2 max-w-[96%] text-[9px] leading-3 text-white/80 sm:mt-3 sm:max-w-[82%] sm:text-[12px] sm:leading-5 xl:mt-4 xl:max-w-[75%] xl:text-[15px] xl:leading-6'
    : 'mt-1.5 max-w-[98%] text-[8px] leading-3 text-white/78 sm:mt-2 sm:max-w-[90%] sm:text-[10px] sm:leading-4 xl:mt-3 xl:max-w-[82%] xl:text-[14px] xl:leading-5'
}

const contentClass = (index: number) => {
  return index < 2
    ? 'p-3 sm:p-5 xl:p-8'
    : 'p-2.5 sm:p-4 xl:p-6'
}

const buttonClass = (index: number) => {
  return index < 2
    ? 'px-2 py-1 text-[7px] tracking-[0.14em] sm:px-3 sm:py-1.5 sm:text-[9px] sm:tracking-[0.18em] xl:px-5 xl:py-2.5 xl:text-[12px] xl:tracking-[0.24em]'
    : 'px-1.5 py-1 text-[6px] tracking-[0.12em] sm:px-2.5 sm:py-1.5 sm:text-[8px] sm:tracking-[0.14em] xl:px-4 xl:py-2 xl:text-[11px] xl:tracking-[0.18em]'
}
</script>

<template>
  <section v-if="visibleCategories.length" class="mx-auto max-w-7xl px-3 py-8 sm:px-6 sm:py-14 lg:px-8">
    <div class="mb-5 sm:mb-8">
      <h2 class="text-2xl font-semibold tracking-[-0.03em] text-gray-900 sm:text-4xl">
        Tech Categories
      </h2>
    </div>

    <div class="grid grid-cols-3 grid-rows-2 gap-2.5 sm:gap-4 xl:gap-5">
      <Link
        v-for="(category, index) in visibleCategories"
        :key="category.id"
        :href="route('frontend.root', { category: category.name })"
        class="tech-card block"
        :class="[cardClass(index), { 'tech-card--no-image': !category.image_url }]"
        :style="{
          '--card-delay': `${index * 120}ms`,
          '--float-duration': `${5 + (index % 3) * 0.6}s`,
        }"
      >
        <div
          class="tech-card__bg"
          :style="category.image_url ? { backgroundImage: `url(${category.image_url})` } : undefined"
        />

        <div class="tech-card__overlay" />
        <div class="tech-card__glow" />

        <div class="relative z-10 flex h-full flex-col justify-between" :class="contentClass(index)">
          <div>
            <h3
              class="max-w-[98%] font-semibold tracking-[-0.04em] text-white drop-shadow-[0_12px_28px_rgba(0,0,0,0.38)] sm:max-w-[88%] xl:max-w-[82%]"
              :class="titleClass(index)"
            >
              {{ category.name }}
            </h3>

            <!-- <p :class="descriptionClass(index)">
              Explore the latest products, accessories, and top picks in this category.
            </p> -->
          </div>

          <div class="mt-3 flex items-end justify-between gap-1.5 sm:mt-6 sm:gap-2 xl:mt-10">
            <div
              class="inline-flex max-w-full items-center gap-1 rounded-full border border-white/20 bg-white/10 font-semibold uppercase text-white backdrop-blur-[6px] sm:gap-1.5 xl:gap-2"
              :class="buttonClass(index)"
            >
              <span class="truncate">View All</span>
              <span class="tech-card__arrow text-[10px] leading-none sm:text-xs xl:text-base">›</span>
            </div>

            <div
              v-if="!category.image_url"
              class="rounded-full border border-white/16 bg-white/10 px-1.5 py-1 text-[6px] font-semibold uppercase tracking-[0.1em] text-white/80 backdrop-blur sm:px-2 sm:text-[7px] xl:px-3 xl:py-1.5 xl:text-[10px] xl:tracking-[0.2em]"
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
.tech-card {
  position: relative;
  overflow: hidden;
  border-radius: 18px;
  background: #0f172a;
  isolation: isolate;
  translate: 0 0;
  min-width: 0;
  box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
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

.tech-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 28px 70px rgba(15, 23, 42, 0.18);
}

.tech-card__bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  opacity: 0.64;
  transform: scale(1);
  filter: saturate(1.08);
  transition:
    transform 0.9s cubic-bezier(0.22, 1, 0.36, 1),
    opacity 0.9s cubic-bezier(0.22, 1, 0.36, 1),
    filter 0.9s cubic-bezier(0.22, 1, 0.36, 1);
}

.tech-card__overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      180deg,
      rgba(15, 23, 42, 0.16) 0%,
      rgba(15, 23, 42, 0.12) 28%,
      rgba(15, 23, 42, 0.3) 68%,
      rgba(15, 23, 42, 0.52) 100%
    );
}

.tech-card__glow {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 45%),
    radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.08), transparent 42%);
  pointer-events: none;
}

.tech-card--no-image .tech-card__bg {
  background:
    linear-gradient(135deg, rgba(51, 65, 85, 0.96), rgba(15, 23, 42, 1));
  opacity: 1;
  filter: none;
}

.tech-card__arrow {
  display: inline-block;
  transform: translateX(0);
  transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

.tech-card:hover .tech-card__bg {
  transform: scale(1.08);
  opacity: 0.74;
  filter: saturate(1.14);
}

.tech-card:hover .tech-card__arrow {
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
    translate: 0 -15px;
  }
}

@media (min-width: 640px) {
  .tech-card {
    border-radius: 26px;
  }
}

@media (min-width: 1280px) {
  .tech-card {
    border-radius: 32px;
  }
}

@media (max-width: 420px) {
  .tech-card__bg {
    opacity: 0.68;
  }

  .tech-card__overlay {
    background:
      linear-gradient(
        180deg,
        rgba(15, 23, 42, 0.2) 0%,
        rgba(15, 23, 42, 0.14) 30%,
        rgba(15, 23, 42, 0.35) 68%,
        rgba(15, 23, 42, 0.58) 100%
      );
  }
}

@media (prefers-reduced-motion: reduce) {
  .tech-card,
  .tech-card__bg,
  .tech-card__arrow {
    animation: none !important;
    transition: none !important;
    transform: none !important;
    translate: 0 0 !important;
  }
}
</style>