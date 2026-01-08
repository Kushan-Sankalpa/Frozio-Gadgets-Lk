<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <div>
                        <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                            Discounts
                        </h5>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Manage info and preferences of locations within your business.
                        </p>
                    </div>
                </div>

                <div class="px-5 py-6">
                    <form @submit.prevent="submit" autocomplete="off" class="space-y-8">
                        <section>
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Basics
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <Input id="name" label="Discount Name" v-model="form.name" placeholder=""
                                        :is-required="true" :error="form.errors.name" />

                                </div>

                                <div class="md:col-span-1">
                                    <label class="mb-1 block text-sm">Description
                                        (Optional)</label>
                                    <textarea v-model="form.description" rows="3" maxlength="200"
                                        class="w-full rounded-md border border-zinc-300 px-3 py-2 text-base dark:border-zinc-700 dark:bg-zinc-800"></textarea>
                                            <div class="mt-1 flex justify-end">
                                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ form.description.length || 0 }}/200 
                                                </span>
                                            </div>
                                </div>


                            </div>
                        </section>
                        <hr>
                        <section>
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Discount
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <SelectInputComponent id="discount_type" label="Discount Type"
                                        :options="[{ id: 'fixed', name: 'Fixed' }, { id: 'percentage', name: 'Percentage' }]"
                                        :error="form.errors.discount_type" v-model="form.discount_type" />

                                </div>
                                <div>
                                    <Input id="name" type="number" label="Discount Amount" v-model="form.discount_amount"
                                      :max="form.discount_type === 'percentage' ? 100 : null"
                                        placeholder="" :is-required="true" :error="form.errors.discount_amount" />

                                </div>
                                <div>
                                    <Input id="name" type="number" label="Priority" v-model="form.priority" placeholder=""
                                        :is-required="true" :error="form.errors.priority" />
                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Lower numbers have higher priority (e.g., 1 = highest priority)
                                        </p>
                                </div>

                            </div>
                        </section>
                        <hr>
                        <section>
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Validations
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                                <div>
                                    <Input id="name" label="Starts At" v-model="form.starts_at" type="datetime-local"
                                        placeholder="" :is-required="true" :error="form.errors.starts_at" />

                                </div>
                                <div>
                                    <Input id="name" label="Ends At" v-model="form.ends_at" type="datetime-local"
                                        placeholder="" :is-required="true" :error="form.errors.ends_at" />

                                </div>
                                <div>
                                    <SelectInputComponent id="status" label="Status"
                                        :options="[{ id: 1, name: 'Active' }, { id: 0, name: 'Inactive' }]"
                                        :error="form.errors.status" v-model="form.status" />

                                </div>

                                <div>
                                    <label for="discount_image"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Image</label>
                                    <FileInputComponent id="avatar" :isRequired="false" :prvImage="Avatar_Image"
                                        v-model="form.discount_image" class="mt-1" />
                                    <p v-if="form.errors.discount_image" class="mt-1 text-sm text-rose-600">
                                        {{ form.errors.discount_image }}
                                    </p>
                                </div>

                            </div>
                        </section>

                        <!-- OPEN MODAL TRIGGER -->
                        <div class="w-full border rounded-xl px-4 py-3 flex justify-between items-center cursor-pointer"
                            @click="modalOpen = true">
                            <span>{{ selectedText }}</span>
                            <span class="text-indigo-600">Edit</span>
                        </div>

                        <div class="flex items-center gap-3 justify-end">
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                {{ discount ? 'Update' : 'Save' }}
                            </button>

                            <Link class="btn-secondary" :href="route('discounts')">
                            Cancel
                            </Link>
                        </div>

                                                <!-- MODAL -->
                        <div v-if="modalOpen" class="fixed inset-0 bg-black/40 flex justify-center items-center z-50 ">
                            <div
                                class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-6 max-h-[80vh] overflow-y-auto">

                                <!-- HEADER -->
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold">Select services</h2>
                                    <button @click="modalOpen = false" class="text-xl">✕</button>
                                </div>

                                <!-- SEARCH -->
                                <input type="text" v-model="search" placeholder="Search services"
                                    class="w-full border rounded-lg px-4 py-2 mb-4" />

                                <!-- ALL SERVICES -->
                                <div class="flex items-center gap-3 py-3 border-b cursor-pointer" @click="toggleAll()">
                                    <CheckboxField id="all_services" value="all" :modelValue="allSelected"
                                        @update:modelValue="toggleAll()" class="pointer-events-none" />
                                    <span class="font-medium">All services ({{ allServices.length }})</span>
                                </div>

                                <!-- CATEGORY GROUPS -->
                                <div v-for="cat in categories" :key="cat.id" class="mt-4">

                                    <!-- CATEGORY TITLE -->
                                    <div class="flex items-center gap-3 py-2 cursor-pointer"
                                        @click="toggleCategory(cat)">
                                        <CheckboxField :id="'category_' + cat.id" :value="cat.id"
                                            :modelValue="categoryChecked(cat.id)"
                                            @update:modelValue="toggleCategory(cat)" class="pointer-events-none" />

                                        <span class="font-medium capitalize">
                                            {{ cat.name }} ({{ servicesByCat(cat.id).length }})
                                        </span>
                                    </div>

                                    <!-- SERVICES UNDER CATEGORY -->
                                    <div class="ml-8 mt-1">
                                        <div v-for="srv in filteredServices(cat.id)" :key="srv.id"
                                            class="flex items-center gap-3 py-2 cursor-pointer"
                                            @click="toggleService(srv.id)">
                                            <CheckboxField :id="'service_' + srv.id" :value="srv.id"
                                                v-model="form.services" class="pointer-events-none" />
                                            <div>
                                                <p class="font-medium">{{ srv.name }}</p>
                                                <p class="text-xs text-gray-500">{{ srv.duration }} | {{ srv.price }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-2" />
                                </div>

                                <!-- ACTION BUTTONS -->
                                <div class="flex justify-between mt-6">
                                    <button class="px-6 py-2 rounded-lg border"
                                        @click="modalOpen = false">Cancel</button>
                                    <button class="px-6 py-2 rounded-lg bg-black text-white"
                                        @click="applySelection">Apply</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import MultiSelect from '@/components/MultiSelect.vue';
import SelectInputComponent from '@/components/SelectInputComponent.vue';
import Input from '@/components/InputField.vue';
import CheckboxField from '@/components/CheckboxField.vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FileInputComponent from '@/components/FileInputComponent.vue';


export default {
    components: {
        Link,
        AppLayout,
        SelectInputComponent,
        MultiSelect,
        Input,
        CheckboxField,
        FileInputComponent

    },
    props: {
        categories: Array,
        services: Array,
        errors: Object,
        discount: Object,
    },
    data() {
        return {
            modalOpen: false,
            search: "",
            form: useForm({
                id: '',
                services: [],
                name: '',
                description: '',
                discount_type: '',
                discount_amount: '',
                priority: '',
                starts_at: '',
                ends_at: '',
                status: 1,
                discount_image: ''
            }),

        };
    },
    mounted() {
        if (this.discount) {
            this.form.id = this.discount.id;
            this.form.name = this.discount.name;
            this.form.description = this.discount.description;
            this.form.discount_type = this.discount.discount_type;
            this.form.discount_amount = this.discount.discount_amount;
            this.form.priority = this.discount.priority;
            this.form.starts_at = this.discount.starts_at;
            this.form.ends_at = this.discount.ends_at;
            this.form.status = this.discount.status;

            // Pre-select services
            this.form.services = this.discount.services
                ? this.discount.services.map(s => s.id)
                : [];
        }
    },

    computed: {
            pageTitle() { return this.discount ? 'Edit discount' : 'Add a new discount' },
        breadcrumbs() {
            return [
                { title: 'Discounts', href: route('discounts') },
                { title: this.discount ? 'Edit' : 'Create', href: '#' },
            ]
        },
        allServices() {
            return this.services;
        },

        allSelected() {
            return this.form.services.length === this.allServices.length;
        },

        selectedText() {
            if (this.form.services.length === 0) return "All services";
            return `${this.form.services.length} services selected`;
        },
        Avatar_Image() {
            return this.discount && this.discount.media && this.discount.media.length
                ? this.discount.media[0].original_url
                : '';
        },
    },
    methods: {

        servicesByCat(catId) {
            return this.services.filter(s => s.service_category_id === catId);
        },

        categoryChecked(catId) {
            let srv = this.servicesByCat(catId).map(s => s.id);
            return srv.length > 0 && srv.every(id => this.form.services.includes(id));
        },

        toggleCategory(cat) {
            let ids = this.servicesByCat(cat.id).map(s => s.id);

            if (this.categoryChecked(cat.id)) {
                this.form.services = this.form.services.filter(id => !ids.includes(id));
            } else {
                this.form.services = [...new Set([...this.form.services, ...ids])];
            }
        },


        toggleService(id) {
            if (this.form.services.includes(id)) {
                this.form.services = this.form.services.filter(s => s !== id);
            } else {
                this.form.services.push(id);
            }
        },


        toggleAll() {
            if (this.allSelected) {
                this.form.services = [];
            } else {
                this.form.services = this.allServices.map(s => s.id);
            }
        },



        filteredServices(catId) {
            return this.servicesByCat(catId).filter(s =>
                s.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },



        applySelection() {
            this.modalOpen = false;
        },
        submit() {
            if (this.form.services.length === 0) {
        this.$root.showMessage('Please select at least one service for this discount.');
        return;
    }
            this.form.post(
                this.discount
                    ? route("discounts.update")
                    : route("discounts.store"),
                {
                    onSuccess: () => {
                        this.form.reset();
                        this.$root.showMessage('A Record Was Created Successfully!')
                    },
                    onError: () => {
                        this.form.reset("password", "password_confirmation");
                        this.$root.showMessage('Something went wrong! ')

                    },
                }
            );
        },

    },
};
</script>

<style scoped>
input:-webkit-autofill,
input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0 1000px transparent inset;
    -webkit-text-fill-color: inherit;
}
</style>
