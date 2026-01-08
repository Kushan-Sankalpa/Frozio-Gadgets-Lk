<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6 container mx-auto">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">

<div class="border-b border-zinc-200 px-4 sm:px-5 py-4 dark:border-zinc-700/60">
    <h5 class="text-xl sm:text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
        {{ service ? 'Edit Service' : 'Create Service' }}
    </h5>

    <p class="text-sm sm:text-base text-zinc-500 dark:text-zinc-400 mt-0.5">
        Basic details, pricing, and duration.
    </p>

    <div class="flex justify-end gap-2 mt-3">
        <button type="button"
            class="btn-secondary text-sm sm:text-base !px-3 !py-1.5 sm:!px-4 sm:!py-2 cursor-pointer whitespace-nowrap"
            @click="openCategoryModal">
            <i class="bx bx-plus text-base sm:text-xl"></i>
            <span class="ml-1">Add Category</span>
        </button>

        <button type="button"
            class="btn-secondary text-sm sm:text-base !px-3 !py-1.5 sm:!px-4 sm:!py-2 cursor-pointer whitespace-nowrap"
            @click="openVariantModal()">
            <i class="bx bx-plus text-base sm:text-xl"></i>
            <span class="ml-1">Add variant</span>
        </button>
    </div>
</div>

                <div class="px-5 py-6">
                    <form @submit.prevent="submit" autocomplete="off" class="space-y-8">

                        <!-- Basic -->
                        <section>
                            <h6
                                class="mb-4 text-base font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Basic details
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <Input id="name" label="Treatment name" v-model="form.name" :is-required="true"
                                    :error="form.errors.name" placeholder="e.g. Men's Haircut" class="text-base" />

                                <div>
                                    <SelectInputComponent id="service_category_id" label="Menu category"
                                        v-model="form.service_category_id" :options="categoryOptions"
                                        :error="form.errors.service_category_id" class="text-base" />
                                    <p class="mt-1 text-sm text-zinc-500">The category displayed to you, and to clients
                                        online.</p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-base font-medium text-zinc-700 dark:text-zinc-300">
                                        Description (Optional)
                                    </label>
                                    <textarea v-model="form.description" rows="4" maxlength="1000"
                                        class="w-full rounded-md border border-zinc-300 px-3 py-2 text-base dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                                    <div v-if="form.errors.description" class="mt-1 text-base text-rose-600">
                                        {{ form.errors.description }}
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Pricing -->
                        <section>
                            <h6
                                class="mb-4 text-base font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Pricing and duration
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-base font-medium">Price type</label>
                                    <SelectInputComponent id="price_type" v-model="form.price_type"
                                        :options="priceTypeOptions" :error="form.errors.price_type" class="text-base" />
                                </div>

                                <Input id="price" type="number" step="0.01"     :min="0"
 label="Price" v-model="form.price"
                                    :error="form.errors.price" class="text-base font-medium" />

                                <div>
                                    <label class="mb-1 block text-base font-medium">Duration</label>
                                    <SelectInputComponent id="duration_minutes" v-model="form.duration_minutes"
                                        :options="durationOptions" :error="form.errors.duration_minutes"
                                        class="text-base" />
                                </div>
                            </div>

                            <div class="mt-8 space-y-6">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="text-zinc-900 dark:text-zinc-100 font-medium">{{ form.name || '—' }}
                                        </div>
                                        <div class="text-sm text-zinc-500">{{ minutesToLabel(form.duration_minutes) }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-zinc-700 dark:text-zinc-300 font-medium whitespace-nowrap">
                                            <template v-if="form.price_type === 'free'">Free</template>
                                            <template v-else-if="baseShowsFrom">from LKR {{ numberFmt(form.price)
                                            }}</template>
                                            <template v-else>LKR {{ numberFmt(form.price) }}</template>
                                        </span>
                                    </div>
                                </div>

                                <div v-for="v in listVariants" :key="v.id || v._tempId"
                                    class="flex items-start justify-between">
                                    <div>
                                        <div class="text-zinc-900 dark:text-zinc-100">{{ v.name || '—' }}</div>
                                        <div class="text-sm text-zinc-500">{{ minutesToLabel(v.duration_minutes) }}
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-zinc-700 dark:text-zinc-300 font-medium whitespace-nowrap">
                                            <template v-if="v.price_type === 'free'">Free</template>
                                            <template v-else>LKR {{ numberFmt(v.price) }}</template>
                                        </span>

                                        <div class="relative">
                                            <button type="button"
                                                class="cursor-pointer rounded-full px-3 py-1.5 border border-zinc-300 text-sm hover:bg-zinc-50 dark:border-zinc-600 dark:hover:bg-zinc-800"
                                                @click.stop="toggleRowMenu(v)">
                                                Actions <i class="bx bx-chevron-down text-base"></i>
                                            </button>

                                            <Transition enter-active-class="transition ease-out duration-200"
                                                enter-from-class="opacity-0 translate-y-1"
                                                enter-to-class="opacity-100 translate-y-0"
                                                leave-active-class="transition ease-in duration-150"
                                                leave-from-class="opacity-100 translate-y-0"
                                                leave-to-class="opacity-0 translate-y-1">
                                                <div v-if="rowMenuOpenKey === (v.id || v._tempId)"
                                                    class="absolute right-0 z-20 mt-2 w-48 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700/60 dark:bg-zinc-900">
                                                    <button type="button"
                                                        class="w-full cursor-pointer px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800"
                                                        @click="openVariantModal(v)">
                                                        Edit
                                                    </button>
                                                    <div class="border-t border-zinc-200 dark:border-zinc-700/60"></div>
                                                    <button type="button"
                                                        class="w-full cursor-pointer px-4 py-2 text-left text-base text-rose-600 hover:bg-rose-50 dark:hover:bg-zinc-800"
                                                        @click="openVariantDelete(v)">
                                                        Delete
                                                    </button>
                                                </div>
                                            </Transition>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="button"
                                        class="inline-flex items-center cursor-pointer gap-2 text-indigo-600 hover:underline text-base"
                                        @click="openVariantModal()">
                                        <i class="bx bx-plus-circle text-lg"></i> Add variant
                                    </button>
                                </div>
                            </div>
                        </section>






                        <section class="mt-10">
                            <h6 class="mb-3 text-base font-semibold text-zinc-500 uppercase dark:text-zinc-400">
                                Staff members
                            </h6>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                                <div v-for="s in staff" :key="s.id" class="cursor-pointer border rounded-xl p-4 flex items-center justify-between
                   transition-all duration-200
                   hover:border-[var(--primary)]
                   hover:bg-[color-mix(in_srgb,var(--primary)_12%,transparent)]
                   dark:hover:bg-[color-mix(in_srgb,var(--primary)_20%,#0f0f0f)]" @click="toggleStaff(s.id)">
                                    <span class="text-base font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ s.name }}
                                    </span>

                                    <CheckboxField size="xl" :value="s.id" v-model="form.staff" @click.stop />
                                </div>

                            </div>
                        </section>






                        <div class="flex items-center gap-3 justify-end">
                            <button type="submit" :disabled="form.processing"
                                class="btn-primary text-base cursor-pointer">
                                {{ service ? 'Update' : 'Save' }}
                            </button>
                            <Link class="btn-secondary text-base cursor-pointer" :href="route('service.index')">Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Category Modal-->
       <Transition name="overlay-fade">
            <div v-if="showCategory" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                 @click.self="showCategory = false">
                <Transition name="slide-down-slow">
                    <div v-if="showCategory"
                         class="w-full max-w-3xl  rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">
                        <div
                            class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700/60">
                            <h5 class="text-xl md:text-2xl font-semibold">Add category</h5>
                            <button
                                type="button"
                                class="rounded-full p-1.5 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                                @click="showCategory = false"
                            >
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <form @submit.prevent="saveCategory" class="p-6 md:p-8">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-[160px_1fr]">
                                <div class="flex flex-col items-start">
                                    <label class="mb-2 block text-base font-medium">Category image</label>
                                    <FileInputComponent
                                        id="category_image"
                                        v-model="categoryForm.image"
                                        :accept="'image/*'"
                                        :maxSizeMB="10"
                                        :parentCls="'mb-2'"
                                    />
                                    <p class="mt-1 text-xs text-zinc-500">JPG/PNG/WebP up to 10MB.</p>
                                </div>

                                <div class="space-y-4">
                                    <Input
                                        id="cat_name"
                                        label="Category name"
                                        v-model="categoryForm.name"
                                        :is-required="true"
                                        class="text-base"
                                    />

                                    <!-- NEW: hard-coded appointment color palette -->
                                    <div class="relative">
                                        <label class="mb-1 block text-base font-medium">
                                            Appointment color
                                        </label>

                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between rounded-md border border-zinc-300 bg-white px-3 py-2 text-base hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
                                            @click="toggleColorMenu"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <span
                                                    class="h-4 w-4 rounded-full border border-zinc-300"
                                                    :style="{ backgroundColor: selectedColor.value }"
                                                ></span>
                                                <span>{{ selectedColor.label }}</span>
                                            </span>
                                            <i class="bx bx-chevron-down text-xl text-zinc-500"></i>
                                        </button>

                                        <div
                                            v-if="colorMenuOpen"
                                            class="absolute z-[80] mt-2 w-full max-h-80 overflow-auto rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
                                        >
                                            <button
                                                v-for="opt in colorOptions"
                                                :key="opt.value"
                                                type="button"
                                                class="flex w-full items-center justify-between px-4 py-2 text-left text-base hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer"
                                                @click="chooseColor(opt.value)"
                                            >
                                                <span class="inline-flex items-center gap-2">
                                                    <span
                                                        class="h-4 w-4 rounded-full border border-zinc-300"
                                                        :style="{ backgroundColor: opt.value }"
                                                    ></span>
                                                    <span>{{ opt.label }}</span>
                                                </span>
                                                <span
                                                    v-if="categoryForm.color_code === opt.value"
                                                    class="text-indigo-600 text-lg"
                                                >
                                                    ✓
                                                </span>
                                            </button>
                                        </div>

                                        <p class="mt-1 text-xs text-zinc-500">
                                            This color will be used for appointments in this category.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-base font-medium">Description</label>
                                        <textarea
                                            v-model="categoryForm.description"
                                            rows="4"
                                            maxlength="1000"
                                            class="w-full rounded-md border border-zinc-300 px-3 py-2 text-base dark:border-zinc-700 dark:bg-zinc-800"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="mt-6 flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="btn-secondary text-base cursor-pointer"
                                    @click="showCategory = false"
                                >
                                    Cancel
                                </button>
                                <button type="submit" class="btn-primary text-base cursor-pointer">
                                    Add
                                </button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>


        <Transition name="overlay-fade">
            <div v-if="showVariant" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="closeVariantModal">
                <Transition name="slide-down-slow">
                    <div v-if="showVariant" class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">
                        <div
                            class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700/60">
                            <h5 class="text-xl md:text-2xl font-semibold">
                                {{ editingVariant ? 'Edit variant' : 'Add variant' }}
                            </h5>
                            <button type="button"
                                class="rounded-full p-1.5 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeVariantModal">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <section class="p-6 md:p-8">
                            <h6 class="mb-4 text-base font-semibold text-zinc-500 uppercase dark:text-zinc-400">
                                Basic details
                            </h6>

                            <form @submit.prevent="saveVariant" class="space-y-5">
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <Input label="Variant name (Optional)" v-model="variantForm.name"
                                        :is-required="false" class="text-base" />

                                    <div class="md:col-span-1">
                                        <label class="mb-1 block text-base font-medium">Variant description
                                            (Optional)</label>
                                        <textarea v-model="variantForm.description" rows="3" maxlength="200"
                                            class="w-full rounded-md border border-zinc-300 px-3 py-2 text-base dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="mb-1 block text-base font-medium">Price type</label>
                                        <SelectInputComponent id="variant_price_type" v-model="variantForm.price_type"
                                            :options="priceTypeOptions" class="text-base" />
                                    </div>

                                    <Input type="number" step="0.01"     :min="0"
label="Price" v-model="variantForm.price"
                                        class="text-base" />

                                    <div>
                                        <label class="mb-1 block text-base font-medium">Duration</label>
                                        <SelectInputComponent id="variant_duration_minutes"
                                            v-model="variantForm.duration_minutes" :options="durationOptions"
                                            class="text-base" />
                                    </div>
                                </div>

                                <!-- Footer buttons -->
                                <div class="mt-6 flex justify-end gap-3">
                                    <button type="button" class="btn-secondary text-base cursor-pointer"
                                        @click="closeVariantModal">
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn-primary text-base cursor-pointer">
                                        {{ editingVariant ? 'Save changes' : 'Save' }}
                                    </button>
                                </div>
                            </form>
                        </section>
                    </div>
                </Transition>
            </div>
        </Transition>


        <!-- Delete Variant Modal -->
        <Transition name="overlay-fade">
            <div v-if="variantDeleteOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50"
                @click.self="closeVariantDelete">
                <Transition name="slide-down-slow">
                    <div v-if="variantDeleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                        <div class="mb-4 flex items-start justify-between">
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Permanently delete variant
                            </h4>
                            <button type="button"
                                class="ml-4 rounded-full p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeVariantDelete">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p class="mb-2 text-base text-zinc-700 dark:text-zinc-200 font-medium">
                            {{ variantDeleteMeta.name }}
                        </p>
                        <p class="mb-6 text-base text-zinc-600 dark:text-zinc-300">
                            This variant will be permanently deleted. This action cannot be undone.
                        </p>

                        <div class="flex justify-end gap-3">
                            <button type="button" class="btn-secondary text-base cursor-pointer"
                                @click="closeVariantDelete">
                                Cancel
                            </button>
                            <button type="button"
                                class="btn-primary bg-rose-600 hover:bg-rose-700 text-white text-base cursor-pointer"
                                :disabled="deletingVariant" @click="confirmVariantDelete">
                                {{ deletingVariant ? 'Deleting…' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/InputField.vue'
import SelectInputComponent from '@/components/SelectInputComponent.vue'
import FileInputComponent from '@/components/FileInputComponent.vue'

import { Link, Head, useForm, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import CheckboxField from '@/components/CheckboxField.vue'

export default {
    components: { AppLayout, Input, SelectInputComponent, Link, Head, FileInputComponent, CheckboxField },
    props: { service: Object, categories: Array, permission: Object, staff: Array },
    data() {
        return {
            form: useForm({
                id: this.service?.id || '',
                service_category_id: this.service?.service_category_id || '',
                name: this.service?.name || '',
                description: this.service?.description || '',
                price_type: this.service?.price_type || 'fixed',
                price: this.service?.price ,
                duration_minutes: this.service?.duration_minutes || 60,
                status: this.service?.status || 'active',
                staff: this.service?.user?.map(s => s.id) || [],
            }),

            variantsDraft: [],

            rowMenuOpenKey: null,

            showCategory: false,
            categoryForm: useForm({
                name: '',
                color_code: '#60a5fa',
                description: '',
                image: null,
            }),
            colorMenuOpen: false,
            colorOptions: [
                { label: 'Blue',          value: '#93c5fd' },
    { label: 'Dark Blue',     value: '#60a5fa' },
    { label: 'Jordy Blue',    value: '#7aa8ff' },
    { label: 'Indigo',        value: '#818cf8' },
    { label: 'Lavender',      value: '#c4b5fd' },
    { label: 'Purple',        value: '#a78bfa' },
    { label: 'Wisteria',      value: '#bfa6ff' },
    { label: 'Pink',          value: '#f9a8d4' },
    { label: 'Coral',         value: '#fda4af' },
    { label: 'Blood Orange',  value: '#fb923c' },
    { label: 'Orange',        value: '#fbbf24' },
    { label: 'Amber',         value: '#fcd34d' },
    { label: 'Yellow',        value: '#fef08a' },
    { label: 'Lime',          value: '#bef264' },
    { label: 'Green',         value: '#86efac' },
    { label: 'Teal',          value: '#5eead4' },
    { label: 'Cyan',          value: '#67e8f9' },
            ],
            showVariant: false,
            editingVariant: null,
            variantForm: { name: '', description: '', price_type: 'fixed', price: 0, duration_minutes: 60 },

            breadcrumbs: [
                { title: 'Services', href: route('service.index') },
                { title: this.service ? 'Edit' : 'Create', href: '#' }
            ],

            // delete modal state
            variantDeleteOpen: false,
            variantDeleteMeta: { id: null, _tempId: null, name: '', isDraft: false },
            deletingVariant: false,
        }
    },
    computed: {
     selectedColor() {
        const palette = Array.isArray(this.colorOptions) && this.colorOptions.length
            ? this.colorOptions
            : [{ label: 'Blue', value: '#60a5fa' }];

        const currentHex = String(this.categoryForm?.color_code || '').toLowerCase();
        const found = palette.find(c => c.value.toLowerCase() === currentHex);

        return found || palette[0];
    },
        categoryOptions() {
            return (this.categories || []).map(c => ({ id: c.id, name: c.name }))
        },
        listVariants() {
            const saved = this.service?.variants || []
            return [...saved, ...this.variantsDraft]
        },
        baseShowsFrom() {
            return (this.form.price_type === 'from') || (this.listVariants.length > 0 && this.form.price_type !== 'free')
        },
        durationOptions() { return this.genDurationOptions(5, 5, 720) },
        priceTypeOptions() {
            return [
                { id: 'fixed', name: 'Fixed' },
                { id: 'from', name: 'From' },
                { id: 'free', name: 'Free' },
            ]
        },
    },
     mounted() {
        if (!this.service && !this.form.service_category_id) {
            try {
                const url = new URL(window.location.href);
                const categoryId = url.searchParams.get('category_id');

                if (!categoryId || categoryId === 'uncategorized') return;

                const exists = (this.categories || []).some(
                    c => String(c.id) === String(categoryId),
                );

                if (exists) {
                    this.form.service_category_id = Number(categoryId);
                }
            } catch (e) {

            }
        }
    },
    methods: {
        numberFmt(v) {
            const n = parseFloat(v ?? 0)
            return isNaN(n) ? '0' : n.toLocaleString(undefined, { minimumFractionDigits: 0,  maximumFractionDigits: 2,  })
        },
        minutesToLabel(m) {
            const mm = Number(m || 0)
            if (!mm) return '—'
            const h = Math.floor(mm / 60), r = mm % 60
            return h ? `${h}h${r ? ` ${r}min` : ''}` : `${mm}min`
        },
        genDurationOptions(step = 5, min = 5, max = 240) {
            const out = []
            for (let m = min; m <= max; m += step) {
                const h = Math.floor(m / 60), r = m % 60
                let label = ''
                if (h) label += `${h}h`
                if (r) label += (h ? ' ' : '') + `${r}min`
                if (!label) label = '0min'
                out.push({ id: m, name: label })
            }
            return out
        },
        toggleRowMenu(v) {
            const k = v.id || v._tempId
            this.rowMenuOpenKey = (this.rowMenuOpenKey === k ? null : k)
        },

        toggleStaff(id) {
            const arr = this.form.staff
            if (arr.includes(id)) {
                this.form.staff = arr.filter(x => x !== id)
            } else {
                this.form.staff.push(id)
            }
        },
 toggleColorMenu() {
            this.colorMenuOpen = !this.colorMenuOpen
        },
        chooseColor(value) {
            this.categoryForm.color_code = value
            this.colorMenuOpen = false
        },

        submit() {
            const payload = this.service ? this.form : {
                ...this.form.data(),
                variants: this.variantsDraft.map(v => ({
                    name: v.name || null,
                    description: v.description || null,
                    price_type: v.price_type || 'fixed',
                    price: v.price ?? 0,
                    duration_minutes: v.duration_minutes || this.form.duration_minutes,
                })),
            }

            const url = this.service ? route('service.update') : route('service.store')
            router.post(url, payload, { onSuccess: () => router.visit(route('service.index')) })
        },

  openCategoryModal() {
            if (!this.categoryForm.color_code) {
                this.categoryForm.color_code = this.colorOptions[0].value
            }
            this.colorMenuOpen = false
            this.showCategory = true
        },
      saveCategory() {
            this.categoryForm.post(route('service.category.store'), {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.showCategory = false
                    this.colorMenuOpen = false
                    this.categoryForm.reset('name', 'color_code', 'description', 'image')
                }
            })
        },


        openVariantModal(v = null) {
            this.editingVariant = v
            this.variantForm = v
                ? { name: v.name || '', description: v.description || '', price_type: v.price_type || 'fixed', price: v.price ?? 0, duration_minutes: v.duration_minutes || this.form.duration_minutes || 60 }
                : { name: '', description: '', price_type: 'fixed', price: '', duration_minutes: this.form.duration_minutes || 60 }
            this.showVariant = true
        },
        closeVariantModal() { this.showVariant = false; this.editingVariant = null },

        saveVariant() {
            if (this.service?.id && this.editingVariant?.id) {
                router.post(route('service.variant.update'), {
                    id: this.editingVariant.id,
                    service_id: this.service.id,
                    ...this.variantForm,
                }, {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.closeVariantModal()
                        router.reload({
                            only: ['service'],
                            preserveScroll: true,
                            onSuccess: () => {
                                if (this.$page?.props?.service) this.form.price_type = this.$page.props.service.price_type
                            }
                        })
                    }
                })
                return
            }

            if (this.service?.id && !this.editingVariant) {
                router.post(route('service.variant.store'), {
                    service_id: this.service.id, ...this.variantForm
                }, {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.closeVariantModal()
                        router.reload({
                            only: ['service'],
                            preserveScroll: true,
                            onSuccess: () => {
                                if (this.$page?.props?.service) this.form.price_type = this.$page.props.service.price_type
                            }
                        })
                    }
                })
                return
            }

            if (!this.service?.id) {
                if (this.editingVariant && this.editingVariant._tempId) {
                    Object.assign(this.editingVariant, { ...this.variantForm })
                } else {
                    this.variantsDraft.push({ _tempId: Date.now(), ...this.variantForm })
                }
                this.closeVariantModal()
            }
        },

        openVariantDelete(v) {
            this.rowMenuOpenKey = null
            this.variantDeleteMeta = {
                id: v.id ?? null,
                _tempId: v._tempId ?? null,
                name: v.name || 'Untitled variant',
                isDraft: !this.service?.id || !v.id,
            }
            this.variantDeleteOpen = true
        },
        closeVariantDelete() {
            this.variantDeleteOpen = false
            this.deletingVariant = false
        },
        confirmVariantDelete() {
            const meta = this.variantDeleteMeta
            this.deletingVariant = true

            if (meta.isDraft && meta._tempId) {
                this.variantsDraft = this.variantsDraft.filter(x => x._tempId !== meta._tempId)
                this.closeVariantDelete()
                return
            }

            if (this.service?.id && meta.id) {
                router.post(route('service.variant.delete'), { id: meta.id }, {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.closeVariantDelete()
                        router.reload({
                            only: ['service'],
                            preserveScroll: true,
                            onSuccess: () => {
                                if (this.$page?.props?.service) {
                                    this.form.price_type = this.$page.props.service.price_type
                                }
                            }
                        })
                    },
                    onError: () => { this.deletingVariant = false },
                })
            } else {
                this.closeVariantDelete()
            }
        },
    }
}
</script>

<style scoped>
.overlay-fade-enter-active,
.overlay-fade-leave-active {
    transition: opacity .45s ease
}

.overlay-fade-enter-from,
.overlay-fade-leave-to {
    opacity: 0
}

.slide-down-slow-enter-active {
    transition: opacity .55s ease, transform .55s cubic-bezier(.16, 1, .3, 1)
}

.slide-down-slow-leave-active {
    transition: opacity .35s ease, transform .35s cubic-bezier(.7, .2, .1, 1)
}

.slide-down-slow-enter-from,
.slide-down-slow-leave-to {
    opacity: 0;
    transform: translateY(-12px) scale(.98)
}

@media (prefers-reduced-motion:reduce) {

    .overlay-fade-enter-active,
    .overlay-fade-leave-active,
    .slide-down-slow-enter-active,
    .slide-down-slow-leave-active {
        transition-duration: .01ms !important
    }

    .slide-down-slow-enter-from,
    .slide-down-slow-leave-to {
        transform: none !important
    }
}

.slide-down-slow-enter-active,
.slide-down-slow-leave-active {
    transition: opacity .28s ease, transform .28s ease;
}

.slide-down-slow-enter-from,
.slide-down-slow-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(.98);
}
.overlay-fade-enter-active,
.overlay-fade-leave-active {
    transition: opacity .45s ease
}
.overlay-fade-enter-from,
.overlay-fade-leave-to {
    opacity: 0
}
.slide-down-slow-enter-active {
    transition: opacity .55s ease, transform .55s cubic-bezier(.16, 1, .3, 1)
}
.slide-down-slow-leave-active {
    transition: opacity .35s ease, transform .35s cubic-bezier(.7, .2, .1, 1)
}
.slide-down-slow-enter-from,
.slide-down-slow-leave-to {
    opacity: 0;
    transform: translateY(-12px) scale(.98)
}
@media (prefers-reduced-motion:reduce) {
    .overlay-fade-enter-active,
    .overlay-fade-leave-active,
    .slide-down-slow-enter-active,
    .slide-down-slow-leave-active {
        transition-duration: .01ms !important
    }
    .slide-down-slow-enter-from,
    .slide-down-slow-leave-to {
        transform: none !important
    }
}
.slide-down-slow-enter-active,
.slide-down-slow-leave-active {
    transition: opacity .28s ease, transform .28s ease;
}
.slide-down-slow-enter-from,
.slide-down-slow-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(.98);
}
</style>
