<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type TechCategory = {
  id: number | string
  name: string
  image_url?: string | null
  status?: string | null
}

type ShoeSubcategory = {
  id: number | string
  name: string
}

type ShoeCategory = {
  id: number | string
  name: string
  image_url?: string | null
  status?: string | null
  subcategories?: ShoeSubcategory[]
}

const page = usePage()

const openMobileMenu = ref(false)
const openMobileTech = ref(false)
const openMobileShoe = ref(false)
const openMobileShoeCategoryId = ref<number | string | null>(null)
const searchQuery = ref('')

const techCategories = computed<TechCategory[]>(() => {
  return Array.isArray(page.props.categories) ? (page.props.categories as TechCategory[]) : []
})

const shoeCategories = computed<ShoeCategory[]>(() => {
  return Array.isArray(page.props.shoeCategories) ? (page.props.shoeCategories as ShoeCategory[]) : []
})

const currentParams = computed(() => {
  const url = page.url || ''
  const query = url.includes('?') ? url.split('?')[1] : ''
  return new URLSearchParams(query)
})

const currentCategory = computed(() => currentParams.value.get('category') || '')
const currentShoeCategory = computed(() => currentParams.value.get('shoe_category') || '')
const currentShoeSubcategory = computed(() => currentParams.value.get('shoe_subcategory') || '')
const currentSearch = computed(() => currentParams.value.get('search') || '')

watch(
  () => page.url,
  () => {
    openMobileMenu.value = false
    openMobileTech.value = false
    openMobileShoe.value = false
    openMobileShoeCategoryId.value = null
    searchQuery.value = currentSearch.value
  },
  { immediate: true }
)

function normalize(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
}

function isTechActive(name?: string | null) {
  return normalize(currentCategory.value) === normalize(name)
}

function isShoeCategoryActive(name?: string | null) {
  return normalize(currentShoeCategory.value) === normalize(name)
}

function isShoeSubcategoryActive(name?: string | null) {
  return normalize(currentShoeSubcategory.value) === normalize(name)
}

function submitSearch() {
  router.get(
    route('frontend.root'),
    {
      category: currentCategory.value || undefined,
      shoe_category: currentShoeCategory.value || undefined,
      shoe_subcategory: currentShoeSubcategory.value || undefined,
      search: searchQuery.value.trim() || undefined,
    },
    {
      preserveScroll: true,
      preserveState: true,
    }
  )
}

function clearSearch() {
  searchQuery.value = ''
  submitSearch()
}

