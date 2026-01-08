<template>
    <div>

        <Head title="Loyalty Program" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

                <!-- Card wrapper -->
                <div class="relative rounded-xl border border-zinc-200 bg-white shadow-sm
                        dark:border-zinc-700/60 dark:bg-zinc-900">

                    <!-- Header -->
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-200 px-4 py-4
                           dark:border-zinc-700/60 flex-wrap">
                        <div>
                            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Loyalty Program
                            </h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Manage and view loyalty tiers inside your business.
                            </p>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-6">

                        <!-- Grid of loyalty tiers -->
                        <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-1 gap-6">

                            <div v-for="tier in loyalty_tier" :key="tier.id"    @click="openEdit(tier)" class="flex items-center justify-between gap-4 p-4 group relative flex items-center gap-4 rounded-xl
           border border-zinc-200 dark:border-zinc-700/60
           bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800
           transition-colors p-4 cursor-pointer">

                                <!-- LEFT: Image + Text -->
                                <div class="flex items-center gap-4">

                                    <!-- Image -->
                                    <img :src="tier.media[0]?.original_url"
                                        class="w-20 h-20 rounded-lg object-cover shadow-sm" alt="Tier Image" />

                                    <!-- Title + Description -->
                                    <div>
                                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ tier.name }}
                                        </h3>

                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ tier.description }}
                                        </p>

                                        <p class="text-xs text-zinc-400 mt-1">
                                            {{ tier.start_points }} – {{ tier.end_points }} points
                                        </p>
                                    </div>
                                </div>

                                <!-- RIGHT SIDE: Edit Button -->
                                <div class="flex items-center gap-3">

                                    <button @click="openEdit(tier)"
                                        class="p-2 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700 transition cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor"
                                            class="w-5 h-5 text-zinc-600 dark:text-zinc-300">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687 1.688m-4.243 4.242l4.242-4.242M6.5 20.002l4.596-.766c.31-.052.592-.205.81-.423l7.5-7.5a2.12 2.12 0 00-3-3l-7.5 7.5c-.218.218-.37.5-.423.81L6.5 20.002z" />
                                        </svg>
                                    </button>
                                </div>

                            </div>


                        </div>

                    </div>
                    <!-- OFFCANVAS EDIT PANEL -->
                    <transition name="fade">
                        <div v-if="showPanel"
                            class="fixed inset-0 z-[999] flex justify-end bg-black/40"
                            @click.self="closePanel" @keydown.esc="closePanel" tabindex="0">

                            <!-- Slide panel -->
                            <transition name="slide-left" mode="out-in">
                                <div v-if="showPanel"
                                    class="h-full w-full sm:max-w-md bg-white dark:bg-zinc-900 shadow-xl p-6">
                                    <!-- Header -->
                                    <div
                                        class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-700">
                                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                            Edit Loyalty Tier
                                        </h2>
                                        <button @click="closePanel"
                                            class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 text-lg cursor-pointer">
                                            ✕
                                        </button>
                                    </div>

                                    <!-- form here -->
                                    <form @submit.prevent="submitEdit" class="mt-6 space-y-4">


                                        <div>
                                            <Input id="name" label="Name" v-model="form.name" placeholder="Name"
                                                :is-required="true" :error="form.errors.name" />
                                        </div>

                                        <div>
                                            <label class="text-sm text-zinc-600 dark:text-zinc-300">Description</label>
                                            <textarea v-model="form.description" class="w-full mt-1 rounded-md border border-zinc-300 dark:border-zinc-700
                           bg-zinc-50 dark:bg-zinc-800 p-2 text-sm text-zinc-900 dark:text-white"></textarea>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <Input id="start_points" label="Start Points" type="number"
                                                    v-model="form.start_points" placeholder="Start Points"
                                                    :is-required="true" :error="form.errors.start_points" />

                                            </div>

                                            <div>
                                                <Input id="end_points" label="End Points" type="number"
                                                    v-model="form.end_points" placeholder="End Points"
                                                    :is-required="true" :error="form.errors.end_points" />

                                            </div>
                                        </div>


                                        <div>
                                            <label for="avatar"
                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">Image</label>
                                            <FileInputComponent id="image" :isRequired="false"
                                                :prvImage="form.existing_image" v-model="form.image" class="mt-1" />
                                        </div>

                                        <!-- Save Button -->
                                        <button type="submit" class="w-full btn-primary text-center">
                                            Save Changes
                                        </button>

                                    </form>
                                </div>
                            </transition>

                        </div>
                    </transition>




                </div>
            </div>
        </AppLayout>
    </div>

</template>

<script lang="ts">
import FileInputComponent from '@/components/FileInputComponent.vue';
import Input from '@/components/InputField.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link, useForm } from '@inertiajs/vue3';

export default {
    components: { AppLayout, Head, FileInputComponent, Input },
    props: {
        loyalty_tier: Array,
        errors: Object,
    },
    data() {
        return {
            showPanel: false,
            form: useForm({
                id: null,
                name: "",
                description: "",
                start_points: "",
                end_points: "",
                image: "",
                existing_image: null,
            }),
            breadcrumbs: [
                { title: 'Loyalty Program', href: route('loyalty-program.index') }
            ],
        }
    },
    computed: {

    },
    methods: {
        openEdit(tier) {
            this.form.id = tier.id
            this.form.name = tier.name
            this.form.description = tier.description
            this.form.start_points = tier.start_points
            this.form.end_points = tier.end_points
            this.form.existing_image = tier.media[0]?.original_url
            this.showPanel = true

        },


        closePanel() {
            this.showPanel = false
        },

        submitEdit() {
            this.form.post(route("loyalty-program.update"), {
                onSuccess: () => {
                    this.form.reset();
                    this.showPanel = false

                    this.$root.showMessage("A Record Was Created Successfully! ");
                },
                onError: () => {
                    this.$root.showMessage("Something went wrong! ");
                }
            });
        },
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.2s ease-out,
        transform 0.2s ease-out;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateX(24px);
}

.slide-step-enter-active,
.slide-step-leave-active {
    transition:
        opacity 0.18s ease-out,
        transform 0.18s ease-out;
}

.slide-step-enter-from,
.slide-step-leave-to {
    opacity: 0;
    transform: translateX(16px);
}

.slide-left-enter-active,
.slide-left-leave-active {
    transition:
        opacity 0.18s ease-out,
        transform 0.18s ease-out;
}

.slide-left-enter-from,
.slide-left-leave-to {
    opacity: 0;
}
</style>
