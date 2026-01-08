<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <div
                class="mx-auto rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                    <div>
                        <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                            Banners
                        </h5>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Create or update homepage banners.
                        </p>
                    </div>
                </div>

                <div class="px-5 py-6">
                    <form @submit.prevent="submit" autocomplete="off" class="space-y-8">
                        <section>
                            <h6
                                class="mb-4 text-sm font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">
                                Basic information
                            </h6>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 items-start">
                                <!-- Left column: name + image under it -->
                                <div class="space-y-5">
                                    <Input
                                        id="banner_name"
                                        label="Banner name"
                                        v-model="form.banner_name"
                                        :is-required="true"
                                        :error="form.errors.banner_name"
                                    />

                                    <div>
                                        <label
                                            for="banner_image"
                                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                                        >
                                            Banner Image
                                        </label>
                                        <FileInputComponent
                                            id="banner_image"
                                            :isRequired="!banner"
                                            :prvImage="Banner_Image"
                                            v-model="form.banner_image"
                                            class="mt-1"
                                        />
                                        <p
                                            v-if="form.errors.banner_image"
                                            class="mt-1 text-sm text-rose-600"
                                        >
                                            {{ form.errors.banner_image }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Right column: description -->
                                <div>
                                    <label
                                        for="banner_description"
                                        class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                                    >
                                        Description
                                    </label>
                                    <textarea
                                        id="banner_description"
                                        v-model="form.banner_description"
                                        rows="4"
                                        class="mt-1 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm
                                               text-zinc-900 placeholder-zinc-400
                                               focus:outline-none focus:ring-2 focus:ring-black-500 focus:border-black-500
                                               dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        placeholder="Short description for this banner (optional)"
                                    ></textarea>
                                    <p
                                        v-if="form.errors.banner_description"
                                        class="mt-1 text-sm text-rose-600"
                                    >
                                        {{ form.errors.banner_description }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <div class="flex items-center gap-3 justify-end">
                            <!-- Save -->
                            <button type="submit" :disabled="form.processing" class="btn-primary">
                                {{ banner ? 'Update' : 'Save' }}
                            </button>

                            <!-- Close / cancel -->
                            <Link class="btn-secondary" :href="route('banner.index')">
                                Close
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
import Input from '@/components/InputField.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

export default {
    components: {
        Link,
        AppLayout,
        FileInputComponent,
        Input,
    },
    props: {
        banner: Object,
        errors: Object,
    },
    data() {
        return {
            breadcrumbs: [],
            form: useForm({
                id: '',
                banner_name: '',
                banner_image: null,
                banner_description: '',
            }),
        };
    },
    mounted() {
        this.breadcrumbs = [
            { title: 'Banners', href: route('banner.index') },
            { title: this.banner ? 'Edit Banner' : 'Add Banner', href: '' },
        ];

        if (this.banner) {
            this.form.id = this.banner.id;
            this.form.banner_name = this.banner.banner_name;
            this.form.banner_description = this.banner.banner_description || '';
        }
    },
    computed: {
        Banner_Image() {
            return this.banner && this.banner.media && this.banner.media.length
                ? this.banner.media[0].original_url
                : '';
        },
    },
    methods: {
        route,
        submit() {
            this.form.post(
                this.banner
                    ? route('banner.update')
                    : route('banner.store'),
                {
                    onSuccess: () => {
                        this.form.reset('banner_image');
                        if (this.$root?.showMessage) {
                            this.$root.showMessage(
                                this.banner ? 'Banner updated successfully!' : 'Banner created successfully!'
                            );
                        }
                    },
                    onError: () => {
                        if (this.$root?.showMessage) {
                            this.$root.showMessage('Something went wrong!');
                        }
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
