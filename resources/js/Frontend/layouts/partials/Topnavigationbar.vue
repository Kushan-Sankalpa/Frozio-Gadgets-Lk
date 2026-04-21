<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import CartDrawer from '@/Frontend/pages/shop/components/CartDrawer.vue'
import { useCart } from '@/Frontend/pages/shop/composables/useCart'

type TechBrand = {
  id: number | string
  name: string
  logo_url?: string | null
  status?: string | null
}

type TechCategory = {
  id: number | string
  name: string
  image_url?: string | null
  status?: string | null
  brands?: TechBrand[]
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

type CosmeticNavCategory = {
  id: number | string
  name: string
  slug?: string | null
}

type CosmeticNavBrand = {
  id: number | string
  name: string
  logo_url?: string | null
  status?: string | null
  categories?: CosmeticNavCategory[]
}

type SearchSuggestion = {
  id: number | string
  name: string
  image_url?: string | null
  type?: 'tech' | 'shoe' | string
  type_label?: string | null
  target_url?: string | null
}

const page = usePage()
const { totalItems, openCart } = useCart()

const navRef = ref<HTMLElement | null>(null)
const mobilePanelRef = ref<HTMLElement | null>(null)
const desktopSearchInputRef = ref<HTMLInputElement | null>(null)
const mobileSearchInputRef = ref<HTMLInputElement | null>(null)

const scrolled = ref(false)
const openMobileMenu = ref(false)
const openMobileTech = ref(false)
const openMobileShoe = ref(false)
const openMobileCosmetics = ref(false)
const openMobileTechCategoryId = ref<number | string | null>(null)
const openMobileShoeCategoryId = ref<number | string | null>(null)
const openMobileCosmeticBrandId = ref<number | string | null>(null)

const activeDropdown = ref<'tech' | 'shoes' | 'cosmetics' | null>(null)
const activeTechSubMenu = ref<number | string | null>(null)
const activeShoeSubMenu = ref<number | string | null>(null)
const activeCosmeticSubMenu = ref<number | string | null>(null)

const searchOpen = ref(false)
const mobileSearchBarOpen = ref(false)
const searchQuery = ref('')

const searchSuggestions = ref<SearchSuggestion[]>([])
const searchLoading = ref(false)
const searchDropdownOpen = ref(false)
const highlightedSuggestionIndex = ref(-1)
const mobileUiSwitching = ref(false)

const MOBILE_MENU_TRANSITION_MS = 280
const MOBILE_SEARCH_TRANSITION_MS = 240

let dropdownCloseTimer: ReturnType<typeof setTimeout> | null = null
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null
let searchAbortController: AbortController | null = null
let skipNextSuggestionFetch = false

const techCategories = computed<TechCategory[]>(() => {
  return Array.isArray(page.props.categories) ? (page.props.categories as TechCategory[]) : []
})

const shoeCategories = computed<ShoeCategory[]>(() => {
  return Array.isArray(page.props.shoeCategories)
    ? (page.props.shoeCategories as ShoeCategory[])
    : []
})

const cosmeticBrands = computed<CosmeticNavBrand[]>(() => {
  return Array.isArray(page.props.cosmeticBrands)
    ? (page.props.cosmeticBrands as CosmeticNavBrand[])
    : []
})

const currentParams = computed(() => {
  const url = page.url || ''
  const query = url.includes('?') ? url.split('?')[1] : ''
  return new URLSearchParams(query)
})

const currentPath = computed(() => {
  const url = page.url || ''
  return url.includes('?') ? url.split('?')[0] : url
})

const isTechListingPage = computed(() => currentPath.value.startsWith('/tech-products'))
const isShoeListingPage = computed(() => currentPath.value.startsWith('/shoe-products'))
const isCosmeticListingPage = computed(() => currentPath.value.startsWith('/cosmetics'))

const currentCategory = computed(() => currentParams.value.get('category') || '')
const currentBrand = computed(() => currentParams.value.get('brand') || '')
const currentShoeCategory = computed(() => currentParams.value.get('shoe_category') || '')
const currentShoeSubcategory = computed(() => currentParams.value.get('shoe_subcategory') || '')
const currentCosmeticCategory = computed(() => currentParams.value.get('cosmetic_category') || '')
const currentCosmeticBrand = computed(() => currentParams.value.get('cosmetic_brand') || '')
const currentSearch = computed(() => currentParams.value.get('search') || '')

const isHomeActive = computed(() => {
  return currentPath.value === '/'
    && !currentCategory.value
    && !currentBrand.value
    && !currentShoeCategory.value
    && !currentShoeSubcategory.value
    && !currentCosmeticCategory.value
    && !currentCosmeticBrand.value
})

const isTechMenuActive = computed(() => {
  return isTechListingPage.value || !!currentCategory.value || !!currentBrand.value
})
const isShoeMenuActive = computed(() => {
  return isShoeListingPage.value || !!currentShoeCategory.value || !!currentShoeSubcategory.value
})
const isCosmeticMenuActive = computed(() => {
  return isCosmeticListingPage.value || !!currentCosmeticBrand.value || !!currentCosmeticCategory.value
})

const isContactUsActive = computed(() => currentPath.value === '/contact-us')
const cartBadgeCount = computed(() => totalItems.value > 99 ? '99+' : String(totalItems.value))

function wait(ms: number) {
  return new Promise(resolve => setTimeout(resolve, ms))
}

function resetMobileAccordionState() {
  openMobileTech.value = false
  openMobileShoe.value = false
  openMobileCosmetics.value = false
  openMobileTechCategoryId.value = null
  openMobileShoeCategoryId.value = null
  openMobileCosmeticBrandId.value = null
}

watch(
  () => page.url,
  () => {
    openMobileMenu.value = false
    mobileSearchBarOpen.value = false
    resetMobileAccordionState()
    activeDropdown.value = null
    activeTechSubMenu.value = null
    activeShoeSubMenu.value = null
    activeCosmeticSubMenu.value = null
    searchOpen.value = false

    cancelSuggestionRequest()
    closeSuggestionDropdown()
    searchSuggestions.value = []

    skipNextSuggestionFetch = true
    searchQuery.value = currentSearch.value
  },
  { immediate: true }
)

watch(openMobileMenu, (value) => {
  document.body.style.overflow = value ? 'hidden' : ''

  if (value) {
    mobileSearchBarOpen.value = false
    closeSuggestionDropdown()
  } else {
    closeSuggestionDropdown()
  }
})

watch(searchOpen, async (value) => {
  if (value) {
    mobileSearchBarOpen.value = false
    await nextTick()
    desktopSearchInputRef.value?.focus()
    openSuggestionDropdownIfNeeded()
  } else if (!mobileSearchBarOpen.value) {
    closeSuggestionDropdown()
  }
})

watch(mobileSearchBarOpen, async (value) => {
  if (value) {
    await nextTick()
    mobileSearchInputRef.value?.focus()
    openSuggestionDropdownIfNeeded()
  } else if (!searchOpen.value) {
    closeSuggestionDropdown()
  }
})

watch(searchQuery, (value) => {
  if (skipNextSuggestionFetch) {
    skipNextSuggestionFetch = false
    return
  }

  scheduleSuggestionFetch(value)
})

function normalize(value: string | null | undefined) {
  return String(value ?? '').trim().toLowerCase()
}

function isTechActive(name?: string | null) {
  return normalize(currentCategory.value) === normalize(name)
}

function isTechBrandActive(name?: string | null) {
  return normalize(currentBrand.value) === normalize(name)
}

function isShoeCategoryActive(name?: string | null) {
  return normalize(currentShoeCategory.value) === normalize(name)
}

function isShoeSubcategoryActive(name?: string | null) {
  return normalize(currentShoeSubcategory.value) === normalize(name)
}

function isCosmeticBrandActive(name?: string | null) {
  return normalize(currentCosmeticBrand.value) === normalize(name)
}

function isCosmeticCategoryActive(name?: string | null) {
  return normalize(currentCosmeticCategory.value) === normalize(name)
}

function clearDropdownTimer() {
  if (dropdownCloseTimer) {
    clearTimeout(dropdownCloseTimer)
    dropdownCloseTimer = null
  }
}

function handleDropdownEnter(name: 'tech' | 'shoes' | 'cosmetics') {
  clearDropdownTimer()
  activeDropdown.value = name
}

function handleDropdownLeave() {
  clearDropdownTimer()
  dropdownCloseTimer = setTimeout(() => {
    activeDropdown.value = null
    activeTechSubMenu.value = null
    activeShoeSubMenu.value = null
    activeCosmeticSubMenu.value = null
  }, 140)
}

function openTechSubMenu(id: number | string | null) {
  clearDropdownTimer()
  activeTechSubMenu.value = id
}

function openShoeSubMenu(id: number | string | null) {
  clearDropdownTimer()
  activeShoeSubMenu.value = id
}

function openCosmeticSubMenu(id: number | string | null) {
  clearDropdownTimer()
  activeCosmeticSubMenu.value = id
}

function openSearchPanel() {
  searchOpen.value = true
}

function closeSearchPanel() {
  searchOpen.value = false

  if (!mobileSearchBarOpen.value) {
    closeSuggestionDropdown()
  }
}

async function toggleMobileMenu() {
  if (mobileUiSwitching.value) return

  if (openMobileMenu.value) {
    openMobileMenu.value = false
    return
  }

  mobileUiSwitching.value = true

  try {
    if (mobileSearchBarOpen.value) {
      mobileSearchBarOpen.value = false
      closeSuggestionDropdown()
      await wait(MOBILE_SEARCH_TRANSITION_MS)
    }

    openMobileMenu.value = true
  } finally {
    await wait(40)
    mobileUiSwitching.value = false
  }
}

async function toggleMobileSearchBar() {
  if (mobileUiSwitching.value) return

  if (mobileSearchBarOpen.value) {
    mobileSearchBarOpen.value = false
    closeSuggestionDropdown()
    return
  }

  mobileUiSwitching.value = true

  try {
    if (openMobileMenu.value) {
      openMobileMenu.value = false
      resetMobileAccordionState()
      closeSuggestionDropdown()
      await wait(MOBILE_MENU_TRANSITION_MS)
    }

    mobileSearchBarOpen.value = true
    await nextTick()
    mobileSearchInputRef.value?.focus()
  } finally {
    await wait(40)
    mobileUiSwitching.value = false
  }
}

function submitSearch() {
  closeSuggestionDropdown()
  cancelSuggestionRequest()

  const goToShoePage =
    isShoeListingPage.value ||
    !!currentShoeCategory.value ||
    !!currentShoeSubcategory.value

  const goToCosmeticPage =
    isCosmeticListingPage.value ||
    !!currentCosmeticBrand.value ||
    !!currentCosmeticCategory.value

  const destination = goToShoePage
    ? 'frontend.shoe-products.index'
    : goToCosmeticPage
      ? 'frontend.cosmetic-products.index'
      : 'frontend.root'

  router.get(
    route(destination),
    {
      category: goToShoePage || goToCosmeticPage ? undefined : currentCategory.value || undefined,
      brand: goToShoePage || goToCosmeticPage ? undefined : currentBrand.value || undefined,
      shoe_category: goToShoePage ? currentShoeCategory.value || undefined : undefined,
      shoe_subcategory: goToShoePage ? currentShoeSubcategory.value || undefined : undefined,
      cosmetic_category: goToCosmeticPage ? currentCosmeticCategory.value || undefined : undefined,
      cosmetic_brand: goToCosmeticPage ? currentCosmeticBrand.value || undefined : undefined,
      search: searchQuery.value.trim() || undefined,
    },
    {
      preserveScroll: true,
      preserveState: true,
    }
  )
}

function clearSearch() {
  skipNextSuggestionFetch = true
  searchQuery.value = ''
  cancelSuggestionRequest()
  searchSuggestions.value = []
  closeSuggestionDropdown()
  submitSearch()
  closeSearchPanel()
  mobileSearchBarOpen.value = false
}

function toggleMobileTechCategory(id: number | string) {
  openMobileTechCategoryId.value = openMobileTechCategoryId.value === id ? null : id
}

function toggleMobileShoeCategory(id: number | string) {
  openMobileShoeCategoryId.value = openMobileShoeCategoryId.value === id ? null : id
}

function toggleMobileCosmeticBrand(id: number | string) {
  openMobileCosmeticBrandId.value = openMobileCosmeticBrandId.value === id ? null : id
}

function handleScroll() {
  scrolled.value = window.scrollY > 28
}

function handleResize() {
  if (window.innerWidth >= 1280) {
    openMobileMenu.value = false
    mobileSearchBarOpen.value = false
    resetMobileAccordionState()
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    activeDropdown.value = null
    activeTechSubMenu.value = null
    activeShoeSubMenu.value = null
    activeCosmeticSubMenu.value = null
    openMobileMenu.value = false
    mobileSearchBarOpen.value = false
    closeSearchPanel()
  }
}

function handleClickOutside(event: MouseEvent) {
  const target = event.target as Node | null
  if (!target) return

  const insideNav = !!navRef.value?.contains(target)
  const insideMobilePanel = !!mobilePanelRef.value?.contains(target)

  if (!insideNav && !insideMobilePanel) {
    activeDropdown.value = null
    activeTechSubMenu.value = null
    activeShoeSubMenu.value = null
    activeCosmeticSubMenu.value = null
    mobileSearchBarOpen.value = false
    closeSearchPanel()
    closeSuggestionDropdown()
  }
}

function cancelSuggestionRequest() {
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer)
    searchDebounceTimer = null
  }

  if (searchAbortController) {
    searchAbortController.abort()
    searchAbortController = null
  }

  searchLoading.value = false
}