function toggleMobileShoeCategory(id: number | string) {
  openMobileShoeCategoryId.value = openMobileShoeCategoryId.value === id ? null : id
}
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b border-white/10 bg-black text-white shadow-[0_8px_30px_rgba(0,0,0,0.24)] backdrop-blur-xl">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-18 items-center justify-between gap-3 py-3">
        <Link :href="route('frontend.root')" class="flex shrink-0 items-center gap-3">
          <img src="/assets/images/froziohub-logo.png" alt="Logo" class="h-10 w-auto sm:h-11" />
        </Link>

        <nav class="hidden xl:flex items-center gap-2">
          <Link
            :href="route('frontend.root')"
            class="nav-pill"
            :class="!currentCategory && !currentShoeCategory && !currentShoeSubcategory ? 'nav-pill--active' : ''"
          >
            Home
          </Link>

          <div class="relative group">
            <button type="button" class="nav-pill inline-flex items-center gap-2">
              <span>Tech Products</span>
              <svg class="h-4 w-4 transition group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
              </svg>
            </button>

            <div class="dropdown-panel min-w-[280px]">
              <div class="px-3 py-3">
                <div class="mb-3 text-xs font-semibold uppercase tracking-[0.22em] text-white/50">
                  Tech Categories
                </div>

                <div class="space-y-1">
                  <Link
                    :href="route('frontend.root')"
                    class="dropdown-link"
                    :class="!currentCategory ? 'dropdown-link--active' : ''"
                  >
                    All Tech Products
                  </Link>

                  <Link
                    v-for="category in techCategories"
                    :key="category.id"
                    :href="route('frontend.root', { category: category.name, search: currentSearch || undefined })"
                    class="dropdown-link"
                    :class="isTechActive(category.name) ? 'dropdown-link--active' : ''"
                  >
                    {{ category.name }}
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <div class="relative group">
            <button type="button" class="nav-pill inline-flex items-center gap-2">
              <span>Shoe Products</span>
              <svg class="h-4 w-4 transition group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
              </svg>
            </button>

            <div class="dropdown-panel min-w-[320px] overflow-visible">
              <div class="px-3 py-3">
                <div class="mb-3 text-xs font-semibold uppercase tracking-[0.22em] text-white/50">
                  Shoe Categories
                </div>

                <div class="space-y-1">
                  <Link
                    :href="route('frontend.root', { search: currentSearch || undefined })"
                    class="dropdown-link"
                    :class="!currentShoeCategory && !currentShoeSubcategory ? 'dropdown-link--active' : ''"
                  >
                    All Shoe Products
                  </Link>

                  <div
                    v-for="category in shoeCategories"
                    :key="category.id"
                    class="relative group/sub"
                  >
                    <div class="flex items-center gap-2">
                      <Link
                        :href="route('frontend.root', {
                          shoe_category: category.name,
                          search: currentSearch || undefined,
                        })"
                        class="dropdown-link flex-1"
                        :class="isShoeCategoryActive(category.name) ? 'dropdown-link--active' : ''"
                      >
                        {{ category.name }}
                      </Link>

                      <span
                        v-if="category.subcategories?.length"
                        class="pointer-events-none pr-2 text-white/40 transition group-hover/sub:text-white"
                      >
                        ›
                      </span>
                    </div>

                    <div
                      v-if="category.subcategories?.length"
                      class="submenu-panel"
                    >
                      <div class="px-3 py-3">
                        <div class="mb-3 text-xs font-semibold uppercase tracking-[0.22em] text-white/50">
                          {{ category.name }}
                        </div>

                        <div class="space-y-1">
                          <Link
                            :href="route('frontend.root', {
                              shoe_category: category.name,
                              search: currentSearch || undefined,
                            })"
                            class="dropdown-link"
                            :class="isShoeCategoryActive(category.name) && !currentShoeSubcategory ? 'dropdown-link--active' : ''"
                          >
                            View All
                          </Link>

                          <Link
                            v-for="subcategory in category.subcategories"
                            :key="subcategory.id"
                            :href="route('frontend.root', {
                              shoe_category: category.name,
                              shoe_subcategory: subcategory.name,
                              search: currentSearch || undefined,
                            })"
                            class="dropdown-link"
                            :class="isShoeSubcategoryActive(subcategory.name) ? 'dropdown-link--active' : ''"
                          >
                            {{ subcategory.name }}
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <Link :href="`${route('frontend.root')}#about-us`" class="nav-pill">
            About Us
          </Link>

          <Link :href="`${route('frontend.root')}#contact-us`" class="nav-pill">
            Contact Us
          </Link>
        </nav>

        <div class="hidden xl:flex items-center gap-3">
          <form @submit.prevent="submitSearch" class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search products..."
              class="w-[230px] rounded-full border border-white/12 bg-white/8 py-2.5 pl-11 pr-11 text-sm text-white placeholder:text-white/45 outline-none transition focus:border-white/30 focus:bg-transparent"
            />
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-white/50">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 104.473 8.702l3.662 3.663a.75.75 0 101.06-1.06l-3.663-3.662A5.5 5.5 0 009 3.5zM5 9a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
              </svg>
            </span>

            <button
              v-if="searchQuery"
              type="button"
              @click="clearSearch"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-white/45 transition hover:text-white"
            >
              ✕
            </button>
          </form>

          <button
            type="button"
            class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/12 bg-white/8 text-white transition hover:border-white/25 hover:bg-transparent"
            aria-label="Cart"
          >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
              <circle cx="10" cy="20" r="1.5" />
              <circle cx="18" cy="20" r="1.5" />
            </svg>
            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold text-black">
              0
            </span>
          </button>
        </div>

        <button
          type="button"
          class="xl:hidden inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/12 bg-white/8 text-white transition hover:border-white/25 hover:bg-transparent"
          @click="openMobileMenu = !openMobileMenu"
          :aria-expanded="openMobileMenu ? 'true' : 'false'"
          aria-label="Toggle menu"
        >
          <svg
            v-if="!openMobileMenu"
            class="h-5 w-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.9"
          >
            <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
          </svg>

          <svg
            v-else
            class="h-5 w-5"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.9"
          >
            <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
          </svg>
        </button>
      </div>
    </div>

    <Transition name="mobile-menu">
      <div
        v-if="openMobileMenu"
        class="xl:hidden border-t border-white/10 bg-black/95 backdrop-blur-2xl"
      >
        <div class="mx-auto max-w-7xl px-4 pb-5 pt-4 sm:px-6">
          <form @submit.prevent="submitSearch" class="relative mb-4">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search products..."
              class="w-full rounded-2xl border border-white/12 bg-white/8 py-3 pl-11 pr-11 text-sm text-white placeholder:text-white/45 outline-none transition focus:border-white/30 focus:bg-transparent"
            />
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-white/50">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 104.473 8.702l3.662 3.663a.75.75 0 101.06-1.06l-3.663-3.662A5.5 5.5 0 009 3.5zM5 9a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
              </svg>
            </span>
          </form>

          <div class="flex flex-col gap-2">
            <Link
              :href="route('frontend.root')"
              class="mobile-link"
              @click="openMobileMenu = false"
            >
              Home
            </Link>

            <div class="rounded-2xl border border-white/10 bg-white/[0.04]">
              <button
                type="button"
                class="mobile-accordion"
                @click="openMobileTech = !openMobileTech"
              >
                <span>Tech Products</span>
                <span class="transition" :class="openMobileTech ? 'rotate-180' : ''">⌄</span>
              </button>

              <Transition name="accordion">
                <div v-if="openMobileTech" class="px-3 pb-3">
                  <div class="space-y-1 rounded-2xl border border-white/8 bg-black/20 p-2">
                    <Link
                      :href="route('frontend.root')"
                      class="mobile-sub-link"
                      @click="openMobileMenu = false"
                    >
                      All Tech Products
                    </Link>

                    <Link
                      v-for="category in techCategories"
                      :key="category.id"
                      :href="route('frontend.root', { category: category.name, search: currentSearch || undefined })"
                      class="mobile-sub-link"
                      @click="openMobileMenu = false"
                    >
                      {{ category.name }}
                    </Link>
                  </div>
                </div>
              </Transition>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/[0.04]">
              <button
                type="button"
                class="mobile-accordion"
                @click="openMobileShoe = !openMobileShoe"
              >
                <span>Shoe Products</span>
                <span class="transition" :class="openMobileShoe ? 'rotate-180' : ''">⌄</span>
              </button>

              <Transition name="accordion">
                <div v-if="openMobileShoe" class="px-3 pb-3">
                  <div class="space-y-2 rounded-2xl border border-white/8 bg-black/20 p-2">
                    <Link
                      :href="route('frontend.root', { search: currentSearch || undefined })"
                      class="mobile-sub-link"
                      @click="openMobileMenu = false"
                    >
                      All Shoe Products
                    </Link>

                    <div
                      v-for="category in shoeCategories"
                      :key="category.id"
                      class="rounded-xl border border-white/8 bg-white/[0.03]"
                    >
                      <button
                        type="button"
                        class="flex w-full items-center justify-between px-3 py-3 text-left text-sm font-semibold text-white"
                        @click="toggleMobileShoeCategory(category.id)"
                      >
                        <span>{{ category.name }}</span>
                        <span class="transition" :class="openMobileShoeCategoryId === category.id ? 'rotate-180' : ''">⌄</span>
                      </button>

                      <Transition name="accordion">
                        <div
                          v-if="openMobileShoeCategoryId === category.id"
                          class="px-3 pb-3"
                        >
                          <div class="space-y-1 rounded-xl border border-white/8 bg-black/25 p-2">
                            <Link
                              :href="route('frontend.root', {
                                shoe_category: category.name,
                                search: currentSearch || undefined,
                              })"
                              class="mobile-sub-link"
                              @click="openMobileMenu = false"
                            >
                              View All
                            </Link>

                            <Link
                              v-for="subcategory in category.subcategories || []"
                              :key="subcategory.id"
                              :href="route('frontend.root', {
                                shoe_category: category.name,
                                shoe_subcategory: subcategory.name,
                                search: currentSearch || undefined,
                              })"
                              class="mobile-sub-link"
                              @click="openMobileMenu = false"
                            >
                              {{ subcategory.name }}
                            </Link>
                          </div>
                        </div>
                      </Transition>
                    </div>
                  </div>
                </div>
              </Transition>
            </div>

            <Link
              :href="`${route('frontend.root')}#about-us`"
              class="mobile-link"
              @click="openMobileMenu = false"
            >
              About Us
            </Link>

            <Link
              :href="`${route('frontend.root')}#contact-us`"
              class="mobile-link"
              @click="openMobileMenu = false"
            >
              Contact Us
            </Link>

            <button
              type="button"
              class="mt-2 inline-flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm font-semibold text-white transition hover:bg-transparent"
            >
              <span class="inline-flex items-center gap-3">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
                  <circle cx="10" cy="20" r="1.5" />
                  <circle cx="18" cy="20" r="1.5" />
                </svg>
                Cart
              </span>

              <span class="inline-flex h-6 min-w-[24px] items-center justify-center rounded-full bg-white px-1 text-[11px] font-bold text-black">
                0
              </span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </header>
