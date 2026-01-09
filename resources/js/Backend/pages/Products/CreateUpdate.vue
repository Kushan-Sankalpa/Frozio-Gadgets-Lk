<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch, defineComponent } from 'vue'
import { route } from 'ziggy-js'

// components
import MultiSelect from '@/Backend/components/MultiSelect.vue'
import SelectInputComponent from '@/Backend/components/SelectInputComponent.vue'

type Opt = { id: number; name: string }
type BrandOpt = { id: number; name: string; category_ids?: number[] }
type WarrantyOpt = { id: number; name: string }
type ColorOpt = { id: number; name: string; image_url?: string | null }
type StorageOpt = { id: number; label: string }
type RamOpt = { id: number; label: string }

type ProductPayload = {
  id: number
  category_id: number
  brand_id: number
  model: string
  device_status: 'used' | 'brandnew'
  price_lkr: number

  discount_type?: 'percent' | 'price' | null
  discount_value?: number | null
  in_stock?: boolean
  stock_count?: number | null
  status?: 'active' | 'inactive'
  is_featured?: boolean
  warranty_option_id?: number | null
  warranty_period?: string | null

  os?: string | null
  // ✅ multi
  storage_option_ids?: number[]
  ram_option_ids?: number[]

  // legacy fallback (if old data exists)
  storage_option_id?: number | null
  ram_option_id?: number | null

  display_size?: string | null
  display_type?: string | null
  resolution?: string | null
  rear_camera?: string | null
  front_camera?: string | null
  connectivity?: string | null
  battery_mah?: number | null

  short_description?: string | null
  long_description?: string | null

  color_ids?: number[]
  main_image_url?: string | null
  gallery_urls?: string[]
}

const page = usePage()

const categories = computed<Opt[]>(() => (page.props.categories as any) || [])
const brandsAll = computed<BrandOpt[]>(() => (page.props.brands as any) || [])
const warrantyOptions = computed<WarrantyOpt[]>(() => (page.props.warranties as any) || [])
const colors = computed<ColorOpt[]>(() => (page.props.colors as any) || [])
const storages = computed<StorageOpt[]>(() => (page.props.storages as any) || [])
const rams = computed<RamOpt[]>(() => (page.props.rams as any) || [])

const props = defineProps<{
  mode: 'create' | 'edit'
  product?: ProductPayload | null
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.product?.id)

const mainPreview = ref<string | null>(props.product?.main_image_url ?? null)
const galleryPreview = ref<string[]>(props.product?.gallery_urls ?? [])
const galleryNewPreview = ref<string[]>([])

// legacy -> multi fallback for edit
const initialStorageIds =
  props.product?.storage_option_ids?.length
    ? props.product.storage_option_ids
    : props.product?.storage_option_id
      ? [Number(props.product.storage_option_id)]
      : []

const initialRamIds =
  props.product?.ram_option_ids?.length
    ? props.product.ram_option_ids
    : props.product?.ram_option_id
      ? [Number(props.product.ram_option_id)]
      : []

const form = useForm({
  // required
  category_id: props.product?.category_id ?? (categories.value[0]?.id ?? null),
  brand_id: props.product?.brand_id ?? null,
  model: props.product?.model ?? '',
  device_status: props.product?.device_status ?? 'brandnew',
  price_lkr: props.product?.price_lkr ?? '',

  // optional common
  discount_type: props.product?.discount_type ?? null,
  discount_value: props.product?.discount_value ?? null,
  in_stock: props.product?.in_stock ?? true,
  stock_count: props.product?.stock_count ?? null,
  status: props.product?.status ?? 'active',
  is_featured: props.product?.is_featured ?? false,
  warranty_option_id: props.product?.warranty_option_id ?? null,
  warranty_period: props.product?.warranty_period ?? '',

  // optional specs
  os: props.product?.os ?? '',
  // ✅ multi
  storage_option_ids: initialStorageIds as number[],
  ram_option_ids: initialRamIds as number[],
  display_size: props.product?.display_size ?? '',
  display_type: props.product?.display_type ?? '',
  resolution: props.product?.resolution ?? '',
  rear_camera: props.product?.rear_camera ?? '',
  front_camera: props.product?.front_camera ?? '',
  connectivity: props.product?.connectivity ?? '',
  battery_mah: props.product?.battery_mah ?? '',

  // descriptions
  short_description: props.product?.short_description ?? '',
  long_description: props.product?.long_description ?? '',

  // relations
  color_ids: props.product?.color_ids ?? [],

  // images
  main_image: null as File | null,
  gallery_images: [] as File[],
  clear_gallery: false,
})