function closeSuggestionDropdown() {
  searchDropdownOpen.value = false
  highlightedSuggestionIndex.value = -1
}

function openSuggestionDropdownIfNeeded() {
  if (searchQuery.value.trim() && (searchLoading.value || searchSuggestions.value.length > 0)) {
    searchDropdownOpen.value = true
  }
}

function rankSuggestion(name: string, query: string) {
  const normalizedName = normalize(name)
  const normalizedQuery = normalize(query)

  if (!normalizedQuery) return 999
  if (normalizedName === normalizedQuery) return 0
  if (normalizedName.startsWith(normalizedQuery)) return 1
  if (normalizedName.split(/\s+/).some(word => word.startsWith(normalizedQuery))) return 2
  if (normalizedName.includes(normalizedQuery)) return 3
  return 4
}

function scheduleSuggestionFetch(value: string) {
  cancelSuggestionRequest()

  const query = value.trim()

  if (!query) {
    searchSuggestions.value = []
    closeSuggestionDropdown()
    return
  }

  searchDebounceTimer = setTimeout(() => {
    fetchSearchSuggestions(query)
  }, 180)
}

async function fetchSearchSuggestions(query: string) {
  const cleanQuery = query.trim()

  if (!cleanQuery) {
    searchSuggestions.value = []
    closeSuggestionDropdown()
    return
  }

  searchLoading.value = true
  searchAbortController = new AbortController()

  try {
    const response = await fetch(
      `${route('frontend.products.suggestions')}?q=${encodeURIComponent(cleanQuery)}`,
      {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        signal: searchAbortController.signal,
      }
    )

    if (!response.ok) {
      throw new Error('Failed to load suggestions')
    }

    const payload = await response.json()

    const uniqueMap = new Map<string, SearchSuggestion>()

    if (Array.isArray(payload)) {
      payload.forEach((item: any) => {
        const name = String(item?.name ?? '').trim()
        const targetUrl = String(item?.target_url ?? '').trim()

        if (!name || !targetUrl) return

        const key = `${String(item?.type ?? 'product').toLowerCase()}::${name.toLowerCase()}::${targetUrl}`

        if (!uniqueMap.has(key)) {
          uniqueMap.set(key, {
            id: item?.id ?? key,
            name,
            image_url: item?.image_url ?? null,
            type: item?.type ?? 'product',
            type_label: item?.type_label ?? 'Product',
            target_url: targetUrl,
          })
        }
      })
    }

    const results = Array.from(uniqueMap.values())
      .sort((a, b) => {
        const rankA = rankSuggestion(a.name, cleanQuery)
        const rankB = rankSuggestion(b.name, cleanQuery)

        if (rankA !== rankB) return rankA - rankB
        return a.name.localeCompare(b.name)
      })
      .slice(0, 10)

    searchSuggestions.value = results
    searchDropdownOpen.value = true
    highlightedSuggestionIndex.value = results.length ? 0 : -1
  } catch (error: any) {
    if (error?.name !== 'AbortError') {
      searchSuggestions.value = []
      closeSuggestionDropdown()
    }
  } finally {
    searchLoading.value = false
    searchAbortController = null
  }
}