</template>

<style scoped>
.nav-pill {
  position: relative;
  display: inline-flex;
  align-items: center;
  height: 42px;
  border-radius: 9999px;
  border: 1px solid transparent;
  padding: 0 16px;
  font-size: 0.92rem;
  font-weight: 600;
  color: white;
  background: transparent;
  transition:
    background-color 220ms ease,
    border-color 220ms ease,
    transform 220ms ease,
    opacity 220ms ease;
}

.nav-pill:hover {
  background: transparent;
  border-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-1px);
}

.nav-pill--active {
  background: rgba(255, 255, 255, 0.09);
  border-color: rgba(255, 255, 255, 0.16);
}

.dropdown-panel {
  position: absolute;
  left: 0;
  top: calc(100% + 12px);
  opacity: 0;
  visibility: hidden;
  transform: translateY(10px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(10, 10, 10, 0.96);
  box-shadow: 0 24px 70px rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(18px);
  transition:
    opacity 220ms ease,
    transform 220ms ease,
    visibility 220ms ease;
  z-index: 60;
}

.group:hover > .dropdown-panel {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.submenu-panel {
  position: absolute;
  left: calc(100% + 12px);
  top: 0;
  min-width: 240px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(6px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(10, 10, 10, 0.96);
  box-shadow: 0 24px 70px rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(18px);
  transition:
    opacity 220ms ease,
    transform 220ms ease,
    visibility 220ms ease;
  z-index: 70;
}

.group\/sub:hover > .submenu-panel {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-link {
  display: flex;
  align-items: center;
  min-height: 42px;
  border-radius: 14px;
  padding: 0 12px;
  font-size: 0.92rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.84);
  transition:
    background-color 180ms ease,
    color 180ms ease,
    transform 180ms ease;
}

.dropdown-link:hover {
  background: rgba(255, 255, 255, 0.07);
  color: white;
  transform: translateX(2px);
}

.dropdown-link--active {
  background: rgba(255, 255, 255, 0.08);
  color: white;
}

.mobile-link {
  display: flex;
  align-items: center;
  min-height: 52px;
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.04);
  padding: 0 16px;
  font-size: 0.95rem;
  font-weight: 700;
  color: white;
  transition:
    background-color 200ms ease,
    transform 200ms ease,
    border-color 200ms ease;
}

.mobile-link:hover {
  background: transparent;
  border-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-1px);
}

.mobile-accordion {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  min-height: 52px;
  padding: 0 16px;
  font-size: 0.95rem;
  font-weight: 700;
  color: white;
}

.mobile-sub-link {
  display: block;
  border-radius: 14px;
  padding: 10px 12px;
  font-size: 0.9rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.84);
  transition:
    background-color 180ms ease,
    color 180ms ease,
    transform 180ms ease;
}

.mobile-sub-link:hover {
  background: rgba(255, 255, 255, 0.06);
  color: white;
  transform: translateX(2px);
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition:
    opacity 240ms ease,
    transform 240ms ease;
  transform-origin: top;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.accordion-enter-active,
.accordion-leave-active {
  transition:
    opacity 220ms ease,
    transform 220ms ease;
  transform-origin: top;
}

.accordion-enter-from,
.accordion-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>