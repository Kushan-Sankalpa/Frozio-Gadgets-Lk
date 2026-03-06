<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
      <div class="font-semibold text-neutral-800">All Shoe Categories</div>

      <button
        class="inline-flex items-center rounded-full border border-red-500 px-4 py-2 text-sm font-medium text-red-500 transition hover:bg-red-500 hover:text-white"
        @click="openCreate"
        type="button"
      >
        + Add Shoe Category
      </button>
    </div>

    <div class="rounded-xl border border-dashed border-neutral-200 bg-neutral-50 px-6 py-12 text-center text-sm text-neutral-500">
      No data yet.
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

function openCreate() {
  router.visit(route('admin.shoes.categories.create'))
}
</script>