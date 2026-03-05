<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch, onBeforeUnmount } from 'vue'
import { route } from 'ziggy-js'

type ColorPayload = {
  id: number
  name: string
  status: 'active' | 'inactive'
  image_url?: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  color?: ColorPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.color?.id)

const imagePreview = ref<string | null>(props.color?.image_url ?? null)
const selectedFileName = ref<string>('')

const fileInputRef = ref<HTMLInputElement | null>(null)
let objectUrl: string | null = null

const form = useForm<{
  name: string
  status: 'active' | 'inactive'
  image: File | null
}>({
  name: props.color?.name ?? '',
  status: props.color?.status ?? 'active',
  image: null,
})

function revokePreviewUrl() {
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl)
    objectUrl = null
  }
}

watch(
  () => props.color,
  (c) => {
    revokePreviewUrl()
    form.clearErrors()
    form.name = c?.name ?? ''
    form.status = c?.status ?? 'active'
    form.image = null
    imagePreview.value = c?.image_url ?? null
    selectedFileName.value = ''
  },
  { immediate: true, deep: true }
)

onBeforeUnmount(() => {
  revokePreviewUrl()
})

function openFilePicker() {
  fileInputRef.value?.click()
}

function onImageChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null

  form.image = file
  selectedFileName.value = file ? file.name : ''

  revokePreviewUrl()

  if (file) {
    objectUrl = URL.createObjectURL(file)
    imagePreview.value = objectUrl
  } else {
    imagePreview.value = props.color?.image_url ?? null
  }
}

function onPreviewError() {
  imagePreview.value = null
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('colors.store'), {
      forceFormData: true,
      preserveScroll: true,
    })
    return
  }

  form
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post(route('colors.update', props.color!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((d) => d),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Color' : 'Create Color'" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Color' : 'Create Color' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit color details.' : 'Create a new color.' }}
          </p>
        </div>

        <Link
          :href="route('colors.index')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Color Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
              placeholder="e.g. Midnight Black"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

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

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-neutral-700 mb-1">Color Image</label>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <div class="h-20 w-20 rounded-xl border border-neutral-200 overflow-hidden bg-neutral-50 flex items-center justify-center shrink-0">
                <img
                  v-if="imagePreview"
                  :src="imagePreview"
                  class="h-full w-full object-cover"
                  @error="onPreviewError"
                />
                <span v-else class="text-xs text-neutral-400">No Image</span>
              </div>

              <div class="flex-1 w-full">
                <input
                  ref="fileInputRef"
                  type="file"
                  accept="image/*"
                  @change="onImageChange"
                  class="hidden"
                />

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                  <button
                    type="button"
                    @click="openFilePicker"
                    class="inline-flex w-fit items-center justify-center rounded-full border border-neutral-200 bg-neutral-100 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-200 transition"
                  >
                    Choose File
                  </button>

                  <span class="text-sm text-neutral-600">
                    {{
                      selectedFileName
                        ? selectedFileName
                        : (isEdit && props.color?.image_url ? 'Current image saved' : 'No file chosen')
                    }}
                  </span>
                </div>

                <p class="text-xs text-neutral-500 mt-2">JPG/PNG/WebP up to 2MB.</p>
                <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-2 sm:justify-end">
          <Link
            :href="route('colors.index')"
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