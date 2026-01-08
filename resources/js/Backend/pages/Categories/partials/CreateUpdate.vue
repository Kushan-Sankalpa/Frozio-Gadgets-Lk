<template>
  <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click="emitClose"></div>

    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
      <div class="flex items-start justify-between gap-4 mb-4">
        <div>
          <h2 class="text-lg font-semibold text-neutral-900">
            {{ mode === 'create' ? 'Create Category' : 'Update Category' }}
          </h2>
          <p class="text-sm text-neutral-500">Fill the details below.</p>
        </div>

        <button class="text-neutral-500 hover:text-neutral-900" @click="emitClose" type="button">
          ✕
        </button>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <div>
          <label class="block text-sm font-medium text-neutral-700 mb-1">Category Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
            placeholder="e.g. Accessories"
          />
          <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
            {{ form.errors.name }}
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 mb-1">Category Image</label>

          <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-xl border border-neutral-200 overflow-hidden flex items-center justify-center bg-neutral-50">
              <img v-if="previewUrl" :src="previewUrl" class="h-full w-full object-cover" />
              <span v-else class="text-xs text-neutral-400">No Image</span>
            </div>

            <div class="flex-1">
              <input
                type="file"
                accept="image/*"
                @change="onFileChange"
                class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
              />
              <p class="text-xs text-neutral-500 mt-1">
                JPG/PNG/WebP, up to 2MB recommended.
              </p>
              <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">
                {{ form.errors.image }}
              </div>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 mb-1">Status</label>
          <select
            v-model="form.status"
            class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
          >
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">
            {{ form.errors.status }}
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            type="button"
            class="rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100"
            @click="emitClose"
          >
            Cancel
          </button>

          <button type="submit" 
          @click.stop
            :disabled="form.processing"
            class="rounded-full bg-red-500 px-5 py-2 text-sm font-medium text-white hover:bg-red-600 disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : (mode === 'create' ? 'Create' : 'Update') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type CategoryRow = {
  id: number
  name: string
  status: 'active' | 'inactive'
  image_url?: string | null
}

const props = defineProps<{
  show: boolean
  mode: 'create' | 'edit'
  category: CategoryRow | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'saved'): void
}>()

const previewUrl = ref<string | null>(null)

const form = useForm<{
  name: string
  status: 'active' | 'inactive'
  image: File | null
}>({
  name: '',
  status: 'active',
  image: null,
})

function hydrateFromProps() {
  form.clearErrors()

  form.defaults({
    name: props.category?.name ?? '',
    status: props.category?.status ?? 'active',
    image: null,
  })

  form.reset()
  previewUrl.value = props.category?.image_url ?? null
}

watch(() => props.show, (open) => { if (open) hydrateFromProps() })

watch(() => props.category?.id, () => {
  if (props.show) hydrateFromProps()
})


function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null
  form.image = file

  if (file) previewUrl.value = URL.createObjectURL(file)
}

function emitClose() {
  emit('close')
}

function submit() {
  form.clearErrors()

  const isEdit = props.mode === 'edit' && props.category?.id

  if (!isEdit) {
    form.post(route('categories.store'), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => emit('saved'),
    })
    return
  }

  // ✅ IMPORTANT: POST + _method=PUT so Laravel receives fields correctly
  form
    .transform((data) => ({
      ...data,
      _method: 'PUT',
    }))
    .post(route('categories.update', props.category!.id), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => emit('saved'),
      onFinish: () => form.transform((d) => d), // reset transform
    })
}



</script>
