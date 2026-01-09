<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { route } from 'ziggy-js'

type BannerPayload = {
  id: number
  name: string
  description?: string | null
  video_url?: string | null
}

const props = defineProps<{
  mode: 'create' | 'edit'
  banner?: BannerPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.banner?.id)

const videoPreview = ref<string | null>(props.banner?.video_url ?? null)
const videoClientError = ref<string | null>(null)

const form = useForm<{
  name: string
  description: string
  video: File | null
}>({
  name: props.banner?.name ?? '',
  description: props.banner?.description ?? '',
  video: null,
})

watch(
  () => props.banner?.id,
  () => {
    const b = props.banner
    form.clearErrors()
    form.name = b?.name ?? ''
    form.description = b?.description ?? ''
    form.video = null
    videoPreview.value = b?.video_url ?? null
    videoClientError.value = null
  }
)

function onVideoChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null

  videoClientError.value = null
  form.video = null

  if (!file) {
    videoPreview.value = props.banner?.video_url ?? null
    return
  }

  // client-side: only video + max 100MB
  if (!file.type.startsWith('video/')) {
    videoClientError.value = 'Only video files are allowed.'
    input.value = ''
    return
  }

  const maxBytes = 100 * 1024 * 1024
  if (file.size > maxBytes) {
    videoClientError.value = 'Video must be less than 100MB.'
    input.value = ''
    return
  }

  form.video = file
  videoPreview.value = URL.createObjectURL(file)
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('homebanners.store'), {
      forceFormData: true,
      preserveScroll: true,
    })
    return
  }

  form
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post(route('homebanners.update', props.banner!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((d) => d),
    })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Home Banner' : 'Create Home Banner'" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Home Banner' : 'Create Home Banner' }}</h1>
          <p class="text-sm text-neutral-500">
            {{ isEdit ? 'Edit home banner details.' : 'Create a new home banner.' }}
          </p>
        </div>

        <Link
          :href="route('homebanners.index')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Banner Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
              placeholder="e.g. New Year Promo"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Video</label>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
              <div class="rounded-xl border border-neutral-200 overflow-hidden bg-neutral-50 flex items-center justify-center">
                <video
                  v-if="videoPreview"
                  :src="videoPreview"
                  controls
                  preload="metadata"
                  class="h-24 w-44 bg-black object-cover"
                />
                <div v-else class="h-24 w-44 flex items-center justify-center text-xs text-neutral-400">
                  No Video
                </div>
              </div>

              <div class="flex-1 w-full">
                <input
                  type="file"
                  accept="video/*"
                  @change="onVideoChange"
                  class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
                />
                <p class="text-xs text-neutral-500 mt-1">MP4/MOV/WEBM/AVI/MKV — max 100MB.</p>

                <p v-if="videoClientError" class="mt-1 text-sm text-red-600">{{ videoClientError }}</p>
                <p v-if="form.errors.video" class="mt-1 text-sm text-red-600">{{ form.errors.video }}</p>
              </div>
            </div>

            <p v-if="isEdit" class="mt-2 text-xs text-neutral-500">
              If you don’t upload a new video, the existing video will remain.
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="5"
              class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
              placeholder="Short description about the banner video..."
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-2 sm:justify-end">
          <Link
            :href="route('homebanners.index')"
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