function goToSuggestion(suggestion: SearchSuggestion) {
  if (!suggestion?.target_url) return

  skipNextSuggestionFetch = true
  searchQuery.value = suggestion.name

  cancelSuggestionRequest()
  searchSuggestions.value = []
  closeSuggestionDropdown()

  activeDropdown.value = null
  activeTechSubMenu.value = null
  activeShoeSubMenu.value = null

  openMobileMenu.value = false
  resetMobileAccordionState()
  searchOpen.value = false
  mobileSearchBarOpen.value = false

  router.visit(suggestion.target_url, {
    preserveScroll: true,
    preserveState: false,
  })
}

function handleSearchInputKeydown(event: KeyboardEvent) {
  if (event.key === 'ArrowDown') {
    if (!searchSuggestions.value.length) return

    event.preventDefault()
    searchDropdownOpen.value = true
    highlightedSuggestionIndex.value = Math.min(
      highlightedSuggestionIndex.value + 1,
      searchSuggestions.value.length - 1
    )
    return
  }

  if (event.key === 'ArrowUp') {
    if (!searchSuggestions.value.length) return

    event.preventDefault()
    highlightedSuggestionIndex.value = Math.max(highlightedSuggestionIndex.value - 1, 0)
    return
  }

  if (event.key === 'Enter') {
    if (
      searchDropdownOpen.value &&
      highlightedSuggestionIndex.value >= 0 &&
      searchSuggestions.value[highlightedSuggestionIndex.value]
    ) {
      event.preventDefault()
      goToSuggestion(searchSuggestions.value[highlightedSuggestionIndex.value])
      return
    }

    return
  }

  if (event.key === 'Escape') {
    closeSuggestionDropdown()

    if (searchOpen.value) {
      closeSearchPanel()
    }

    if (mobileSearchBarOpen.value) {
      mobileSearchBarOpen.value = false
    }
  }
}

