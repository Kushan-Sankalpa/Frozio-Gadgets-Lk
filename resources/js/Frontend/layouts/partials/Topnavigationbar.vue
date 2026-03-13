<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
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
const navRef = ref<HTMLElement | null>(null)
const desktopSearchInputRef = ref<HTMLInputElement | null>(null)

const scrolled = ref(false)
const openMobileMenu = ref(false)
const openMobileTech = ref(false)
const openMobileShoe = ref(false)
const openMobileShoeCategoryId = ref<number | string | null>(null)

const activeDropdown = ref<'tech' | 'shoes' | null>(null)
const activeShoeSubMenu = ref<number | string | null>(null)

const searchOpen = ref(false)
const searchQuery = ref('')

let dropdownCloseTimer: ReturnType<typeof setTimeout> | null = null

const techCategories = computed<TechCategory[]>(() => {
  return Array.isArray(page.props.categories) ? (page.props.categories as TechCategory[]) : []
})

const shoeCategories = computed<ShoeCategory[]>(() => {
  return Array.isArray(page.props.shoeCategories)
    ? (page.props.shoeCategories as ShoeCategory[])
    : []
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

const isHomeActive = computed(() => {
  return !currentCategory.value && !currentShoeCategory.value && !currentShoeSubcategory.value
})

const isTechMenuActive = computed(() => !!currentCategory.value)
const isShoeMenuActive = computed(() => !!currentShoeCategory.value || !!currentShoeSubcategory.value)

watch(
  () => page.url,
  () => {
    openMobileMenu.value = false
    openMobileTech.value = false
    openMobileShoe.value = false
    openMobileShoeCategoryId.value = null
    activeDropdown.value = null
    activeShoeSubMenu.value = null
    searchOpen.value = false
    searchQuery.value = currentSearch.value
  },
  { immediate: true }
)

watch(openMobileMenu, (value) => {
  document.body.style.overflow = value ? 'hidden' : ''
})

watch(searchOpen, async (value) => {
  if (value) {
    await nextTick()
    desktopSearchInputRef.value?.focus()
  }
})

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

function clearDropdownTimer() {
  if (dropdownCloseTimer) {
    clearTimeout(dropdownCloseTimer)
    dropdownCloseTimer = null
  }
}

function handleDropdownEnter(name: 'tech' | 'shoes') {
  clearDropdownTimer()
  activeDropdown.value = name
}

function handleDropdownLeave() {
  clearDropdownTimer()
  dropdownCloseTimer = setTimeout(() => {
    activeDropdown.value = null
    activeShoeSubMenu.value = null
  }, 140)
}

function openShoeSubMenu(id: number | string | null) {
  clearDropdownTimer()
  activeShoeSubMenu.value = id
}

function openSearchPanel() {
  searchOpen.value = true
}

function closeSearchPanel() {
  searchOpen.value = false
}

function toggleSearchPanel() {
  searchOpen.value = !searchOpen.value
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
  closeSearchPanel()
}

function toggleMobileShoeCategory(id: number | string) {
  openMobileShoeCategoryId.value = openMobileShoeCategoryId.value === id ? null : id
}

function handleScroll() {
  scrolled.value = window.scrollY > 28
}

function handleResize() {
  if (window.innerWidth >= 1280) {
    openMobileMenu.value = false
    openMobileTech.value = false
    openMobileShoe.value = false
    openMobileShoeCategoryId.value = null
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    activeDropdown.value = null
    activeShoeSubMenu.value = null
    openMobileMenu.value = false
    closeSearchPanel()
  }
}

function handleClickOutside(event: MouseEvent) {
  const target = event.target as Node | null
  if (!target || !navRef.value) return

  if (!navRef.value.contains(target)) {
    activeDropdown.value = null
    activeShoeSubMenu.value = null
    closeSearchPanel()
  }
}

onMounted(() => {
  handleScroll()
  handleResize()

  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('resize', handleResize, { passive: true })
  window.addEventListener('keydown', handleKeydown)
  document.addEventListener('mousedown', handleClickOutside)
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('mousedown', handleClickOutside)
  document.body.style.overflow = ''
  clearDropdownTimer()
})
</script>

<template>
  <header
    ref="navRef"
    class="sticky top-0 z-50 w-full transition-all duration-500 ease-out"
    :class="
      scrolled
        ? 'border-b border-black/10 bg-white/70 text-black shadow-sm backdrop-blur-2xl'
        : 'border-b border-white/10 bg-black text-white shadow-[0_10px_30px_rgba(0,0,0,0.28)]'
    "
  >
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-[72px] items-center justify-between">
        <Link :href="route('frontend.root')" class="flex shrink-0 items-center gap-3">
          <img src="/assets/images/froziohub-logo.png" alt="Logo" class="h-10 w-auto sm:h-11" />
        </Link>

        <nav class="hidden items-center gap-8 xl:flex">
          <Link
            :href="route('frontend.root')"
            class="nav-link"
            :class="[
              scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85',
              isHomeActive ? 'nav-link--active' : '',
            ]"
          >
            <span>Home</span>
            <span
              class="nav-link-indicator"
              :class="[
                isHomeActive ? 'nav-link-indicator--active' : '',
                scrolled ? 'bg-black' : 'bg-white',
              ]"
            />
          </Link>

          <div
            class="relative"
            @mouseenter="handleDropdownEnter('tech')"
            @mouseleave="handleDropdownLeave"
          >
            <button
              type="button"
              class="nav-link inline-flex items-center gap-1.5"
              :class="[
                scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85',
                isTechMenuActive ? 'nav-link--active' : '',
              ]"
            >
              <span>Tech Products</span>
              <svg
                class="h-3.5 w-3.5 transition-transform duration-300"
                :class="activeDropdown === 'tech' ? 'rotate-180' : ''"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>

              <span
                class="nav-link-indicator"
                :class="[
                  isTechMenuActive ? 'nav-link-indicator--active' : '',
                  scrolled ? 'bg-black' : 'bg-white',
                ]"
              />
            </button>

            <Transition name="dropdown-fade">
              <div
                v-if="activeDropdown === 'tech'"
                class="dropdown-panel left-0 min-w-[280px]"
              >
                <div class="px-2 py-2">
                  <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
                    Tech Categories
                  </div>

                  <Link
                    :href="route('frontend.root', { search: currentSearch || undefined })"
                    class="dropdown-link"
                    :class="!currentCategory ? 'dropdown-link--active' : ''"
                  >
                    All Tech Products
                  </Link>

                  <Link
                    v-for="category in techCategories"
                    :key="category.id"
                    :href="route('frontend.root', {
                      category: category.name,
                      search: currentSearch || undefined,
                    })"
                    class="dropdown-link"
                    :class="isTechActive(category.name) ? 'dropdown-link--active' : ''"
                  >
                    {{ category.name }}
                  </Link>
                </div>
              </div>
            </Transition>
          </div>

          <div
            class="relative"
            @mouseenter="handleDropdownEnter('shoes')"
            @mouseleave="handleDropdownLeave"
          >
            <button
              type="button"
              class="nav-link inline-flex items-center gap-1.5"
              :class="[
                scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85',
                isShoeMenuActive ? 'nav-link--active' : '',
              ]"
            >
              <span>Shoe Products</span>
              <svg
                class="h-3.5 w-3.5 transition-transform duration-300"
                :class="activeDropdown === 'shoes' ? 'rotate-180' : ''"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>

              <span
                class="nav-link-indicator"
                :class="[
                  isShoeMenuActive ? 'nav-link-indicator--active' : '',
                  scrolled ? 'bg-black' : 'bg-white',
                ]"
              />
            </button>

            <Transition name="dropdown-fade">
              <div
                v-if="activeDropdown === 'shoes'"
                class="dropdown-panel left-0 min-w-[320px] overflow-visible"
              >
                <div class="px-2 py-2">
                  <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
                    Shoe Categories
                  </div>

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
                    class="relative"
                    @mouseenter="openShoeSubMenu(category.subcategories?.length ? category.id : null)"
                  >
                    <div class="flex items-center">
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
                        class="pointer-events-none pr-3 text-white/35"
                      >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                          <path
                            fill-rule="evenodd"
                            d="M7.21 14.77a.75.75 0 010-1.06L10.94 10 7.21 6.29a.75.75 0 111.06-1.06l4.25 4.25a.75.75 0 010 1.06l-4.25 4.25a.75.75 0 01-1.06 0z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </span>
                    </div>

                    <Transition name="submenu-fade">
                      <div
                        v-if="category.subcategories?.length && activeShoeSubMenu === category.id && activeDropdown === 'shoes'"
                        class="submenu-panel"
                      >
                        <div class="px-2 py-2">
                          <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
                            {{ category.name }}
                          </div>

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
                            v-for="subcategory in category.subcategories || []"
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
                    </Transition>
                  </div>
                </div>
              </div>
            </Transition>
          </div>

          <Link
            :href="`${route('frontend.root')}#about-us`"
            class="nav-link"
            :class="scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85'"
          >
            <span>About Us</span>
            <span class="nav-link-indicator" :class="scrolled ? 'bg-black' : 'bg-white'" />
          </Link>

          <Link
            :href="`${route('frontend.root')}#contact-us`"
            class="nav-link"
            :class="scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85'"
          >
            <span>Contact Us</span>
            <span class="nav-link-indicator" :class="scrolled ? 'bg-black' : 'bg-white'" />
          </Link>
        </nav>

        <div class="hidden items-center gap-2.5 xl:flex">
          <div
            class="search-shell"
            :class="searchOpen ? 'search-shell--open' : 'search-shell--closed'"
          >
            <form @submit.prevent="submitSearch" class="relative">
              <input
                ref="desktopSearchInputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Search products..."
                class="search-shell-input w-full rounded-full border py-2.5 pl-10 pr-10 text-sm outline-none"
                :class="
                  scrolled
                    ? 'border-black/10 bg-black/5 text-black placeholder:text-black/40 focus:border-black/20'
                    : 'border-white/10 bg-white/8 text-white placeholder:text-white/45 focus:border-white/25'
                "
                @keydown.esc.prevent="closeSearchPanel"
              />

              <span
                class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2"
                :class="scrolled ? 'text-black/45' : 'text-white/45'"
              >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path
                    fill-rule="evenodd"
                    d="M9 3.5a5.5 5.5 0 104.473 8.702l3.662 3.663a.75.75 0 101.06-1.06l-3.663-3.662A5.5 5.5 0 009 3.5zM5 9a4 4 0 118 0 4 4 0 01-8 0z"
                    clip-rule="evenodd"
                  />
                </svg>
              </span>

              <button
                v-if="searchOpen && searchQuery"
                type="button"
                @click="clearSearch"
                class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                :class="scrolled ? 'text-black/45 hover:text-black' : 'text-white/45 hover:text-white'"
                aria-label="Clear search"
              >
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                  <path
                    fill-rule="evenodd"
                    d="M4.72 4.72a.75.75 0 011.06 0L10 8.94l4.22-4.22a.75.75 0 111.06 1.06L11.06 10l4.22 4.22a.75.75 0 01-1.06 1.06L10 11.06l-4.22 4.22a.75.75 0 01-1.06-1.06L8.94 10 4.72 5.78a.75.75 0 010-1.06z"
                    clip-rule="evenodd"
                  />
                </svg>
              </button>
            </form>
          </div>

          <button
            type="button"
            :aria-label="searchOpen ? 'Close search' : 'Open search'"
            :aria-expanded="searchOpen ? 'true' : 'false'"
            class="inline-flex navbar-icon-btn"
            :class="
              scrolled
                ? 'border-black/10 bg-black/5 text-black hover:border-black/20 hover:bg-transparent'
                : 'border-white/10 bg-white/8 text-white hover:border-white/20 hover:bg-transparent'
            "
            @click="searchOpen ? closeSearchPanel() : openSearchPanel()"
          >
            <svg
              v-if="!searchOpen"
              class="h-4.5 w-4.5"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M9 3.5a5.5 5.5 0 104.473 8.702l3.662 3.663a.75.75 0 101.06-1.06l-3.663-3.662A5.5 5.5 0 009 3.5zM5 9a4 4 0 118 0 4 4 0 01-8 0z"
                clip-rule="evenodd"
              />
            </svg>

            <svg
              v-else
              class="h-4.5 w-4.5"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M4.72 4.72a.75.75 0 011.06 0L10 8.94l4.22-4.22a.75.75 0 111.06 1.06L11.06 10l4.22 4.22a.75.75 0 01-1.06 1.06L10 11.06l-4.22 4.22a.75.75 0 01-1.06-1.06L8.94 10 4.72 5.78a.75.75 0 010-1.06z"
                clip-rule="evenodd"
              />
            </svg>
          </button>

          <button
            type="button"
            aria-label="Cart"
            class="inline-flex navbar-icon-btn relative"
            :class="
              scrolled
                ? 'border-black/10 bg-black/5 text-black hover:border-black/20 hover:bg-transparent'
                : 'border-white/10 bg-white/8 text-white hover:border-white/20 hover:bg-transparent'
            "
          >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
              <circle cx="10" cy="20" r="1.5" />
              <circle cx="18" cy="20" r="1.5" />
            </svg>

            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#f04f45] px-1 text-[10px] font-bold text-white">
              0
            </span>
          </button>
        </div>

        <button
          type="button"
          class="inline-flex navbar-icon-btn xl:hidden"
          :class="
            scrolled
              ? 'border-black/10 bg-black/5 text-black hover:border-black/20 hover:bg-transparent'
              : 'border-white/10 bg-white/8 text-white hover:border-white/20 hover:bg-transparent'
          "
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
  </header>

  <Teleport to="body">
    <Transition name="overlay-fade">
      <div
        v-if="openMobileMenu"
        class="fixed inset-0 top-[72px] z-[70] bg-black/60 backdrop-blur-sm xl:hidden"
        @click="openMobileMenu = false"
      />
    </Transition>

    <Transition name="panel-slide">
      <div
        v-if="openMobileMenu"
        class="fixed bottom-0 right-0 top-[72px] z-[80] w-full max-w-sm overflow-y-auto border-l border-white/10 bg-black text-white xl:hidden"
        @click.stop
      >
        <div class="space-y-2 p-5">
          <form @submit.prevent="submitSearch" class="relative mb-4">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search products..."
              class="w-full rounded-2xl border border-white/10 bg-white/8 py-3 pl-11 pr-4 text-sm text-white placeholder:text-white/45 outline-none"
            />
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-white/45">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M9 3.5a5.5 5.5 0 104.473 8.702l3.662 3.663a.75.75 0 101.06-1.06l-3.663-3.662A5.5 5.5 0 009 3.5zM5 9a4 4 0 118 0 4 4 0 01-8 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </span>
          </form>

          <Link
            :href="route('frontend.root')"
            class="mobile-link"
            @click="openMobileMenu = false"
          >
            Home
          </Link>

          <div class="mobile-group">
            <button
              type="button"
              class="mobile-accordion"
              @click="openMobileTech = !openMobileTech"
            >
              <span>Tech Products</span>
              <svg
                class="h-4 w-4 transition-transform duration-300"
                :class="openMobileTech ? 'rotate-180' : ''"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <Transition name="accordion">
              <div v-if="openMobileTech" class="overflow-hidden px-3 pb-3">
                <div class="space-y-1 rounded-2xl border border-white/8 bg-black/20 p-2">
                  <Link
                    :href="route('frontend.root', { search: currentSearch || undefined })"
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

          <div class="mobile-group">
            <button
              type="button"
              class="mobile-accordion"
              @click="openMobileShoe = !openMobileShoe"
            >
              <span>Shoe Products</span>
              <svg
                class="h-4 w-4 transition-transform duration-300"
                :class="openMobileShoe ? 'rotate-180' : ''"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <Transition name="accordion">
              <div v-if="openMobileShoe" class="overflow-hidden px-3 pb-3">
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
                      <svg
                        class="h-4 w-4 transition-transform duration-300"
                        :class="openMobileShoeCategoryId === category.id ? 'rotate-180' : ''"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>

                    <Transition name="accordion">
                      <div
                        v-if="openMobileShoeCategoryId === category.id"
                        class="overflow-hidden px-3 pb-3"
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
            class="mt-3 inline-flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3.5 text-sm font-semibold text-white transition hover:bg-transparent"
          >
            <span class="inline-flex items-center gap-3">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
                <circle cx="10" cy="20" r="1.5" />
                <circle cx="18" cy="20" r="1.5" />
              </svg>
              Cart
            </span>

            <span class="inline-flex h-6 min-w-[24px] items-center justify-center rounded-full bg-[#f04f45] px-1 text-[11px] font-bold text-white">
              0
            </span>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.nav-link {
  position: relative;
  display: inline-flex;
  align-items: center;
  height: 48px;
  padding: 0;
  font-size: 0.96rem;
  font-weight: 600;
  transition:
    color 260ms ease,
    opacity 260ms ease,
    transform 260ms ease;
}

