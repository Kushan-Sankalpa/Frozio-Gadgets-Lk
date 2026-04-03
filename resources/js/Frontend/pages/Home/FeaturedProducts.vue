<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

type ProductColor = {
  id: number | string
  name: string
  color_code?: string | null
  image_url?: string | null
}

type ProductItem = {
  id: number | string
  name: string
  category_name?: string | null
  brand_name?: string | null
  thumbnail_url: string | null
  hover_image_url: string | null
  regular_price: number | null
  display_price: number | null
  has_discount: boolean
  discount_label?: string | null
  is_sold_out: boolean
  reviews_count?: number
  reviews_avg_rating?: number | null
  colors?: ProductColor[]
}

const props = withDefaults(
  defineProps<{
    endpoint: string
    title?: string
    subtitle?: string
    activeCategory?: string | null
    activeBrand?: string | null
    search?: string | null
  }>(),
  {
    title: 'Featured Products',
    subtitle: 'Top hand-picked products loaded only when this section comes into view.',
    activeCategory: null,
    activeBrand: null,
    search: null,
  },
)

const sectionRef = ref<HTMLElement | null>(null)
const scrollerRef = ref<HTMLElement | null>(null)

const products = ref<ProductItem[]>([])
const isLoading = ref(false)
const hasLoaded = ref(false)
const fetchError = ref('')

const canScrollLeft = ref(false)
const canScrollRight = ref(false)
const isHovered = ref(false)

const skeletons = [1, 2, 3, 4, 5]

let observer: IntersectionObserver | null = null
let autoSlideTimer: number | null = null

const hasProducts = computed(() => products.value.length > 0)

const repeatedProducts = computed(() => {
  if (products.value.length <= 1) return products.value
  return [...products.value, ...products.value]
})

const endpointUrl = computed(() => {
  const url = new URL(props.endpoint, window.location.origin)

  if (props.activeCategory) url.searchParams.set('category', props.activeCategory)
  if (props.activeBrand) url.searchParams.set('brand', props.activeBrand)
  if (props.search) url.searchParams.set('search', props.search)

  return url.toString()
})