const brandsFiltered = computed(() => {
  if (!form.category_id) return brandsAll.value
  return brandsAll.value.filter(b => (b.category_ids || []).includes(Number(form.category_id)))
})

watch(
  () => form.category_id,
  () => {
    const b = brandsAll.value.find(x => x.id === Number(form.brand_id))
    if (form.brand_id && b && !(b.category_ids || []).includes(Number(form.category_id))) {
      form.brand_id = null
    }
  }
)

watch(
  () => form.in_stock,
  (val) => {
    if (!val) form.stock_count = null
  }
)

function onMainImageChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] || null
  form.main_image = file
  if (file) mainPreview.value = URL.createObjectURL(file)
}

function onGalleryChange(e: Event) {
  const input = e.target as HTMLInputElement
  const files = input.files ? Array.from(input.files) : []
  form.gallery_images = files
  galleryNewPreview.value = files.map(f => URL.createObjectURL(f))
}

function submit() {
  form.clearErrors()

  if (!isEdit.value) {
    form.post(route('products.store'), {
      forceFormData: true,
      preserveScroll: true,
    })
    return
  }

  form
    .transform((data: any) => ({ ...data, _method: 'PUT' }))
    .post(route('products.update', props.product!.id), {
      forceFormData: true,
      preserveScroll: true,
      onFinish: () => form.transform((d: any) => d),
    })
}

/** color multi select with image circle */
// const ColorMultiSelect = defineComponent({
//   props: {
//     options: { type: Array as any, required: true },
//     modelValue: { type: Array as any, required: true },
//   },
//   emits: ['update:modelValue'],
//   setup(props, { emit }) {
//     const open = ref(false)

//     const selected = computed<number[]>({
//       get: () => props.modelValue,
//       set: (v) => emit('update:modelValue', v),
//     })

//     function toggle(id: number) {
//       const s = new Set(selected.value)
//       if (s.has(id)) s.delete(id)
//       else s.add(id)
//       selected.value = Array.from(s)
//     }

//     const selectedObjects = computed(() => {
//       const map = new Map((props.options as any[]).map(o => [o.id, o]))
//       return selected.value.map(id => map.get(id)).filter(Boolean)
//     })

//     return { open, selected, toggle, selectedObjects }
//   },
//   template: `
//     <div class="relative">
//       <button type="button"
//         class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm text-left outline-none hover:bg-neutral-50"
//         @click="open = !open">
//         <span v-if="selected.length === 0" class="text-neutral-400">Select colors</span>
//         <span v-else class="text-neutral-700">{{ selected.length }} selected</span>
//       </button>

//       <div v-if="open" class="absolute z-50 mt-2 w-full rounded-xl border border-neutral-200 bg-white shadow-lg p-2 max-h-72 overflow-auto">
//         <div v-for="c in options" :key="c.id"
//           class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-neutral-50 cursor-pointer"
//           @click="toggle(c.id)">
//           <div class="h-6 w-6 rounded-full overflow-hidden border border-neutral-200 bg-neutral-50">
//             <img v-if="c.image_url" :src="c.image_url" class="h-full w-full object-cover" />
//           </div>
//           <div class="text-sm text-neutral-700">{{ c.name }}</div>
//           <div class="ml-auto text-xs text-neutral-500" v-if="selected.includes(c.id)">✓</div>
//         </div>
//       </div>

//       <div v-if="selectedObjects.length" class="mt-2 flex flex-wrap gap-2">
//         <div v-for="c in selectedObjects" :key="c.id" class="inline-flex items-center gap-2 rounded-full border border-neutral-200 px-3 py-1 text-xs">
//           <div class="h-4 w-4 rounded-full overflow-hidden border border-neutral-200 bg-neutral-50">
//             <img v-if="c.image_url" :src="c.image_url" class="h-full w-full object-cover" />
//           </div>
//           <span class="text-neutral-700">{{ c.name }}</span>
//         </div>
//       </div>
//     </div>
//   `,
// })

const deviceStatusOptions = [
  { id: 'brandnew', name: 'Brand New' },
  { id: 'used', name: 'Used' },
]

const discountTypeOptions = [
  { id: null, name: 'None' },
  { id: 'percent', name: '%' },
  { id: 'price', name: 'Price' },
]