function openCartDrawer() {
  if (openMobileMenu.value) {
    openMobileMenu.value = false
  }

  if (mobileSearchBarOpen.value) {
    mobileSearchBarOpen.value = false
  }

  openCart()
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
  cancelSuggestionRequest()
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
      <div class="relative flex h-[82px] items-center justify-between sm:h-[86px] xl:h-[72px]">
        <button
          type="button"
          class="inline-flex navbar-icon-btn xl:hidden"
          :class="
            scrolled
              ? 'border-black/10 bg-black/5 text-black hover:border-black/20 hover:bg-transparent'
              : 'border-white/10 bg-white/8 text-white hover:border-white/20 hover:bg-transparent'
          "
          @click="toggleMobileMenu"
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

        <Link
          :href="route('frontend.root')"
          class="absolute left-1/2 top-1/2 z-[1] flex -translate-x-1/2 -translate-y-1/2 items-center justify-center xl:static xl:left-auto xl:top-auto xl:z-auto xl:translate-x-0 xl:translate-y-0 xl:shrink-0"
        >
          <img
            :src="scrolled ? '/assets/images/froziohubcolored.png' : '/assets/images/froziohub_new.png'"
            alt="FrozioHub"
            class="h-[142px] w-auto max-w-[210px] sm:h-[60px] sm:max-w-[240px] md:h-[66px] md:max-w-[260px] xl:h-[118px] xl:max-w-none"
          />
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
            <div
              class="nav-link inline-flex items-center gap-1.5"
              :class="[
                scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85',
                isTechMenuActive ? 'nav-link--active' : '',
              ]"
            >
              <Link
                :href="route('frontend.tech-products.index')"
                class="inline-flex items-center"
              >
                <span>Mobile Essentials</span>
              </Link>
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
            </div>

            <Transition name="dropdown-fade">
              <div
                v-if="activeDropdown === 'tech'"
                class="dropdown-panel left-0 min-w-[300px] overflow-visible"
              >
                <div class="px-2 py-2">
                  <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
                    Tech Categories
                  </div>

                  <Link
                    :href="route('frontend.tech-products.index', { search: currentSearch || undefined })"
                    class="dropdown-link"
                    :class="!currentCategory && !currentBrand ? 'dropdown-link--active' : ''"
                  >
                    All Mobile Essentials
                  </Link>

                  <div
                    v-for="category in techCategories"
                    :key="category.id"
                    class="relative"
                    @mouseenter="openTechSubMenu(category.brands?.length ? category.id : null)"
                  >
                    <div class="flex items-center">
                      <Link
                        :href="route('frontend.tech-products.index', {
                          category: category.name,
                          search: currentSearch || undefined,
                        })"
                        class="dropdown-link flex-1"
                        :class="isTechActive(category.name) && !currentBrand ? 'dropdown-link--active' : ''"
                      >
                        {{ category.name }}
                      </Link>

                      <span
                        v-if="category.brands?.length"
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
                        v-if="category.brands?.length && activeTechSubMenu === category.id && activeDropdown === 'tech'"
                        class="submenu-panel"
                      >
                        <div class="px-2 py-2">
                          <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
                            {{ category.name }} Brands
                          </div>

                          <Link
                            :href="route('frontend.tech-products.index', {
                              category: category.name,
                              search: currentSearch || undefined,
                            })"
                            class="dropdown-link"
                            :class="isTechActive(category.name) && !currentBrand ? 'dropdown-link--active' : ''"
                          >
                            View All
                          </Link>

                          <Link
                            v-for="brand in category.brands || []"
                            :key="brand.id"
                            :href="route('frontend.tech-products.index', {
                              category: category.name,
                              brand: brand.name,
                              search: currentSearch || undefined,
                            })"
                            class="dropdown-link"
                            :class="isTechActive(category.name) && isTechBrandActive(brand.name) ? 'dropdown-link--active' : ''"
                          >
                            {{ brand.name }}
                          </Link>
                        </div>
                      </div>
                    </Transition>
                  </div>
                </div>
              </div>
            </Transition>
          </div>

          <div
            class="relative"
            @mouseenter="handleDropdownEnter('shoes')"
            @mouseleave="handleDropdownLeave"
          >
            <div
              class="nav-link inline-flex items-center gap-1.5"
              :class="[
                scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85',
                isShoeMenuActive ? 'nav-link--active' : '',
              ]"
            >
              <Link
                :href="route('frontend.shoe-products.index')"
                class="inline-flex items-center"
              >
                <span>Featured Footwear</span>
              </Link>

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
            </div>

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
                    :href="route('frontend.shoe-products.index', { search: currentSearch || undefined })"
                    class="dropdown-link"
                    :class="!currentShoeCategory && !currentShoeSubcategory ? 'dropdown-link--active' : ''"
                  >
                    All Featured Footwear
                  </Link>

                  <div
                    v-for="category in shoeCategories"
                    :key="category.id"
                    class="relative"
                    @mouseenter="openShoeSubMenu(category.subcategories?.length ? category.id : null)"
                  >
                    <div class="flex items-center">
                      <Link
                        :href="route('frontend.shoe-products.index', {
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
                            :href="route('frontend.shoe-products.index', {
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
                            :href="route('frontend.shoe-products.index', {
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

          <div
            class="relative"
            @mouseenter="handleDropdownEnter('cosmetics')"
            @mouseleave="handleDropdownLeave"
          >
            <div
              class="nav-link inline-flex items-center gap-1.5"
              :class="[
                scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85',
                isCosmeticMenuActive ? 'nav-link--active' : '',
              ]"
            >
              <Link
                :href="route('frontend.cosmetic-products.index')"
                class="inline-flex items-center"
              >
                <span>Cosmetics</span>
              </Link>

              <svg
                class="h-3.5 w-3.5 transition-transform duration-300"
                :class="activeDropdown === 'cosmetics' ? 'rotate-180' : ''"
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
                  isCosmeticMenuActive ? 'nav-link-indicator--active' : '',
                  scrolled ? 'bg-black' : 'bg-white',
                ]"
              />
            </div>

            <Transition name="dropdown-fade">
              <div
                v-if="activeDropdown === 'cosmetics'"
                class="dropdown-panel left-0 min-w-[320px] overflow-visible"
              >
                <div class="px-2 py-2">
                  <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
                    Cosmetic Brands
                  </div>

                  <Link
                    :href="route('frontend.cosmetic-products.index', { search: currentSearch || undefined })"
                    class="dropdown-link"
                    :class="!currentCosmeticBrand && !currentCosmeticCategory ? 'dropdown-link--active' : ''"
                  >
                    All Cosmetics
                  </Link>

                  <div
                    v-for="brand in cosmeticBrands"
                    :key="brand.id"
                    class="relative"
                    @mouseenter="openCosmeticSubMenu(brand.categories?.length ? brand.id : null)"
                  >
                    <div class="flex items-center">
                      <Link
                        :href="route('frontend.cosmetic-products.index', {
                          cosmetic_brand: brand.name,
                          search: currentSearch || undefined,
                        })"
                        class="dropdown-link flex-1"
                        :class="isCosmeticBrandActive(brand.name) && !currentCosmeticCategory ? 'dropdown-link--active' : ''"
                      >
                        <span class="flex items-center gap-2">
                          <img
                            v-if="brand.logo_url"
                            :src="brand.logo_url"
                            :alt="brand.name"
                            class="h-5 w-5 rounded-full bg-white/10 object-contain"
                            loading="lazy"
                            decoding="async"
                          />
                          <span>{{ brand.name }}</span>
                        </span>
                      </Link>

                      <span
                        v-if="brand.categories?.length"
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
                        v-if="brand.categories?.length && activeCosmeticSubMenu === brand.id && activeDropdown === 'cosmetics'"
                        class="submenu-panel"
                      >
                        <div class="px-2 py-2">
                          <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/45">
                            {{ brand.name }} Categories
                          </div>

                          <Link
                            :href="route('frontend.cosmetic-products.index', {
                              cosmetic_brand: brand.name,
                              search: currentSearch || undefined,
                            })"
                            class="dropdown-link"
                            :class="isCosmeticBrandActive(brand.name) && !currentCosmeticCategory ? 'dropdown-link--active' : ''"
                          >
                            View All
                          </Link>

                          <Link
                            v-for="category in brand.categories || []"
                            :key="category.id"
                            :href="route('frontend.cosmetic-products.index', {
                              cosmetic_brand: brand.name,
                              cosmetic_category: category.name,
                              search: currentSearch || undefined,
                            })"
                            class="dropdown-link"
                            :class="isCosmeticBrandActive(brand.name) && isCosmeticCategoryActive(category.name) ? 'dropdown-link--active' : ''"
                          >
                            {{ category.name }}
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
            :href="route('frontend.contact-us.index')"
            class="nav-link"
            :class="[
              scrolled ? 'text-black hover:text-black/80' : 'text-white hover:text-white/85',
              isContactUsActive ? 'nav-link--active' : '',
            ]"
          >
            <span>Contact Us</span>
            <span
              class="nav-link-indicator"
              :class="[
                isContactUsActive ? 'nav-link-indicator--active' : '',
                scrolled ? 'bg-black' : 'bg-white',
              ]"
            />
          </Link>
        </nav>

        <div class="hidden items-center gap-2.5 xl:flex">
          <div class="search-shell-wrap" :class="searchOpen ? 'search-shell-wrap--open' : 'search-shell-wrap--closed'">
            <div class="search-shell">
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
                  @focus="openSuggestionDropdownIfNeeded"
                  @keydown="handleSearchInputKeydown"
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

            <Transition name="dropdown-fade">
              <div
                v-if="searchOpen && searchQuery.trim() && (searchDropdownOpen || searchLoading)"
                class="absolute left-0 right-0 top-[calc(100%+10px)] z-[90] overflow-hidden rounded-[24px] border shadow-[0_24px_70px_rgba(0,0,0,0.28)] backdrop-blur-xl"
                :class="scrolled ? 'border-black/10 bg-white text-black' : 'border-white/10 bg-black/95 text-white'"
              >
                <div
                  v-if="searchLoading"
                  class="px-4 py-3 text-sm"
                  :class="scrolled ? 'text-black/60' : 'text-white/60'"
                >
                  Searching products...
                </div>

                <template v-else>
                  <button
                    v-for="(suggestion, index) in searchSuggestions"
                    :key="`${suggestion.id}-${suggestion.name}`"
                    type="button"
                    class="suggestion-item"
                    :class="[
                      index === highlightedSuggestionIndex
                        ? scrolled
                          ? 'bg-black/[0.05]'
                          : 'bg-white/[0.07]'
                        : '',
                    ]"
                    @mouseenter="highlightedSuggestionIndex = index"
                    @mousedown.prevent="goToSuggestion(suggestion)"
                  >
                    <div class="suggestion-thumb">
                      <img
                        v-if="suggestion.image_url"
                        :src="suggestion.image_url"
                        :alt="suggestion.name"
                      />
                      <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-[10px] font-semibold"
                        :class="scrolled ? 'text-black/40' : 'text-white/40'"
                      >
                        No Image
                      </div>
                    </div>

                    <div class="min-w-0 flex-1">
                      <div class="suggestion-name" :class="scrolled ? 'text-black' : 'text-white'">
                        {{ suggestion.name }}
                      </div>
                      <div class="suggestion-type" :class="scrolled ? 'text-black/55' : 'text-white/55'">
                        {{ suggestion.type_label || 'Product' }}
                      </div>
                    </div>
                  </button>

                  <div
                    v-if="!searchSuggestions.length"
                    class="px-4 py-3 text-sm"
                    :class="scrolled ? 'text-black/60' : 'text-white/60'"
                  >
                    No matching products found.
                  </div>
                </template>
              </div>
            </Transition>
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
            @click="openCartDrawer"
          >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
              <circle cx="10" cy="20" r="1.5" />
              <circle cx="18" cy="20" r="1.5" />
            </svg>

            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#f04f45] px-1 text-[10px] font-bold text-white">
              {{ cartBadgeCount }}
            </span>
          </button>
        </div>

        <div class="ml-auto flex items-center gap-2 xl:hidden">
          <button
            type="button"
            :aria-label="mobileSearchBarOpen ? 'Close search' : 'Open search'"
            :aria-expanded="mobileSearchBarOpen ? 'true' : 'false'"
            class="inline-flex navbar-icon-btn"
            :class="
              scrolled
                ? 'border-black/10 bg-black/5 text-black hover:border-black/20 hover:bg-transparent'
                : 'border-white/10 bg-white/8 text-white hover:border-white/20 hover:bg-transparent'
            "
            @click="toggleMobileSearchBar"
          >
            <svg
              v-if="!mobileSearchBarOpen"
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
            @click="openCartDrawer"
          >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1 1 0 00.98.8H18.8a1 1 0 00.97-.76L21 7H7" />
              <circle cx="10" cy="20" r="1.5" />
              <circle cx="18" cy="20" r="1.5" />
            </svg>

            <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#f04f45] px-1 text-[10px] font-bold text-white">
              {{ cartBadgeCount }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <Transition name="mobile-search-slide">
      <div
        v-if="mobileSearchBarOpen"
        class="border-t xl:hidden"
        :class="scrolled ? 'border-black/10 bg-white/80' : 'border-white/10 bg-black/95'"
      >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="relative py-3">
            <form @submit.prevent="submitSearch" class="relative">
              <input
                ref="mobileSearchInputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Search products..."
                class="w-full rounded-2xl border py-3.5 pl-11 pr-10 text-sm outline-none transition-all duration-300"
                :class="
                  scrolled
                    ? 'border-black/10 bg-black/5 text-black placeholder:text-black/40 focus:border-black/20'
                    : 'border-white/10 bg-white/8 text-white placeholder:text-white/45 focus:border-white/20'
                "
                @focus="openSuggestionDropdownIfNeeded"
                @keydown="handleSearchInputKeydown"
              />

              <span
                class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2"
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
                v-if="searchQuery"
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

            <Transition name="dropdown-fade">
              <div
                v-if="mobileSearchBarOpen && searchQuery.trim() && (searchDropdownOpen || searchLoading)"
                class="absolute left-0 right-0 top-[calc(100%+10px)] z-[90] overflow-hidden rounded-[24px] border shadow-[0_24px_70px_rgba(0,0,0,0.28)] backdrop-blur-xl"
                :class="scrolled ? 'border-black/10 bg-white text-black' : 'border-white/10 bg-black/95 text-white'"
              >
                <div
                  v-if="searchLoading"
                  class="px-4 py-3 text-sm"
                  :class="scrolled ? 'text-black/60' : 'text-white/60'"
                >
                  Searching products...
                </div>

                <template v-else>
                  <button
                    v-for="(suggestion, index) in searchSuggestions"
                    :key="`mobile-${suggestion.id}-${suggestion.name}`"
                    type="button"
                    class="suggestion-item"
                    :class="[
                      index === highlightedSuggestionIndex
                        ? scrolled
                          ? 'bg-black/[0.05]'
                          : 'bg-white/[0.07]'
                        : '',
                    ]"
                    @mouseenter="highlightedSuggestionIndex = index"
                    @mousedown.prevent="goToSuggestion(suggestion)"
                  >
                    <div class="suggestion-thumb">
                      <img
                        v-if="suggestion.image_url"
                        :src="suggestion.image_url"
                        :alt="suggestion.name"
                      />
                      <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-[10px] font-semibold"
                        :class="scrolled ? 'text-black/40' : 'text-white/40'"
                      >
                        No Image
                      </div>
                    </div>

                    <div class="min-w-0 flex-1">
                      <div class="suggestion-name" :class="scrolled ? 'text-black' : 'text-white'">
                        {{ suggestion.name }}
                      </div>
                      <div class="suggestion-type" :class="scrolled ? 'text-black/55' : 'text-white/55'">
                        {{ suggestion.type_label || 'Product' }}
                      </div>
                    </div>
                  </button>

                  <div
                    v-if="!searchSuggestions.length"
                    class="px-4 py-3 text-sm"
                    :class="scrolled ? 'text-black/60' : 'text-white/60'"
                  >
                    No matching products found.
                  </div>
                </template>
              </div>
            </Transition>
          </div>
          <!--kus-->
        </div>
      </div>
    </Transition>
  </header>

  <Teleport to="body">
    <Transition name="overlay-fade">
      <div
        v-if="openMobileMenu"
        class="fixed inset-0 top-[82px] z-[70] bg-black/60 backdrop-blur-sm sm:top-[86px] xl:hidden"
        @click="openMobileMenu = false"
      />
    </Transition>

    <Transition name="panel-slide">
      <div
        v-if="openMobileMenu"
        ref="mobilePanelRef"
        class="fixed bottom-0 left-0 top-[82px] z-[80] w-full max-w-sm overflow-y-auto border-r border-white/10 bg-black text-white sm:top-[86px] xl:hidden"
        @click.stop
      >
        <div class="space-y-2 p-5">
          <Link
            :href="route('frontend.root')"
            class="mobile-link"
            @click="openMobileMenu = false"
          >
            Home
          </Link>

          <div class="mobile-group">
            <div class="mobile-accordion">
              <Link
                :href="route('frontend.tech-products.index')"
                class="flex-1 text-left"
                @click="openMobileMenu = false"
              >
                Mobile Essentials
              </Link>

              <button
                type="button"
                class="ml-3 inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/90 transition hover:bg-white/5"
                :aria-expanded="openMobileTech"
                aria-label="Toggle Mobile Essentials"
                @click.stop="openMobileTech = !openMobileTech"
              >
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
            </div>

            <Transition name="accordion">
              <div v-if="openMobileTech" class="overflow-hidden px-3 pb-3">
                <div class="space-y-2 rounded-2xl border border-white/8 bg-black/20 p-2">
                  <Link
                    :href="route('frontend.tech-products.index', { search: currentSearch || undefined })"
                    class="mobile-sub-link"
                    @click="openMobileMenu = false"
                  >
                    All Mobile Essentials
                  </Link>

                  <div
                    v-for="category in techCategories"
                    :key="category.id"
                    class="rounded-xl border border-white/8 bg-white/[0.03]"
                  >
                    <button
                      type="button"
                      class="flex w-full items-center justify-between px-3 py-3 text-left text-sm font-semibold text-white"
                      @click="toggleMobileTechCategory(category.id)"
                    >
                      <span>{{ category.name }}</span>
                      <svg
                        class="h-4 w-4 transition-transform duration-300"
                        :class="openMobileTechCategoryId === category.id ? 'rotate-180' : ''"
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
                        v-if="openMobileTechCategoryId === category.id"
                        class="overflow-hidden px-3 pb-3"
                      >
                        <div class="space-y-1 rounded-xl border border-white/8 bg-black/25 p-2">
                          <Link
                            :href="route('frontend.tech-products.index', {
                              category: category.name,
                              search: currentSearch || undefined,
                            })"
                            class="mobile-sub-link"
                            @click="openMobileMenu = false"
                          >
                            View All
                          </Link>

                          <Link
                            v-for="brand in category.brands || []"
                            :key="brand.id"
                            :href="route('frontend.tech-products.index', {
                              category: category.name,
                              brand: brand.name,
                              search: currentSearch || undefined,
                            })"
                            class="mobile-sub-link"
                            @click="openMobileMenu = false"
                          >
                            {{ brand.name }}
                          </Link>
                        </div>
                      </div>
                    </Transition>
                  </div>
                </div>
              </div>
            </Transition>
          </div>

          <div class="mobile-group">
            <div class="mobile-accordion">
              <Link
                :href="route('frontend.shoe-products.index')"
                class="flex-1 text-left"
                @click="openMobileMenu = false"
              >
                Featured Footwear
              </Link>

              <button
                type="button"
                class="ml-3 inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/90 transition hover:bg-white/5"
                :aria-expanded="openMobileShoe"
                aria-label="Toggle Featured Footwear"
                @click.stop="openMobileShoe = !openMobileShoe"
              >
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
            </div>

            <Transition name="accordion">
              <div v-if="openMobileShoe" class="overflow-hidden px-3 pb-3">
                <div class="space-y-2 rounded-2xl border border-white/8 bg-black/20 p-2">
                  <Link
                    :href="route('frontend.shoe-products.index', { search: currentSearch || undefined })"
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
                            :href="route('frontend.shoe-products.index', {
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
                            :href="route('frontend.shoe-products.index', {
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

          <div class="mobile-group">
            <div class="mobile-accordion">
              <Link
                :href="route('frontend.cosmetic-products.index')"
                class="flex-1 text-left"
                @click="openMobileMenu = false"
              >
                Cosmetics
              </Link>

              <button
                type="button"
                class="ml-3 inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/90 transition hover:bg-white/5"
                :aria-expanded="openMobileCosmetics"
                aria-label="Toggle Cosmetics"
                @click.stop="openMobileCosmetics = !openMobileCosmetics"
              >
                <svg
                  class="h-4 w-4 transition-transform duration-300"
                  :class="openMobileCosmetics ? 'rotate-180' : ''"
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
            </div>

            <Transition name="accordion">
              <div v-if="openMobileCosmetics" class="overflow-hidden px-3 pb-3">
                <div class="space-y-2 rounded-2xl border border-white/8 bg-black/20 p-2">
                  <Link
                    :href="route('frontend.cosmetic-products.index', { search: currentSearch || undefined })"
                    class="mobile-sub-link"
                    @click="openMobileMenu = false"
                  >
                    All Cosmetics
                  </Link>

                  <div
                    v-for="brand in cosmeticBrands"
                    :key="brand.id"
                    class="rounded-xl border border-white/8 bg-white/[0.03]"
                  >
                    <button
                      type="button"
                      class="flex w-full items-center justify-between px-3 py-3 text-left text-sm font-semibold text-white"
                      @click="toggleMobileCosmeticBrand(brand.id)"
                    >
                      <span class="inline-flex items-center gap-2">
                        <img
                          v-if="brand.logo_url"
                          :src="brand.logo_url"
                          :alt="brand.name"
                          class="h-6 w-6 rounded-full bg-white/10 p-1 object-contain"
                          loading="lazy"
                          decoding="async"
                        />
                        {{ brand.name }}
                      </span>
                      <svg
                        class="h-4 w-4 transition-transform duration-300"
                        :class="openMobileCosmeticBrandId === brand.id ? 'rotate-180' : ''"
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
                        v-if="openMobileCosmeticBrandId === brand.id"
                        class="overflow-hidden px-3 pb-3"
                      >
                        <div class="space-y-1 rounded-xl border border-white/8 bg-black/25 p-2">
                          <Link
                            :href="route('frontend.cosmetic-products.index', {
                              cosmetic_brand: brand.name,
                              search: currentSearch || undefined,
                            })"
                            class="mobile-sub-link"
                            @click="openMobileMenu = false"
                          >
                            View All
                          </Link>

                          <Link
                            v-for="category in brand.categories || []"
                            :key="category.id"
                            :href="route('frontend.cosmetic-products.index', {
                              cosmetic_brand: brand.name,
                              cosmetic_category: category.name,
                              search: currentSearch || undefined,
                            })"
                            class="mobile-sub-link"
                            @click="openMobileMenu = false"
                          >
                            {{ category.name }}
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
            :href="route('frontend.contact-us.index')"
            class="mobile-link"
            @click="openMobileMenu = false"
          >
            Contact Us
          </Link>

          <button
            type="button"
            class="mt-3 inline-flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3.5 text-sm font-semibold text-white transition hover:bg-transparent"
            @click="openCartDrawer"
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
              {{ cartBadgeCount }}
            </span>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>

  <CartDrawer />
