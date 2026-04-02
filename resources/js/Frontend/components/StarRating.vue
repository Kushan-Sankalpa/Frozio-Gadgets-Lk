<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    rating?: number | null
    count?: number | null
    showCount?: boolean
    showValue?: boolean
    size?: 'xs' | 'sm' | 'md'
  }>(),
  {
    rating: null,
    count: null,
    showCount: false,
    showValue: true,
    size: 'sm',
  }
)

const safeRating = computed(() => {
  const value = Number(props.rating ?? 0)
  if (Number.isNaN(value)) return 0
  return Math.max(0, Math.min(5, value))
})

const fillPercent = computed(() => `${(safeRating.value / 5) * 100}%`)

const displayValue = computed(() => {
  if (props.rating === null || typeof props.rating === 'undefined') {
    return '-'
  }

  return (Math.round(safeRating.value * 10) / 10).toFixed(1)
})

const starSizeClass = computed(() => {
  if (props.size === 'xs') return 'h-3 w-3'
  if (props.size === 'md') return 'h-5 w-5'
  return 'h-4 w-4'
})

const valueSizeClass = computed(() => {
  if (props.size === 'xs') return 'text-xs'
  if (props.size === 'md') return 'text-sm'
  return 'text-sm'
})
</script>

<template>
  <div class="inline-flex items-center gap-2">
    <div class="relative inline-flex leading-none">
      <div class="flex text-neutral-200">
        <svg
          v-for="n in 5"
          :key="`star-base-${n}`"
          :class="starSizeClass"
          viewBox="0 0 20 20"
          fill="currentColor"
          aria-hidden="true"
        >
          <path
            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.174c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.363 1.118l1.286 3.97c.3.921-.755 1.688-1.54 1.118l-3.377-2.455a1 1 0 00-1.176 0l-3.377 2.455c-.784.57-1.838-.197-1.539-1.118l1.286-3.97a1 1 0 00-.363-1.118L2.049 9.397c-.783-.57-.38-1.81.588-1.81h4.174a1 1 0 00.95-.69l1.286-3.97z"
          />
        </svg>
      </div>

      <div
        class="absolute inset-y-0 left-0 flex overflow-hidden text-yellow-400"
        :style="{ width: fillPercent }"
        aria-hidden="true"
      >
        <svg
          v-for="n in 5"
          :key="`star-fill-${n}`"
          :class="starSizeClass"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.174c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.363 1.118l1.286 3.97c.3.921-.755 1.688-1.54 1.118l-3.377-2.455a1 1 0 00-1.176 0l-3.377 2.455c-.784.57-1.838-.197-1.539-1.118l1.286-3.97a1 1 0 00-.363-1.118L2.049 9.397c-.783-.57-.38-1.81.588-1.81h4.174a1 1 0 00.95-.69l1.286-3.97z"
          />
        </svg>
      </div>
    </div>

    <span v-if="props.showValue" :class="['font-medium text-slate-700', valueSizeClass]">
      {{ displayValue }}
    </span>

    <span v-if="props.showCount && props.count !== null" class="text-xs text-slate-500">
      ({{ props.count }})
    </span>
  </div>
</template>

