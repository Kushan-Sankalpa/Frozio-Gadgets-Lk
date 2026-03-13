<script setup lang="ts">
import { computed, ref, watch } from 'vue'

type ShoeSubcategory = {
  id: number | string
  name: string
}

type ShoeCategory = {
  id: number | string
  name: string
  subcategories?: ShoeSubcategory[]
}

type Filters = {
  search?: string | null
  shoe_category?: string | null
  shoe_subcategory?: string | null
  stock?: string | null
  sale?: boolean
  sort?: string | null
  min_price?: string | number | null
  max_price?: string | number | null
  page?: number
}

const props = defineProps<{
  filters: Filters
  shoeCategories: ShoeCategory[]
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'apply', value: Filters): void
  (e: 'reset'): void
}>()

const localFilters = ref<Filters>({
  search: '',
  shoe_category: '',
  shoe_subcategory: '',
  stock: '',
  sale: false,
  sort: 'latest',
  min_price: '',
  max_price: '',
  page: 1,
})

watch(
  () => props.filters,
  (value) => {
    localFilters.value = {
      search: value?.search || '',
      shoe_category: value?.shoe_category || '',
      shoe_subcategory: value?.shoe_subcategory || '',
      stock: value?.stock || '',
      sale: !!value?.sale,
      sort: value?.sort || 'latest',
      min_price: value?.min_price ?? '',
      max_price: value?.max_price ?? '',
      page: Number(value?.page || 1),
    }
  },
  { immediate: true, deep: true }
)

const activeCategory = computed(() => {
  return props.shoeCategories.find(
    (category) => category.name === localFilters.value.shoe_category
  )
})

const visibleSubcategories = computed(() => {
  return activeCategory.value?.subcategories || []
})

watch(
  () => localFilters.value.shoe_category,
  (newValue, oldValue) => {
    if (newValue !== oldValue) {
      const exists = visibleSubcategories.value.some(
        (subcategory) => subcategory.name === localFilters.value.shoe_subcategory
      )

      if (!exists) {
        localFilters.value.shoe_subcategory = ''
      }
    }
  }
)

function applyFilters() {
  emit('apply', {
    search: String(localFilters.value.search || '').trim(),
    shoe_category: localFilters.value.shoe_category || '',
    shoe_subcategory: localFilters.value.shoe_subcategory || '',
    stock: localFilters.value.stock || '',
    sale: !!localFilters.value.sale,
    sort: localFilters.value.sort || 'latest',
    min_price: localFilters.value.min_price ?? '',
    max_price: localFilters.value.max_price ?? '',
    page: 1,
  })
}

function resetFilters() {
  localFilters.value = {
    search: '',
    shoe_category: '',
    shoe_subcategory: '',
    stock: '',
    sale: false,
    sort: 'latest',
    min_price: '',
    max_price: '',
    page: 1,
  }

  emit('reset')
}
</script>