const inStockOptions = [
  { id: true, name: 'Yes' },
  { id: false, name: 'No' },
]

const statusOptions = [
  { id: 'active', name: 'Active' },
  { id: 'inactive', name: 'Inactive' },
]
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Product' : 'Create Product'" />

    <div class="p-6 space-y-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">{{ isEdit ? 'Update Product' : 'Create Product' }}</h1>
          <p class="text-sm text-neutral-500">Required fields first, then optional details.</p>
        </div>

        <Link
          :href="route('products.index')"
          class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 hover:bg-neutral-100 transition"
        >
          Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-6 shadow-sm space-y-6">

        <!-- REQUIRED -->
        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Required</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
              <SelectInputComponent
                id="category_id"
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
              <SelectInputComponent
                id="brand_id"
                label="Brand (filtered by category)"
                :options="brandsFiltered"
                v-model="form.brand_id"
                :error="form.errors.brand_id"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select brand"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Device Model</label>
              <input
                v-model="form.model"
                type="text"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                placeholder="e.g. iPhone 13 Pro"
              />
              <p v-if="form.errors.model" class="mt-1 text-sm text-red-600">{{ form.errors.model }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="device_status"
                label="Device Status"
                :options="deviceStatusOptions"
                v-model="form.device_status"
                :error="form.errors.device_status"
                :isRequired="true"
                valueKey="id"
                labelKey="name"
                placeholder="Select status"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-neutral-700 mb-1">Price (LKR)</label>
              <input
                v-model="form.price_lkr"
                type="number"
                step="0.01"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                placeholder="e.g. 250000"
              />
              <p v-if="form.errors.price_lkr" class="mt-1 text-sm text-red-600">{{ form.errors.price_lkr }}</p>
            </div>
          </div>
        </div>

        <!-- COMMON OPTIONAL -->
        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Common Optional</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <SelectInputComponent
                id="discount_type"
                label="Discount Type"
                :options="discountTypeOptions"
                v-model="form.discount_type"
                :error="form.errors.discount_type"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="None"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Discount Value</label>
              <input
                v-model="form.discount_value"
                type="number"
                step="0.01"
                min="0"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                placeholder="e.g. 10 or 5000"
              />
              <p v-if="form.errors.discount_value" class="mt-1 text-sm text-red-600">{{ form.errors.discount_value }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="in_stock"
                label="In Stock"
                :options="inStockOptions"
                v-model="form.in_stock"
                :error="form.errors.in_stock"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="Select"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Stock Count</label>
              <input
                v-model="form.stock_count"
                type="number"
                step="1"
                min="0"
                :disabled="!form.in_stock"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500 disabled:bg-neutral-100"
                placeholder="e.g. 10"
              />
              <p v-if="form.errors.stock_count" class="mt-1 text-sm text-red-600">{{ form.errors.stock_count }}</p>
            </div>

            <div>
              <SelectInputComponent
                id="status"
                label="Status"
                :options="statusOptions"
                v-model="form.status"
                :error="form.errors.status"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="Select status"
              />
            </div>

            <div class="flex items-center gap-3 mt-6">
              <input id="featured" type="checkbox" v-model="form.is_featured" class="h-4 w-4" />
              <label for="featured" class="text-sm font-medium text-neutral-700">Is Featured</label>
            </div>

            <div>
              <SelectInputComponent
                id="warranty_option_id"
                label="Warranty Type"
                :options="[{ id: null, name: 'None' }, ...warrantyOptions]"
                v-model="form.warranty_option_id"
                :error="form.errors.warranty_option_id"
                :isRequired="false"
                valueKey="id"
                labelKey="name"
                placeholder="None"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Warranty Period</label>
              <input
                v-model="form.warranty_period"
                type="text"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                placeholder="e.g. 12 months / 1 year"
              />
              <p v-if="form.errors.warranty_period" class="mt-1 text-sm text-red-600">{{ form.errors.warranty_period }}</p>
            </div>
          </div>
        </div>

        <!-- SPECS OPTIONAL -->
        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Optional Specifications</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">OS</label>
              <input v-model="form.os" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Connectivity</label>
              <input v-model="form.connectivity" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500" placeholder="e.g. 5G, WiFi 6" />
            </div>

            <!-- ✅ STORAGE MULTI -->
            <div>
              <MultiSelect
                id="storage_option_ids"
                label="Storage (multi)"
                placeholder="Pick storage options"
                :options="storages"
                v-model="form.storage_option_ids"
                valueKey="id"
                labelKey="label"
                :required="false"
                :error="form.errors.storage_option_ids"
              />
              <p v-if="form.errors['storage_option_ids.0']" class="mt-1 text-sm text-red-600">{{ form.errors['storage_option_ids.0'] }}</p>
            </div>

            <!-- ✅ RAM MULTI -->
            <div>
              <MultiSelect
                id="ram_option_ids"
                label="RAM (multi)"
                placeholder="Pick ram options"
                :options="rams"
                v-model="form.ram_option_ids"
                valueKey="id"
                labelKey="label"
                :required="false"
                :error="form.errors.ram_option_ids"
              />
              <p v-if="form.errors['ram_option_ids.0']" class="mt-1 text-sm text-red-600">{{ form.errors['ram_option_ids.0'] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Display Size</label>
              <input v-model="form.display_size" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500" placeholder="e.g. 6.1 inch" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Display Type</label>
              <input v-model="form.display_type" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500" placeholder="e.g. OLED" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Resolution</label>
              <input v-model="form.resolution" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500" placeholder="e.g. 1170x2532" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Battery (mAh)</label>
              <input v-model="form.battery_mah" type="number" min="0" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500" placeholder="e.g. 5000" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Rear Camera</label>
              <input v-model="form.rear_camera" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500" placeholder="e.g. 48MP + 12MP" />
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Front Camera</label>
              <input v-model="form.front_camera" type="text" class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500" placeholder="e.g. 12MP" />
            </div>

            <div class="md:col-span-2">
  <MultiSelect
    id="color_ids"
    label="Colors (multi select)"
    placeholder="Pick colors"
    :options="colors"
    v-model="form.color_ids"
    valueKey="id"
    labelKey="name"
    imageKey="image_url"
    :required="false"
    :error="form.errors.color_ids"
  />
</div>

          </div>
        </div>

        <!-- DESCRIPTIONS -->
        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Descriptions</h2>
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Short Description</label>
              <textarea v-model="form.short_description" rows="3"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Long Description</label>
              <textarea v-model="form.long_description" rows="6"
                class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"></textarea>
            </div>
          </div>
        </div>

        <!-- IMAGES -->
        <div>
          <h2 class="text-base font-semibold text-neutral-900 mb-3">Images</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Main Image</label>
              <div class="flex items-center gap-4">
                <div class="h-20 w-20 rounded-xl border border-neutral-200 overflow-hidden bg-neutral-50 flex items-center justify-center">
                  <img v-if="mainPreview" :src="mainPreview" class="h-full w-full object-cover" />
                  <span v-else class="text-xs text-neutral-400">No Image</span>
                </div>

                <div class="flex-1">
                  <input type="file" accept="image/*" @change="onMainImageChange"
                    class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
                  <p v-if="form.errors.main_image" class="mt-1 text-sm text-red-600">{{ form.errors.main_image }}</p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 mb-1">Gallery Images</label>
              <input type="file" accept="image/*" multiple @change="onGalleryChange"
                class="block w-full text-sm text-neutral-600 file:mr-4 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200" />
              <p v-if="form.errors.gallery_images" class="mt-1 text-sm text-red-600">{{ form.errors.gallery_images }}</p>

              <div v-if="isEdit" class="mt-2 flex items-center gap-2">
                <input id="clearGallery" type="checkbox" v-model="form.clear_gallery" class="h-4 w-4" />
                <label for="clearGallery" class="text-sm text-neutral-700">Clear existing gallery on update</label>
              </div>
            </div>

            <div class="md:col-span-2 space-y-2">
              <div v-if="galleryPreview.length" class="text-sm font-medium text-neutral-700">Existing Gallery</div>
              <div class="flex flex-wrap gap-2">
                <img v-for="(u, i) in galleryPreview" :key="'old-'+i" :src="u" class="h-16 w-16 rounded-lg object-cover border" />
              </div>

              <div v-if="galleryNewPreview.length" class="text-sm font-medium text-neutral-700 mt-2">New Gallery (to upload)</div>
              <div class="flex flex-wrap gap-2">
                <img v-for="(u, i) in galleryNewPreview" :key="'new-'+i" :src="u" class="h-16 w-16 rounded-lg object-cover border" />
              </div>
            </div>
          </div>
        </div>

        <!-- ACTIONS -->
        <div class="flex flex-col sm:flex-row gap-2 sm:justify-end pt-2">
          <Link
            :href="route('products.index')"
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