</template>

<style scoped>
.nav-link {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.35rem 0;
  font-size: 0.95rem;
  font-weight: 600;
  transition: color 0.25s ease, opacity 0.25s ease;
}

.nav-link-indicator {
  position: absolute;
  left: 0;
  right: 0;
  bottom: -0.35rem;
  height: 2px;
  border-radius: 9999px;
  transform: scaleX(0);
  transform-origin: center;
  transition: transform 0.28s ease;
}

.nav-link:hover .nav-link-indicator,
.nav-link--active .nav-link-indicator,
.nav-link-indicator--active {
  transform: scaleX(1);
}

.search-shell-wrap {
  position: relative;
  overflow: hidden;
  transition: max-width 0.38s ease, opacity 0.28s ease;
}

.search-shell-wrap--closed {
  max-width: 0;
  opacity: 0;
  pointer-events: none;
}

.search-shell-wrap--open {
  max-width: 24rem;
  opacity: 1;
  overflow: visible;
}

.search-shell {
  width: 24rem;
  max-width: 24rem;
}

.search-shell-input {
  transition: border-color 0.22s ease, background-color 0.22s ease, color 0.22s ease;
}

.navbar-icon-btn {
  align-items: center;
  justify-content: center;
  width: 2.8rem;
  height: 2.8rem;
  border-width: 1px;
  border-style: solid;
  border-radius: 9999px;
  transition: all 0.25s ease;
}