.nav-link:hover {
  transform: translateY(-1px);
}

.nav-link-indicator {
  position: absolute;
  left: 0;
  bottom: 9px;
  height: 2px;
  width: 100%;
  transform: scaleX(0);
  transform-origin: center;
  border-radius: 9999px;
  opacity: 0.9;
  transition:
    transform 240ms ease,
    opacity 240ms ease;
}

.nav-link:hover .nav-link-indicator,
.nav-link--active .nav-link-indicator,
.nav-link-indicator--active {
  transform: scaleX(1);
}

.dropdown-panel {
  position: absolute;
  top: calc(100% + 14px);
  z-index: 60;
  border-radius: 26px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(9, 9, 11, 0.96);
  box-shadow: 0 28px 70px rgba(0, 0, 0, 0.34);
  backdrop-filter: blur(18px);
}

.submenu-panel {
  position: absolute;
  left: calc(100% + 14px);
  top: 0;
  z-index: 70;
  min-width: 240px;
  border-radius: 26px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(9, 9, 11, 0.96);
  box-shadow: 0 28px 70px rgba(0, 0, 0, 0.34);
  backdrop-filter: blur(18px);
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

.navbar-icon-btn {
  height: 44px;
  width: 44px;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  border-width: 1px;
  transition:
    background-color 240ms ease,
    border-color 240ms ease,
    color 240ms ease,
    transform 240ms ease;
}

.navbar-icon-btn:hover {
  transform: translateY(-1px);
}

.search-shell {
  overflow: hidden;
  transition:
    width 320ms ease,
    opacity 220ms ease,
    margin-right 320ms ease;
}

.search-shell--open {
  width: 240px;
  opacity: 1;
  margin-right: 2px;
}

.search-shell--closed {
  width: 0;
  opacity: 0;
  margin-right: 0;
}

.search-shell-input {
  transition:
    opacity 220ms ease,
    transform 320ms ease,
    background-color 240ms ease,
    border-color 240ms ease,
    color 240ms ease;
}

.search-shell--open .search-shell-input {
  opacity: 1;
  transform: translateX(0);
  pointer-events: auto;
}

.search-shell--closed .search-shell-input {
  opacity: 0;
  transform: translateX(14px);
  pointer-events: none;
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

.mobile-group {
  overflow: hidden;
  border-radius: 18px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.04);
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

.dropdown-fade-enter-active,
.dropdown-fade-leave-active,
.submenu-fade-enter-active,
.submenu-fade-leave-active,
.overlay-fade-enter-active,
.overlay-fade-leave-active,
.panel-slide-enter-active,
.panel-slide-leave-active,
.accordion-enter-active,
.accordion-leave-active {
  transition: all 240ms ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.submenu-fade-enter-from,
.submenu-fade-leave-to {
  opacity: 0;
  transform: translateX(-8px);
}

.overlay-fade-enter-from,
.overlay-fade-leave-to {
  opacity: 0;
}

.panel-slide-enter-from,
.panel-slide-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.accordion-enter-from,
.accordion-leave-to {
  opacity: 0;
  transform: translateY(-6px);
  max-height: 0;
}

.accordion-enter-to,
.accordion-leave-from {
  opacity: 1;
  transform: translateY(0);
  max-height: 500px;
}
</style>