function formatPrice(value: number | null) {
  if (value === null || Number.isNaN(Number(value))) return ''
  return `Rs. ${Number(value).toLocaleString('en-LK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function normalizedDiscount(label?: string | null) {
  if (!label) return null

  return label
    .replace(/^sale\s*/i, '')
    .replace(/^rs\s*/i, 'Rs. ')
    .replace(/^lkr\s*/i, 'Rs. ')
    .trim()
}

function ratingStars(rating?: number | null) {
  const safe = Math.max(0, Math.min(5, Math.round(Number(rating || 0))))
  return '★'.repeat(safe) + '☆'.repeat(5 - safe)
}

function getStepSize() {
  const el = scrollerRef.value
  if (!el) return 260

  const firstCard = el.querySelector('.featured-card') as HTMLElement | null
  const gap = 16

  return firstCard ? firstCard.offsetWidth + gap : 260
}

function updateScrollState() {
  const el = scrollerRef.value
  if (!el) {
    canScrollLeft.value = false
    canScrollRight.value = false
    return
  }

  canScrollLeft.value = el.scrollLeft > 4
  canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 4
}

function scrollCards(direction: 'left' | 'right', smooth = true) {
  const el = scrollerRef.value
  if (!el) return

  const step = getStepSize()
  const half = products.value.length > 1 ? el.scrollWidth / 2 : el.scrollWidth

  if (direction === 'right') {
    if (products.value.length > 1 && el.scrollLeft + step >= half) {
      el.scrollLeft = 0
    }

    el.scrollBy({
      left: step,
      behavior: smooth ? 'smooth' : 'auto',
    })
  } else {
    if (products.value.length > 1 && el.scrollLeft <= 0) {
      el.scrollLeft = half
    }

    el.scrollBy({
      left: -step,
      behavior: smooth ? 'smooth' : 'auto',
    })
  }

  window.setTimeout(updateScrollState, smooth ? 350 : 0)
}

function stopAutoSlide() {
  if (autoSlideTimer !== null) {
    window.clearInterval(autoSlideTimer)
    autoSlideTimer = null
  }
}

function startAutoSlide() {
  stopAutoSlide()

  if (typeof window === 'undefined') return
  if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return
  if (products.value.length <= 1) return

  autoSlideTimer = window.setInterval(() => {
    if (document.hidden || isHovered.value) return
    scrollCards('right')
  }, 2800)
}

async function loadFeaturedProducts() {
  if (isLoading.value || hasLoaded.value) return

  isLoading.value = true
  fetchError.value = ''

  try {
    const response = await fetch(endpointUrl.value, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })

    if (!response.ok) {
      throw new Error('Unable to load featured products.')
    }

    const payload = await response.json()
    const normalized = Array.isArray(payload.products) ? payload.products : []

    products.value = normalized.slice(0, 5)
    hasLoaded.value = true

    await nextTick()
    updateScrollState()
    startAutoSlide()
  } catch (error) {
    fetchError.value =
      error instanceof Error ? error.message : 'Something went wrong while loading featured products.'
  } finally {
    isLoading.value = false
  }
}

function initObserver() {
  if (!sectionRef.value) return

  if (!('IntersectionObserver' in window)) {
    loadFeaturedProducts()
    return
  }

  observer = new IntersectionObserver(
    (entries) => {
      const entry = entries[0]
      if (!entry?.isIntersecting) return

      loadFeaturedProducts()
      observer?.disconnect()
      observer = null
    },
    {
      root: null,
      threshold: 0.12,
      rootMargin: '180px 0px',
    },
  )

  observer.observe(sectionRef.value)
}

function handleResize() {
  updateScrollState()
}

function handleVisibilityChange() {
  if (document.hidden) {
    stopAutoSlide()
    return
  }

  if (hasLoaded.value && hasProducts.value) {
    startAutoSlide()
  }
}

onMounted(() => {
  initObserver()
  window.addEventListener('resize', handleResize)
  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onBeforeUnmount(() => {
  observer?.disconnect()
  observer = null
  stopAutoSlide()
  window.removeEventListener('resize', handleResize)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})
</script>

<template>
  <section ref="sectionRef" class="featured-products-section">
    <div class="featured-products-container">
      <div class="featured-shell">
        <div class="featured-banner">
          <div class="featured-banner__content">
            <span class="featured-banner__eyebrow">LIMITED PICKS</span>
            <h2 class="featured-banner__title">{{ title }}</h2>
            <p class="featured-banner__text">
              {{ subtitle }}
            </p>

            <button
              type="button"
              class="featured-banner__button"
              @click="scrollCards('right')"
            >
              Explore Now
            </button>
          </div>

          <div class="featured-banner__glow featured-banner__glow--one" />
          <div class="featured-banner__glow featured-banner__glow--two" />
        </div>

        <div
          class="featured-products-panel"
          @mouseenter="isHovered = true"
          @mouseleave="isHovered = false"
        >
          <div class="featured-products-panel__header">
            <div>
              <h3 class="featured-products-panel__title">Featured Collection</h3>
              <p class="featured-products-panel__subtitle">
                5 featured products, auto-swiping step by step.
              </p>
            </div>

            <div class="featured-products-panel__nav">
              <button
                type="button"
                class="featured-nav-btn"
                :disabled="!canScrollLeft"
                @click="scrollCards('left')"
              >
                ‹
              </button>
              <button
                type="button"
                class="featured-nav-btn"
                :disabled="!canScrollRight && products.length <= 1"
                @click="scrollCards('right')"
              >
                ›
              </button>
            </div>
          </div>

          <div
            ref="scrollerRef"
            class="featured-products-track"
            @scroll="updateScrollState"
          >
            <template v-if="isLoading">
              <article
                v-for="item in skeletons"
                :key="item"
                class="featured-card featured-card--skeleton"
              >
                <div class="featured-card__media skeleton" />
                <div class="featured-card__body">
                  <div class="skeleton skeleton--sm" />
                  <div class="skeleton skeleton--lg" />
                  <div class="skeleton skeleton--md" />
                  <div class="skeleton skeleton--sm" />
                </div>
              </article>
            </template>

            <template v-else-if="hasProducts">
              <article
                v-for="(product, index) in repeatedProducts"
                :key="`${product.id}-${index}`"
                class="featured-card"
              >
                <div class="featured-card__media">
                  <span
                    v-if="product.has_discount && normalizedDiscount(product.discount_label)"
                    class="featured-card__badge"
                  >
                    {{ normalizedDiscount(product.discount_label) }}
                  </span>

                  <span
                    v-if="product.is_sold_out"
                    class="featured-card__stock"
                  >
                    Sold out
                  </span>

                  <div class="featured-card__image-wrap">
                    <img
                      v-if="product.thumbnail_url"
                      :src="product.thumbnail_url"
                      :alt="product.name"
                      class="featured-card__image featured-card__image--main"
                      loading="lazy"
                    />

                    <img
                      v-if="product.hover_image_url"
                      :src="product.hover_image_url"
                      :alt="product.name"
                      class="featured-card__image featured-card__image--hover"
                      loading="lazy"
                    />
                  </div>
                </div>

                <div class="featured-card__body">
                  <p class="featured-card__meta">
                    {{ product.brand_name || product.category_name || 'Featured product' }}
                  </p>

                  <h4 class="featured-card__name">
                    {{ product.name }}
                  </h4>

                  <div
                    v-if="product.reviews_count"
                    class="featured-card__rating"
                  >
                    <span class="featured-card__stars">
                      {{ ratingStars(product.reviews_avg_rating) }}
                    </span>
                    <span class="featured-card__reviews">
                      ({{ product.reviews_count }})
                    </span>
                  </div>

                  <div
                    v-if="product.colors?.length"
                    class="featured-card__colors"
                  >
                    <span
                      v-for="color in product.colors.slice(0, 5)"
                      :key="color.id"
                      class="featured-card__color"
                      :title="color.name"
                      :style="{ backgroundColor: color.color_code || '#e5e7eb' }"
                    />
                  </div>

                  <div class="featured-card__price">
                    <span class="featured-card__price-current">
                      {{ formatPrice(product.display_price) }}
                    </span>

                    <span
                      v-if="product.has_discount && product.regular_price"
                      class="featured-card__price-old"
                    >
                      {{ formatPrice(product.regular_price) }}
                    </span>
                  </div>
                </div>
              </article>
            </template>

            <div
              v-else-if="fetchError"
              class="featured-products-state"
            >
              {{ fetchError }}
            </div>

            <div
              v-else-if="hasLoaded && !hasProducts"
              class="featured-products-state"
            >
              No featured products available right now.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.featured-products-section {
  width: 100%;
  padding: 30px 0 8px;
}

.featured-products-container {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 16px;
  box-sizing: border-box;
}

.featured-shell {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 18px;
  align-items: stretch;
}

.featured-banner {
  position: relative;
  overflow: hidden;
  border-radius: 26px;
  min-height: 360px;
  padding: 28px 24px;
  background:
    radial-gradient(circle at top right, rgba(255, 213, 79, 0.25), transparent 34%),
    linear-gradient(180deg, #081b68 0%, #06144f 100%);
  color: #fff;
  box-shadow: 0 18px 42px rgba(8, 27, 104, 0.18);
}

.featured-banner__content {
  position: relative;
  z-index: 2;
  display: flex;
  min-height: 100%;
  flex-direction: column;
  justify-content: center;
}

.featured-banner__eyebrow {
  display: inline-flex;
  width: fit-content;
  margin-bottom: 14px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.14);
  padding: 8px 14px;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
}

.featured-banner__title {
  margin: 0;
  font-size: clamp(28px, 4vw, 42px);
  line-height: 1.05;
  font-weight: 900;
}

.featured-banner__text {
  margin: 16px 0 0;
  color: rgba(255, 255, 255, 0.85);
  font-size: 14px;
  line-height: 1.7;
}

.featured-banner__button {
  margin-top: 24px;
  width: fit-content;
  border: none;
  border-radius: 999px;
  padding: 14px 22px;
  background: #f4c400;
  color: #081b68;
  font-weight: 800;
  font-size: 13px;
  letter-spacing: 0.04em;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.featured-banner__button:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 30px rgba(244, 196, 0, 0.22);
}

.featured-banner__glow {
  position: absolute;
  border-radius: 999px;
  filter: blur(10px);
}

.featured-banner__glow--one {
  width: 180px;
  height: 180px;
  right: -34px;
  top: -28px;
  background: rgba(255, 212, 59, 0.18);
}

.featured-banner__glow--two {
  width: 140px;
  height: 140px;
  left: -24px;
  bottom: -36px;
  background: rgba(255, 255, 255, 0.08);
}

.featured-products-panel {
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid #eef2f7;
  border-radius: 26px;
  padding: 20px;
  box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

.featured-products-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.featured-products-panel__title {
  margin: 0;
  color: #111827;
  font-size: 20px;
  font-weight: 800;
}

.featured-products-panel__subtitle {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
}

.featured-products-panel__nav {
  display: flex;
  gap: 10px;
}

.featured-nav-btn {
  width: 42px;
  height: 42px;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  background: #fff;
  color: #0f172a;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s ease;
}

.featured-nav-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  border-color: #081b68;
  color: #081b68;
}

.featured-nav-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.featured-products-track {
  display: flex;
  gap: 16px;
  overflow-x: hidden;
  scroll-behavior: smooth;
  scrollbar-width: none;
  scroll-snap-type: x mandatory;
}

.featured-products-track::-webkit-scrollbar {
  display: none;
}

.featured-card {
  flex: 0 0 calc((100% - 32px) / 3);
  min-width: calc((100% - 32px) / 3);
  background: #fff;
  border: 1px solid #f1f5f9;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  scroll-snap-align: start;
}

.featured-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12);
}

.featured-card__media {
  position: relative;
  padding: 16px;
  background:
    radial-gradient(circle at top, rgba(244, 196, 0, 0.13), transparent 38%),
    linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
}

.featured-card__image-wrap {
  position: relative;
  height: 190px;
  border-radius: 18px;
  background: #fff;
  overflow: hidden;
}

.featured-card__image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  transition: opacity 0.35s ease, transform 0.35s ease;
}

.featured-card__image--main {
  opacity: 1;
}

.featured-card__image--hover {
  position: absolute;
  inset: 0;
  opacity: 0;
}

.featured-card:hover .featured-card__image--main {
  opacity: 0;
  transform: scale(1.04);
}

.featured-card:hover .featured-card__image--hover {
  opacity: 1;
  transform: scale(1.05);
}

.featured-card__badge {
  position: absolute;
  top: 14px;
  left: 14px;
  z-index: 2;
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  background: #e10600;
  color: #fff;
  padding: 7px 11px;
  font-size: 12px;
  font-weight: 800;
  box-shadow: 0 8px 18px rgba(225, 6, 0, 0.22);
}

.featured-card__stock {
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 2;
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.9);
  color: #fff;
  padding: 7px 11px;
  font-size: 11px;
  font-weight: 700;
}

.featured-card__body {
  padding: 16px 16px 18px;
}

.featured-card__meta {
  margin: 0 0 8px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.featured-card__name {
  margin: 0;
  color: #0f172a;
  font-size: 15px;
  line-height: 1.45;
  font-weight: 800;
  min-height: 44px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.featured-card__rating {
  margin-top: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.featured-card__stars {
  color: #f59e0b;
  font-size: 12px;
  letter-spacing: 0.08em;
}

.featured-card__reviews {
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.featured-card__colors {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
}

.featured-card__color {
  width: 15px;
  height: 15px;
  border-radius: 999px;
  border: 2px solid #fff;
  box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.08);
}

.featured-card__price {
  display: flex;
  align-items: baseline;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 14px;
}

.featured-card__price-current {
  color: #4f46e5;
  font-size: 22px;
  font-weight: 900;
  line-height: 1;
}

.featured-card__price-old {
  color: #94a3b8;
  font-size: 14px;
  text-decoration: line-through;
  font-weight: 600;
}

.featured-products-state {
  width: 100%;
  min-height: 220px;
  display: grid;
  place-items: center;
  text-align: center;
  color: #64748b;
  font-size: 15px;
  font-weight: 600;
  padding: 20px;
}

.featured-card--skeleton {
  pointer-events: none;
}

.skeleton {
  background: linear-gradient(90deg, #edf2f7 25%, #f8fafc 50%, #edf2f7 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite linear;
  border-radius: 14px;
}

.featured-card--skeleton .featured-card__media {
  height: 222px;
  background: transparent;
}

.featured-card--skeleton .featured-card__body {
  display: grid;
  gap: 12px;
}

.skeleton--sm {
  height: 14px;
  width: 42%;
}

.skeleton--md {
  height: 18px;
  width: 64%;
}

.skeleton--lg {
  height: 20px;
  width: 90%;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

@media (max-width: 1199px) {
  .featured-shell {
    grid-template-columns: 1fr;
  }

  .featured-banner {
    min-height: 250px;
  }

  .featured-card {
    flex: 0 0 calc((100% - 32px) / 3);
    min-width: calc((100% - 32px) / 3);
  }
}

@media (max-width: 991px) {
  .featured-card {
    flex: 0 0 calc((100% - 16px) / 2);
    min-width: calc((100% - 16px) / 2);
  }
}

@media (max-width: 767px) {
  .featured-products-section {
    padding-top: 24px;
  }

  .featured-products-container {
    padding: 0 14px;
  }

  .featured-products-panel {
    padding: 16px;
    border-radius: 22px;
  }

  .featured-products-panel__header {
    align-items: flex-start;
    flex-direction: column;
  }

  .featured-card {
    flex: 0 0 100%;
    min-width: 100%;
  }

  .featured-card__image-wrap {
    height: 190px;
  }

  .featured-card__price-current {
    font-size: 20px;
  }
}
</style>