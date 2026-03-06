<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import SelectInputComponent from '@/Backend/components/SelectInputComponent.vue'
import axios from 'axios'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { route } from 'ziggy-js'

type SubcategoryPayload = {
  id: number
  category_id: number
  name: string
  status: 'active' | 'inactive'
}

type Option = {
  id: number
  name: string
}

const props = defineProps<{
  mode: 'create' | 'edit'
  subcategory?: SubcategoryPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.subcategory?.id)
const categories = ref<Option[]>([])

const form = useForm({
  category_id: props.subcategory?.category_id ?? null as number | null,
  name: props.subcategory?.name ?? '',
  is_active: (props.subcategory?.status ?? 'active') === 'active',
})

async function fetchCategories() {
  const response = await axios.get(route('admin.shoes.categories.options'))
  categories.value = Array.isArray(response.data) ? response.data : []
}

function submit() {
  form.clearErrors()

  const payload = (data: typeof form) => ({
    ...data,
    status: data.is_active ? 'active' : 'inactive',
  })

  if (!isEdit.value) {
    form
      .transform((data) => payload(data as typeof form))
      .post(route('admin.shoes.subcategories.store'), {
        preserveScroll: true,
        onFinish: () => form.transform((data) => data),
      })

    return
  }

  form
    .transform((data) => ({
      ...payload(data as typeof form),
      _method: 'PUT',
    }))
    .post(route('admin.shoes.subcategories.update', props.subcategory!.id), {
      preserveScroll: true,
      onFinish: () => form.transform((data) => data),
    })
}

onMounted(() => {
  fetchCategories()
})
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Shoe Subcategory' : 'Create Shoe Subcategory'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Shoe Subcategory' : 'Create Shoe Subcategory' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit shoe subcategory details.' : 'Create a new shoe subcategory.' }}
          </p>
        </div>

        <Link
          :href="route('admin.shoes.subcategories.index')"
          class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
        >
          Back
        </Link>
      </div>

      <form
        @submit.prevent="submit"
        class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6"
      >
        <div class="grid grid-cols-1 gap-4">
          <div>
            <SelectInputComponent
              id="shoe_subcategory_category_id"
              label="Category"
              :options="categories"
              v-model="form.category_id"
              :error="form.errors.category_id"
              :isRequired="true"
              valueKey="id"
              labelKey="name"
              placeholder="Select category"
            />
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-neutral-700">Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="e.g. Running"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="flex items-center justify-between rounded-xl border border-neutral-200 px-4 py-3">
              <span class="text-sm font-medium text-neutral-700">Active Status</span>
              <input v-model="form.is_active" type="checkbox" class="h-4 w-4 accent-red-500" />
            </label>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
          <Link
            :href="route('admin.shoes.subcategories.index')"
            class="inline-flex w-full items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100 sm:w-auto"
          >
            Cancel
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex w-full items-center justify-center rounded-full bg-red-500 px-6 py-2 text-sm font-medium text-white hover:bg-red-600 disabled:opacity-50 sm:w-auto"
          >
            {{ form.processing ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>