<template>
  <div class="xl:sticky xl:top-[96px]">
    <form
      class="overflow-hidden rounded-[28px] border border-neutral-200 bg-white shadow-sm"
      @submit.prevent="applyFilters"
    >
      <div class="border-b border-neutral-100 px-5 py-5 sm:px-6">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold text-neutral-900">
              Filters
            </h2>
            <p class="mt-1 text-sm text-neutral-500">
              Refine shoe products quickly.
            </p>
          </div>

          <button
            type="button"
            class="text-sm font-medium text-neutral-500 transition hover:text-neutral-900"
            @click="resetFilters"
          >
            Reset
          </button>
        </div>
      </div>

      <div class="space-y-6 px-5 py-5 sm:px-6">
        <div>
          <label class="mb-2 block text-sm font-semibold text-neutral-900">
            Search
          </label>

          <input
            v-model="localFilters.search"
            type="text"
            placeholder="Search shoe name, SKU, brand..."
            class="w-full rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 outline-none transition focus:border-neutral-900"
          />
        </div>

        <div>
          <label class="mb-3 block text-sm font-semibold text-neutral-900">
            Category
          </label>

          <div class="flex flex-wrap gap-2">
            <button
              type="button"
              class="rounded-full border px-3 py-2 text-sm font-medium transition"
              :class="
                !localFilters.shoe_category
                  ? 'border-neutral-900 bg-neutral-900 text-white'
                  : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-900'
              "
              @click="localFilters.shoe_category = ''"
            >
              All
            </button>

            <button
              v-for="category in shoeCategories"
              :key="category.id"
              type="button"
              class="rounded-full border px-3 py-2 text-sm font-medium transition"
              :class="
                localFilters.shoe_category === category.name
                  ? 'border-neutral-900 bg-neutral-900 text-white'
                  : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-900'
              "
              @click="localFilters.shoe_category = category.name"
            >
              {{ category.name }}
            </button>
          </div>
        </div>

        <div>
          <label class="mb-3 block text-sm font-semibold text-neutral-900">
            Subcategory
          </label>

          <div
            v-if="visibleSubcategories.length"
            class="flex flex-wrap gap-2"
          >
            <button
              type="button"
              class="rounded-full border px-3 py-2 text-sm font-medium transition"
              :class="
                !localFilters.shoe_subcategory
                  ? 'border-neutral-900 bg-neutral-900 text-white'
                  : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-900'
              "
              @click="localFilters.shoe_subcategory = ''"
            >
              All
            </button>

            <button
              v-for="subcategory in visibleSubcategories"
              :key="subcategory.id"
              type="button"
              class="rounded-full border px-3 py-2 text-sm font-medium transition"
              :class="
                localFilters.shoe_subcategory === subcategory.name
                  ? 'border-neutral-900 bg-neutral-900 text-white'
                  : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-900'
              "
              @click="localFilters.shoe_subcategory = subcategory.name"
            >
              {{ subcategory.name }}
            </button>
          </div>

          <div
            v-else
            class="rounded-2xl border border-dashed border-neutral-200 bg-neutral-50 px-4 py-4 text-sm text-neutral-500"
          >
            Select a category to see related subcategories.
          </div>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-neutral-900">
            Sort By
          </label>

          <select
            v-model="localFilters.sort"
            class="w-full rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 outline-none transition focus:border-neutral-900"
          >
            <option value="latest">Latest</option>
            <option value="price_low_high">Price: Low to High</option>
            <option value="price_high_low">Price: High to Low</option>
            <option value="name_az">Name: A to Z</option>
            <option value="name_za">Name: Z to A</option>
          </select>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-neutral-900">
            Stock
          </label>

          <select
            v-model="localFilters.stock"
            class="w-full rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 outline-none transition focus:border-neutral-900"
          >
            <option value="">All stock types</option>
            <option value="in_stock">In Stock</option>
            <option value="out_of_stock">Out of Stock</option>
          </select>
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-neutral-900">
            Price Range
          </label>

          <div class="grid grid-cols-2 gap-3">
            <input
              v-model="localFilters.min_price"
              type="number"
              min="0"
              placeholder="Min"
              class="w-full rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 outline-none transition focus:border-neutral-900"
            />

            <input
              v-model="localFilters.max_price"
              type="number"
              min="0"
              placeholder="Max"
              class="w-full rounded-2xl border border-neutral-200 bg-white px-4 py-3 text-sm text-neutral-900 outline-none transition focus:border-neutral-900"
            />
          </div>
        </div>

        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-neutral-200 px-4 py-3 transition hover:border-neutral-900">
          <input
            v-model="localFilters.sale"
            type="checkbox"
            class="h-4 w-4 rounded border-neutral-300 text-neutral-900 focus:ring-neutral-900"
          />
          <span class="text-sm font-medium text-neutral-800">
            Show sale products only
          </span>
        </label>
      </div>

      <div class="border-t border-neutral-100 px-5 py-5 sm:px-6">
        <button
          type="submit"
          class="inline-flex w-full items-center justify-center rounded-2xl bg-neutral-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-neutral-800 disabled:cursor-not-allowed disabled:opacity-70"
          :disabled="loading"
        >
          {{ loading ? 'Applying...' : 'Apply Filters' }}
        </button>
      </div>
    </form>
  </div>
</template>