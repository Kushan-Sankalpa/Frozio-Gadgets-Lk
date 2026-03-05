<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import MultiSelect from '@/Backend/components/MultiSelect.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type BrandOption = { id: number; name: string }

type CategoryPayload = {
  id: number
  name: string
  status: 'active' | 'inactive'
  image_url?: string | null
  brand_ids?: number[]
}

const props = defineProps<{
  mode: 'create' | 'edit'
  brands: BrandOption[]
  category?: CategoryPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && props.category?.id)

const previewUrl = ref<string | null>(props.category?.image_url ?? null)

const form = useForm<{
  name: string
  status: 'active' | 'inactive'
  image: File | null
  brand_ids: number[]
}>({
  name: props.category?.name ?? '',
  status: props.category?.status ?? 'active',
  image: null,
  brand_ids: props.category?.brand_ids ?? [],
})

watch(
  () => props.category?.id,
  () => {
    const c = props.category
    form.name = c?.name ?? ''
    form.status = (c?.status as any) ?? 'active'
    form.image = null
    form.brand_ids = c?.brand_ids ?? []
    previewUrl.value = c?.image_url ?? null
  }
)

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null
  form.image = file
  if (file) previewUrl.value = URL.createObjectURL(file)
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('categories.store'), {
      forceFormData: true,
      preserveScroll: true,
    })
    return
  }

  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(route('categories.update', props.category!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((d) => d),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Category' : 'Create Category'" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Category' : 'Create Category' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit category details.' : 'Create a new category.' }}
          </p>
        </div>

        <Link
          :href="route('categories.index')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <!-- Category Name -->
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Category Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
              placeholder="e.g. Accessories"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Status -->
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
          </div>

          <!-- Brands MultiSelect -->
          <div class="md:col-span-2">
            <MultiSelect
              id="categoryBrands"
              label="Brands"
              placeholder="Select brands"
              :options="brands"
              v-model="form.brand_ids"
              valueKey="id"
              labelKey="name"
              :error="form.errors.brand_ids"
            />
            <p v-if="form.errors.brand_ids" class="mt-1 text-sm text-red-600">
              {{ form.errors.brand_ids }}
            </p>
          </div>

          <!-- Category Image -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Category Image</label>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <div class="h-20 w-20 rounded-xl border border-neutral-200 overflow-hidden bg-neutral-50 flex items-center justify-center">
                <img v-if="previewUrl" :src="previewUrl" class="h-full w-full object-cover" />
                <span v-else class="text-xs text-neutral-400">No Image</span>
              </div>

              <div class="flex-1 w-full">
                <input
                  type="file"
                  accept="image/*"
                  @change="onFileChange"
                  class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                />
                <p class="text-xs text-neutral-500 mt-1">JPG/PNG/WebP, up to 2MB recommended.</p>
                <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-2 sm:justify-end">
          <Link
            :href="route('categories.index')"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
          >
            Cancel
          </Link>

          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-red-500 px-6 py-2 text-sm font-medium text-white hover:bg-red-600 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (isEdit ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
