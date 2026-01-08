<template>
    <div>

        <Head title="Locations" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div
                    class="relative rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                    <!-- Header -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700/60">
                        <div>
                            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Locations
                            </h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Manage info and preferences of locations within
                                your business.
                            </p>
                        </div>

                        <Link class="btn-primary mt-3 sm:mt-0" :href="route('branch.create')"
                            v-if="hasPermission('branch.create')">
                        <span class="ml-1">Add</span>
                        </Link>
                    </div>

                    <!-- Body -->
                    <div class="p-5">
                        <div v-if="localBranches.length" class="space-y-4">
                            <!-- Search -->
                            <div class="relative w-full max-w-sm">
                                <input v-model="q" type="text" placeholder="Search location name"
                                    class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-base text-zinc-800 placeholder-zinc-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                <i class="bx bx-search absolute top-2.5 right-3 text-zinc-400"></i>
                            </div>
                            <hr />

                            <!-- Branch Card -->

                            <div v-for="branch in filteredBranches" :key="branch.id" @click="goToEdit(branch.id)"
                                class="group relative md:flex items-start sm:items-center gap-3 sm:gap-4 rounded-xl border border-zinc-200 bg-white p-3 sm:p-4 transition-colors hover:bg-zinc-50 dark:border-zinc-700/60 dark:bg-zinc-900 dark:hover:bg-zinc-800">
                                <!-- IMAGE -->
                                <div
                                    class="flex  h-16 w-16 sm:h-20 sm:w-20 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-zinc-200 dark:bg-zinc-800">
                                    <img v-if="getBranchImage(branch)" :src="getBranchImage(branch)"
                                        class="h-full w-full object-cover" />

                                    <div v-else class="flex flex-col items-center text-zinc-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm3 0l3 3 2-2 4 4 3-3" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- LEFT DETAILS -->
                                <div class="flex-1 ">
                                    <h4 class="text-lg font-semibold text-zinc-900 capitalize dark:text-zinc-100">
                                        {{ branch.name }}
                                    </h4>

                                    <p class="mt-1 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                                        {{
                                            branch.address ||
                                            'Address not available'
                                        }}
                                    </p>
                                </div>

                                <!-- RIGHT SIDE: Status + Dots -->
                                <div class="relative flex items-center justify-between w-full sm:w-auto sm:justify-start gap-3 mt-3 sm:mt-0"
                                    @click.stop>
                                    <!-- STATUS BADGE -->
                                    <span :class="branch.status === ACTIVE_VALUE
                                            ? 'z-40 bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                            : 'z-40 bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'
                                        " class="rounded-full px-3 py-1 text-xs font-semibold whitespace-nowrap">
                                        {{
                                            branch.status === ACTIVE_VALUE
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>

                                    <!-- THREE DOTS BUTTON -->
                                    <button
                                        class="three-dots-btn rounded-md p-2 transition hover:bg-zinc-200 dark:hover:bg-zinc-700 ml-auto sm:ml-0"
                                        @click="toggleDropdown(branch.id)">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 text-zinc-600 dark:text-zinc-300" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <circle cx="12" cy="5" r="1.5" />
                                            <circle cx="12" cy="12" r="1.5" />
                                            <circle cx="12" cy="19" r="1.5" />
                                        </svg>
                                    </button>

                                    <!-- DROPDOWN -->
                                    <div v-if="dropdownOpen === branch.id"
                                        class="dropdown-menu absolute top-10 right-0 z-50 w-40 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                        <button
                                            class="w-full px-4 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            @click="openGallery(branch)">
                                            View Gallery
                                        </button>
                                        <button
                                            class="w-full px-4 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            @click="goToEdit(branch.id)">
                                            Edit
                                        </button>

                                        <button
                                            class="w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-900/40"
                                            @click="openDeleteModal(branch)">
                                            Delete
                                        </button>

                                        <button
                                            class="w-full px-4 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            @click="
                                                onToggleChange(
                                                    branch,
                                                    branch.status !==
                                                    ACTIVE_VALUE,
                                                )
                                                ">
                                            {{
                                                branch.status ===
                                                    ACTIVE_VALUE
                                                    ? 'Deactivate'
                                                    : 'Activate'
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div v-else class="py-6 text-center text-sm text-zinc-500">
                            No locations found.
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>

        <!-- Delete Modal -->
        <Transition name="overlay-fade">
            <div v-if="branchDeleteOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50"
                @click.self="closeDeleteModal">
                <Transition name="modal-pop" appear>
                    <div v-show="branchDeleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                        <div class="mb-4 flex items-start justify-between">
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Permanently delete location
                            </h4>
                            <button class="ml-4 rounded-full p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeDeleteModal">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p class="mb-2 text-base font-medium text-zinc-700 dark:text-zinc-200">
                            {{ branchDeleteMeta.name }}
                        </p>
                        <p class="mb-6 text-base text-zinc-600 dark:text-zinc-300">
                            This location will be permanently deleted.
                        </p>

                        <div class="flex justify-end gap-3">
                            <button class="btn-secondary text-base" @click="closeDeleteModal">
                                Cancel
                            </button>
                            <button class="btn-primary bg-rose-600 text-base text-white hover:bg-rose-700"
                                :disabled="deletingBranch" @click="confirmDeleteBranch">
                                {{ deletingBranch ? 'Deleting…' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- View Gallery Modal -->
        <Transition name="overlay-fade">
            <div v-if="galleryOpen" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60"
                @click.self="closeGallery">
                <Transition name="modal-pop" appear>
                    <div v-show="galleryOpen"
                        class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                        <!-- Header -->
                        <div class="mb-4 flex items-start justify-between">
                            <h4 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Gallery – {{ galleryBranch?.name }}
                            </h4>

                            <button
                                class="cursor-pointer rounded-full p-1 text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700"
                                @click="closeGallery">
                                <i class="bx bx-x text-3xl"></i>
                            </button>
                        </div>

                        <!-- Images Grid -->
                        <div v-if="galleryImages.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                            <img v-for="(img, i) in galleryImages" :key="i" :src="img"
                                class="h-48 w-full cursor-pointer rounded-xl border border-zinc-200 object-cover transition hover:opacity-80 dark:border-zinc-700"
                                @click="openLightbox(i)" />
                        </div>

                        <div v-else class="py-10 text-center text-zinc-500 dark:text-zinc-400">
                            No gallery images found.
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
        <!-- LIGHTBOX FULLSCREEN -->
        <Transition name="overlay-fade">
            <div v-if="lightboxOpen" class="fixed inset-0 z-[90] flex items-center justify-center bg-black/90"
                @click.self="closeLightbox">
                <!-- FIXED CLOSE BUTTON (Always top-right corner) -->
                <button
                    class="fixed top-5 right-5 z-[95] cursor-pointer rounded-full bg-white/20 p-3 text-white backdrop-blur-sm transition hover:bg-white/40"
                    @click="closeLightbox">
                    <i class="bx bx-x text-3xl"></i>
                </button>

                <Transition name="modal-pop" appear>
                    <div v-show="lightboxOpen" class="relative flex w-full max-w-5xl items-center justify-center px-4">
                        <!-- Fullscreen Image -->
                        <img :src="galleryImages[currentIndex]"
                            class="mx-auto max-h-[85vh] max-w-full rounded-xl shadow-2xl" />

                        <!-- LEFT ARROW -->
                        <button v-if="currentIndex > 0"
                            class="absolute top-1/2 left-5 -translate-y-1/2 cursor-pointer p-3 text-5xl text-white hover:text-indigo-300"
                            @click="prevImage">
                            <i class="bx bx-chevron-left"></i>
                        </button>

                        <!-- RIGHT ARROW -->
                        <button v-if="currentIndex < galleryImages.length - 1"
                            class="absolute top-1/2 right-5 -translate-y-1/2 cursor-pointer p-3 text-5xl text-white hover:text-indigo-300"
                            @click="nextImage">
                            <i class="bx bx-chevron-right"></i>
                        </button>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<script lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

export default {
    name: 'LocationsPage',
    components: { AppLayout, Head, Link },

    props: {
        branches: { type: Array, default: () => [] },
    },

    data() {
        return {
            breadcrumbs: [
                { title: 'Locations', href: route('branch.index') },
            ] as BreadcrumbItem[],
            dropdownOpen: null as number | null,
            localBranches: [] as any[],
            ACTIVE_VALUE: 1,
            INACTIVE_VALUE: 0,
            branchDeleteOpen: false,
            branchDeleteMeta: { id: null, name: '' },
            deletingBranch: false,
            q: '',
            galleryOpen: false,
            galleryImages: [],
            galleryBranch: null,
            lightboxOpen: false,
            currentIndex: 0,
        };
    },

    created() {
        this.localBranches = this.branches.map((b) => ({ ...b })) || [];
    },

    computed: {
        filteredBranches() {
            if (!this.q) return this.localBranches;
            const query = this.q.toLowerCase().trim();
            return this.localBranches.filter((branch) =>
                branch.name.toLowerCase().includes(query),
            );
        },
    },

    methods: {
        getBranchImage(branch) {
            if (!branch?.media) return null;

            const main = branch.media.find(
                (m) => m.collection_name === 'branch_image',
            );

            return main ? main.original_url : null;
        },
        openGallery(branch) {
            this.galleryBranch = branch;

            this.galleryImages = (branch.media || [])
                .filter((m) => m.collection_name === 'branch_gallery')
                .map((m) => m.original_url);

            this.galleryOpen = true;
            this.dropdownOpen = null;
        },

        closeGallery() {
            this.galleryOpen = false;
        },

        // LIGHTBOX
        openLightbox(index) {
            this.currentIndex = index;
            this.lightboxOpen = true;
        },

        closeLightbox() {
            this.lightboxOpen = false;
        },

        nextImage() {
            if (this.currentIndex < this.galleryImages.length - 1)
                this.currentIndex++;
        },

        prevImage() {
            if (this.currentIndex > 0) this.currentIndex--;
        },

        hasPermission(perm: string) {
            const perms = this.$page.props.permission || {};
            return !!perms[perm];
        },

        toggleDropdown(id: number) {
            this.dropdownOpen = this.dropdownOpen === id ? null : id;
        },

        handleOutsideClick(event: MouseEvent) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            const triggers = document.querySelectorAll('.three-dots-btn');

            const clickedEl = event.target as HTMLElement;

            // If clicking inside dropdown -> don't close
            for (const dd of dropdowns) {
                if (dd.contains(clickedEl)) return;
            }

            // If clicking inside the 3-dots button -> don't close
            for (const btn of triggers) {
                if (btn.contains(clickedEl)) return;
            }

            // Otherwise close all dropdowns
            this.dropdownOpen = null;
        },

        goToEdit(id: number) {
            this.$inertia.visit(route('branch.edit', id));
        },

        openDeleteModal(branch: any) {
            this.branchDeleteMeta = { id: branch.id, name: branch.name };
            this.branchDeleteOpen = true;
            this.dropdownOpen = null;
        },

        closeDeleteModal() {
            this.branchDeleteOpen = false;
        },

        confirmDeleteBranch() {
            this.deletingBranch = true;
            const id = this.branchDeleteMeta.id;
            const index = this.localBranches.findIndex((b) => b.id === id);
            let removed = null;

            if (index !== -1) {
                removed = this.localBranches[index];
                this.localBranches.splice(index, 1);
            }

            this.$inertia.delete(route('branch.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.deletingBranch = false;
                    this.branchDeleteOpen = false;
                    this.$root.showMessage('Branch deleted successfully!');
                },
                onError: () => {
                    if (removed) this.localBranches.splice(index, 0, removed);
                    this.deletingBranch = false;
                    this.$root.showMessage('Failed to delete branch.');
                },
            });
        },

        onToggleChange(branch: any, checked: boolean) {
            const newStatus = checked ? this.ACTIVE_VALUE : this.INACTIVE_VALUE;
            const idx = this.localBranches.findIndex((b) => b.id === branch.id);
            const prev = branch.status;

            if (idx !== -1) {
                this.localBranches[idx] = { ...branch, status: newStatus };
            }

            this.$inertia.post(
                route('branch.toggleStatus', branch.id),
                { status: newStatus },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        const msg = newStatus ? 'Activated!' : 'Deactivated!';
                        this.$root.showMessage(msg);
                    },
                    onError: () => {
                        if (idx !== -1)
                            this.localBranches[idx] = {
                                ...branch,
                                status: prev,
                            };
                    },
                },
            );
        },
    },

    mounted() {
        document.addEventListener('click', this.handleOutsideClick);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.handleOutsideClick);
    },
};
</script>

<style scoped>
.overlay-fade-enter-active,
.overlay-fade-leave-active {
    transition: opacity 0.2s ease;
}

.overlay-fade-enter-from,
.overlay-fade-leave-to {
    opacity: 0;
}

.modal-pop-enter-active {
    transition: all 0.25s ease;
}

.modal-pop-enter-from {
    opacity: 0;
    transform: scale(0.95);
}
</style>
