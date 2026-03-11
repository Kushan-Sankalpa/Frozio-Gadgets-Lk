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
  if (index < 2) {
    return 'min-h-[380px] sm:min-h-[460px] xl:row-span-2'
  }

  return 'min-h-[270px] sm:min-h-[300px]'
}

const titleClass = (index: number) => {
  return index < 2 ? 'text-[30px] sm:text-[36px]' : 'text-[24px] sm:text-[28px]'
}
</script>

<template>
  <section v-if="visibleCategories.length" class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
    <div class="mb-6 sm:mb-8">
      <h2 class="text-3xl font-semibold tracking-[-0.03em] text-gray-900 sm:text-4xl">
        Tech Categories
      </h2>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 xl:grid-rows-2">
      <Link
        v-for="(category, index) in visibleCategories"
        :key="category.id"
        :href="route('frontend.root', { category: category.name })"
        class="tech-card block"
        :class="[cardClass(index), { 'tech-card--no-image': !category.image_url }]"
        :style="{
          '--card-delay': `${index * 120}ms`,
          '--float-duration': `${6 + (index % 3) * 0.7}s`,
        }"
      >
        <div
          class="tech-card__bg"
          :style="category.image_url ? { backgroundImage: `url(${category.image_url})` } : undefined"
        />

        <div class="tech-card__overlay" />
        <div class="tech-card__glow" />

        <div class="relative z-10 flex h-full flex-col justify-between p-7 sm:p-8">
          <div>
            <h3
              class="max-w-[82%] font-semibold leading-none tracking-[-0.04em] text-white drop-shadow-[0_12px_28px_rgba(0,0,0,0.38)]"
              :class="titleClass(index)"
            >
              {{ category.name }}
            </h3>

            <p class="mt-4 max-w-[75%] text-sm leading-6 text-white/80 sm:text-[15px]">
              Explore the latest products, accessories, and top picks in this category.
            </p>
          </div>

          <div class="mt-10 flex items-end justify-between gap-4">
            <div
              class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-[12px] font-semibold uppercase tracking-[0.24em] text-white backdrop-blur-[6px]"
            >
              <span>View All</span>
              <span class="tech-card__arrow text-base leading-none">›</span>
            </div>

            <div
              v-if="!category.image_url"
              class="rounded-full border border-white/16 bg-white/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-white/80 backdrop-blur"
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
  border-radius: 32px;
  background: #0f172a;
  isolation: isolate;
  translate: 0 0;
  box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
  transition:
    transform 0.7s cubic-bezier(0.22, 1, 0.36, 1),
    box-shadow 0.7s cubic-bezier(0.22, 1, 0.36, 1);
  animation:
    fadeUp 0.8s cubic-bezier(0.22, 1, 0.36, 1) both,
    floatCard var(--float-duration, 6.4s) ease-in-out infinite;
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
  opacity: 0.58;
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
      rgba(15, 23, 42, 0.22) 0%,
      rgba(15, 23, 42, 0.14) 30%,
      rgba(15, 23, 42, 0.34) 68%,
      rgba(15, 23, 42, 0.48) 100%
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
  opacity: 0.7;
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