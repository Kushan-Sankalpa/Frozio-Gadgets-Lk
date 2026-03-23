<script setup lang="ts">
import { Autoplay } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'

import 'swiper/css'

type CardItem = {
  id: number
  title: string
  icon: string
}

const cards: CardItem[] = [
  { id: 1, title: 'Fast Delivery', icon: '🚚' },
  { id: 2, title: 'Premium Quality', icon: '✨' },
  { id: 3, title: 'Best Prices', icon: '💰' },
  { id: 4, title: 'Secure Payment', icon: '🔒' },
  { id: 5, title: 'Latest Collection', icon: '🔥' },
//   { id: 6, title: 'Top Rated Products', icon: '⭐' },
  { id: 7, title: '24/7 Support', icon: '🎧' },
  { id: 8, title: 'Easy Returns', icon: '↩️' },
]

const loopCards = [...cards, ...cards, ...cards]
</script>

<template>
  <section class="card-swiper-section">
    <div class="card-swiper-container">
      <Swiper
        :modules="[Autoplay]"
        :slides-per-view="'auto'"
        :space-between="16"
        :loop="true"
        :allow-touch-move="true"
        :grab-cursor="true"
        :speed="5000"
        :autoplay="{
          delay: 0,
          disableOnInteraction: false,
          pauseOnMouseEnter: false,
        }"
        class="card-swiper"
      >
        <SwiperSlide
          v-for="(card, index) in loopCards"
          :key="`${card.id}-${index}`"
          class="card-slide"
        >
          <div class="info-card">
            <div class="card-icon">{{ card.icon }}</div>
            <p class="card-text">{{ card.title }}</p>
          </div>
        </SwiperSlide>
      </Swiper>
    </div>
  </section>
</template>

<style scoped>
.card-swiper-section {
  width: 100%;
  padding: 28px 0;
  background: transparent;
  overflow: hidden;
}

.card-swiper-container {
  width: 100%;
  overflow: hidden;
}

:deep(.card-swiper .swiper-wrapper) {
  transition-timing-function: linear !important;
}

.card-slide {
  width: 170px !important;
}

.info-card {
  min-height: 120px;
  background: #ffffff;
  border-radius: 24px;
  padding: 22px 18px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  /* box-shadow:
    0 10px 30px rgba(0, 0, 0, 0.06),
    0 2px 10px rgba(0, 0, 0, 0.03); */
  border: 1px solid rgba(0, 0, 0, 0.05);
  text-align: center;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.info-card:hover {
  transform: translateY(-4px);
  /* box-shadow:
    0 16px 36px rgba(0, 0, 0, 0.08),
    0 4px 12px rgba(0, 0, 0, 0.04); */
}

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #f8f8f8;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  flex-shrink: 0;
}

.card-text {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  line-height: 1.4;
  color: #111827;
}

@media (max-width: 1024px) {
  .card-slide {
    width: 155px !important;
  }

  .info-card {
    min-height: 112px;
    padding: 20px 16px;
    border-radius: 22px;
  }

  .card-icon {
    width: 52px;
    height: 52px;
    font-size: 25px;
  }

  .card-text {
    font-size: 14px;
  }
}

@media (max-width: 640px) {
  .card-swiper-section {
    padding: 20px 0;
  }

  .card-slide {
    width: 138px !important;
  }

  .info-card {
    min-height: 102px;
    padding: 16px 14px;
    border-radius: 20px;
    gap: 10px;
  }

  .card-icon {
    width: 46px;
    height: 46px;
    font-size: 22px;
  }

  .card-text {
    font-size: 13px;
  }
}
</style>