.dropdown-panel {
  position: absolute;
  top: calc(100% + 14px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(0, 0, 0, 0.94);
  color: white;
  box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
  backdrop-filter: blur(24px);
  padding: 0.25rem;
}

.submenu-panel {
  position: absolute;
  left: calc(100% + 12px);
  top: -0.25rem;
  min-width: 260px;
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(0, 0, 0, 0.94);
  color: white;
  box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
  backdrop-filter: blur(24px);
  padding: 0.25rem;
}

.dropdown-link {
  display: flex;
  align-items: center;
  min-height: 44px;
  width: 100%;
  padding: 0.75rem 0.875rem;
  border-radius: 16px;
  font-size: 0.92rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.86);
  transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.dropdown-link:hover {
  background: rgba(255, 255, 255, 0.07);
  color: white;
  transform: translateX(2px);
}

.dropdown-link--active {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.mobile-group {
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
}

.mobile-link,
.mobile-accordion {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  border-radius: 1rem;
  padding: 0.95rem 1rem;
  font-size: 0.96rem;
  font-weight: 600;
  color: white;
  transition: background-color 0.2s ease;
}

.mobile-link:hover,
.mobile-accordion:hover {
  background: rgba(255, 255, 255, 0.06);
}

.mobile-sub-link {
  display: flex;
  width: 100%;
  align-items: center;
  border-radius: 0.85rem;
  padding: 0.72rem 0.85rem;
  font-size: 0.88rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.88);
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.mobile-sub-link:hover {
  background: rgba(255, 255, 255, 0.06);
  transform: translateX(2px);
}

.suggestion-item {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 0.8rem;
  padding: 0.8rem 1rem;
  text-align: left;
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.suggestion-item:hover {
  transform: translateY(-1px);
}

.suggestion-thumb {
  display: flex;
  width: 48px;
  height: 48px;
  flex-shrink: 0;
  overflow: hidden;
  border-radius: 16px;
  background: rgba(148, 163, 184, 0.12);
}

.suggestion-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.suggestion-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.35;
}

.suggestion-type {
  margin-top: 0.16rem;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active,
.submenu-fade-enter-active,
.submenu-fade-leave-active,
.overlay-fade-enter-active,
.overlay-fade-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to,
.submenu-fade-enter-from,
.submenu-fade-leave-to,
.overlay-fade-enter-from,
.overlay-fade-leave-to {
  opacity: 0;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to,
.submenu-fade-enter-from,
.submenu-fade-leave-to {
  transform: translateY(8px);
}

.panel-slide-enter-active,
.panel-slide-leave-active {
  transition: transform 0.28s ease, opacity 0.28s ease;
}

.panel-slide-enter-from,
.panel-slide-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

.mobile-search-slide-enter-active,
.mobile-search-slide-leave-active {
  transition: max-height 0.24s ease, opacity 0.2s ease, transform 0.24s ease;
  overflow: hidden;
  will-change: max-height, opacity, transform;
}

.mobile-search-slide-enter-from,
.mobile-search-slide-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-10px);
}

.mobile-search-slide-enter-to,
.mobile-search-slide-leave-from {
  opacity: 1;
  max-height: 220px;
  transform: translateY(0);
}

.accordion-enter-active,
.accordion-leave-active {
  transition: all 0.25s ease;
  overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
  opacity: 0;
  max-height: 0;
}

.accordion-enter-to,
.accordion-leave-from {
  opacity: 1;
  max-height: 500px;
}
</style>
