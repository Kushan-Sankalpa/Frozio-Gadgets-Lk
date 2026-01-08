<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <div>
                        <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                            Locations
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
                                    <Input id="name" label="Location name" v-model="form.name" placeholder=""
                                        :is-required="true" :error="form.errors.name" />

                                </div>

                                <div>
                                    <Input id="email" type="email" label="Location Email" v-model="form.email"
                                        placeholder="" :is-required="true" :error="form.errors.email" />

                                </div>
                                <div>
                                    <Input id="contact_number" type="tel" label="Location contact number"
                                        v-model="form.contact_number" placeholder="" :is-required="true"
                                        :error="form.errors.contact_number" />

                                </div>



                            </div>
                        </section>
                        <hr>
                        <section>
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Images
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                                <div>
                                    <label for="branch_image"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Branch
                                        Image</label>
                                    <FileInputComponent id="branch_image" :isRequired="false" :prvImage="Branch_Image"
                                        v-model="form.branch_image" class="mt-1" />
                                    <p v-if="form.errors.branch_image" class="mt-1 text-sm text-rose-600">
                                        {{ form.errors.branch_image }}
                                    </p>
                                </div>

                                <!-- Gallery / More Images -->
                                <div>
                                    <label for="branch_gallery"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">More
                                        Images</label>
                                    <MultipleFileInputComponent id="branch_gallery"
                                        :initial-urls="Branch_Gallery_Images" v-model="form.branch_gallery"
                                        @remove-initial="(url, index) => form.removedInitial.push(url)"
 />
                                    <p v-if="form.errors.branch_gallery" class="mt-1 text-sm text-rose-600">
                                        {{ form.errors.branch_gallery }}
                                    </p>
                                </div>


                            </div>
                        </section>
                        <hr>
                        <section>
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Business location
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <GooglePlaceInput v-model="form.business_located" v-model:latitude="form.latitude"
                                        v-model:longitude="form.longitude" />


                                </div>

                            </div>
                        </section>
                        <hr>
                        <section>
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Billing details for clients sale
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <Input id="name" label="Company name" v-model="form.company_name" placeholder=""
                                        :is-required="true" :error="form.errors.company_name" />

                                </div>
                                <div>
                                    <Input id="name" label="Address" v-model="form.address" placeholder=""
                                        :is-required="true" :error="form.errors.address" />

                                </div>
                                <div>
                                    <Input id="name" label="City" v-model="form.city" placeholder="" :is-required="true"
                                        :error="form.errors.city" />

                                </div>
                                <div>
                                    <Input id="name" label="State" v-model="form.state" placeholder=""
                                        :is-required="true" :error="form.errors.state" />

                                </div>
                                <div>
                                    <Input id="name" label="Postcode" v-model="form.postcode" placeholder=""
                                        :is-required="true" :error="form.errors.postcode" />

                                </div>

                            </div>
                        </section>
                        <section>
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Opening hours
                            </h6>
                            <OpeningHours v-model="form.opening_hours" />

                        </section>



                        <div class="flex items-center gap-3 justify-end">
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                {{ branch ? 'Update' : 'Save' }}
                            </button>

                            <Link class="btn-secondary" :href="route('branch.index')">
                            Cancel
                            </Link>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import FileInputComponent from '@/components/FileInputComponent.vue';
import MultiSelect from '@/components/MultiSelect.vue';
import MultipleFileInputComponent from '@/components/MultipleFileInputComponent.vue';
import SelectInputComponent from '@/components/SelectInputComponent.vue';
import Input from '@/components/InputField.vue';
import ToggleSwitch from '@/components/ToggleSwitch.vue'
import CheckboxField from '@/components/CheckboxField.vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import OpeningHours from '@/components/OpeningHours.vue';
import GooglePlaceInput from '@/components/GooglePlaceInput.vue';


export default {
    components: {
        Link,
        AppLayout,
        FileInputComponent,
        SelectInputComponent,
        MultiSelect,
        MultipleFileInputComponent,
        Input,
        ToggleSwitch,
        CheckboxField,
        OpeningHours,
        GooglePlaceInput
    },
    props: {
        branch: Object,
        errors: Object,
    },
    data() {
        return {

            form: useForm({
                id: '',
                name: '',
                email: '',
                contact_number: '',
                business_located: '',
                company_name: '',
                address: '',
                city: '',
                state: '',
                postcode: '',
                latitude: '',
                longitude: '',
                opening_hours: [],
                branch_image: null,
                branch_gallery: [],

                removedInitial: [],
            }),

        };
    },
    mounted() {
        if (this.branch) {
            this.form.id = this.branch.id;
            this.form.name = this.branch.name;
            this.form.email = this.branch.email;
            this.form.contact_number = this.branch.contact_number;
            this.form.business_located = this.branch.business_located;
            this.form.latitude = this.branch.latitude;
            this.form.longitude = this.branch.longitude;
            this.form.opening_hours = this.branch.opening_hours;
            this.form.address = this.branch.address;
            this.form.company_name = this.branch.company_name;
            this.form.city = this.branch.city;
            this.form.state = this.branch.state;
            this.form.postcode = this.branch.postcode;

        }
    },
    computed: {
        Branch_Image() {
            if (!this.branch || !Array.isArray(this.branch.media)) return '';

            const image = this.branch.media.find(
                (m) => m.collection_name === 'branch_image'
            );

            return image ? image.original_url : '';
        },
        Branch_Gallery_Images() {
            if (!this.branch || !Array.isArray(this.branch.media)) return [];

            return this.branch.media
                .filter((m) => m.collection_name === 'branch_gallery')
                .map((m) => m.original_url);
        },
    },
    methods: {
        submit() {
            this.form.post(
                this.branch
                    ? route("branch.update")
                    : route("branch.store"),
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
