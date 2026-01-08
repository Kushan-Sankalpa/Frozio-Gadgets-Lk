<template>
    <div>

        <Head title="Banners" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div
                    class="relative rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">

                    <!-- HEADER -->
                    <div
                        class="flex items-center justify-between gap-3 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700/60 flex-wrap">
                        <div>
                            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Banners</h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Manage banners and their visibility.
                            </p>
                        </div>

                        <Link class="btn-primary mt-3 sm:mt-0" :href="route('banner.create')"
                            v-if="hasPermission('banner.create')">
                        <span class="ml-1">Add</span>
                        </Link>
                    </div>

                    <!-- BODY -->
                    <div class="p-5">


                        <div class="space-y-4">
                            <!-- Search -->
                            <div class="relative w-full max-w-sm">
                                <input v-model="q" type="text" placeholder="Search location name" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-base text-zinc-800 placeholder-zinc-400
                                           focus:border-indigo-500 focus:ring focus:ring-indigo-200
                                           dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100" />
                                <i class="bx bx-search absolute right-3 top-2.5 text-zinc-400"></i>
                            </div>

                            <hr>

                            <!-- Branch Card -->
                            <div v-for="item in filteredBanners" :key="item.id" @click="goToEdit(item.id)" class="group relative flex items-center gap-4 rounded-xl
           border border-zinc-200 dark:border-zinc-700/60
           bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800
           transition-colors p-4 cursor-pointer">

                                <!-- IMAGE -->
                                <div
                                    class="w-20 h-20 rounded-lg overflow-hidden bg-zinc-200 dark:bg-zinc-800 flex-shrink-0 flex items-center justify-center">
                                    <img v-if="item.media && item.media.length && item.media[0].original_url"
                                        :src="item.media[0].original_url" class="object-cover w-full h-full" />

                                    <div v-else class="flex flex-col items-center text-zinc-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm3 0l3 3 2-2 4 4 3-3" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- LEFT DETAILS -->
                                <div class="flex-1">
                                    <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 capitalize">
                                        {{ item.banner_name }}
                                    </h4>

                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1 line-clamp-2">
                                        {{ item.banner_description }}
                                    </p>
                                </div>

                                <!-- RIGHT SIDE: Status + Dots -->
                                <div class="flex items-center gap-3 relative " @click.stop>

                                    <!-- STATUS BADGE -->
                                    <span :class="item.status === ACTIVE_VALUE
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 z-40'
                                        : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 z-40'"
                                        class="text-xs px-3 py-1 font-semibold rounded-full whitespace-nowrap">
                                        {{ item.status === ACTIVE_VALUE ? 'Active' : 'Inactive' }}
                                    </span>

                                    <!-- THREE DOTS BUTTON -->
                                    <button
                                        class="three-dots-btn p-0 rounded-md hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        @click="toggleDropdown(item.id)">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 text-zinc-600 dark:text-zinc-300" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <circle cx="12" cy="5" r="1.5" />
                                            <circle cx="12" cy="12" r="1.5" />
                                            <circle cx="12" cy="19" r="1.5" />
                                        </svg>
                                    </button>

                                    <!-- DROPDOWN -->
                                    <div v-if="dropdownOpen === item.id" class="dropdown-menu absolute right-0 top-10 z-50 w-40 rounded-xl shadow-lg
                   bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                        <button
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            @click="goToEdit(item.id)">
                                            Edit
                                        </button>

                                        <button class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50
                       dark:text-rose-400 dark:hover:bg-rose-900/40" @click="openDeleteModal(item)">
                                            Delete
                                        </button>

                                        <button
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                            @click="onToggleChange(item, item.status !== ACTIVE_VALUE)">
                                            {{ item.status === ACTIVE_VALUE ? "Deactivate" : "Activate" }}
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </AppLayout>

        <!-- DELETE MODAL -->
        <Transition name="overlay-fade">
            <div v-if="bannerDeleteOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4"
                @click.self="closeDeleteModal">

                <Transition name="modal-pop" appear>
                    <div v-show="bannerDeleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">

                        <div class="mb-4 flex items-start justify-between">
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Permanently delete banner
                            </h4>
                            <button @click="closeDeleteModal"
                                class="p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-full">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p class="text-base text-zinc-700 dark:text-zinc-200 font-medium">
                            {{ bannerDeleteMeta.banner_name }}
                        </p>
                        <p class="mt-2 text-base text-zinc-600 dark:text-zinc-300">
                            This banner and its related data will be permanently deleted.
                        </p>

                        <div class="flex justify-end gap-3 mt-6">
                            <button class="btn-secondary" @click="closeDeleteModal">Cancel</button>
                            <button class="btn-primary bg-rose-600 hover:bg-rose-700 text-white"
                                :disabled="deletingBanner" @click="confirmDeleteBanner">
                                {{ deletingBanner ? 'Deleting…' : 'Delete' }}
                            </button>
                        </div>

                    </div>
                </Transition>

            </div>
        </Transition>
    </div>
</template>

<script lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { BreadcrumbItem } from '@/types'

export default {
    name: 'BannersPage',
    components: { AppLayout, Head, Link },
    props: {
        banners: Array,
    },
    data() {
        return {
            breadcrumbs: [{ title: 'Banners', href: route('banner.index') }] as BreadcrumbItem[],
            dropdownOpen: null as number | null,
            localBanners: [] as any[],
            ACTIVE_VALUE: 1,
            INACTIVE_VALUE: 0,
            bannerDeleteOpen: false,
            bannerDeleteMeta: { id: null as number | null, banner_name: '' },
            deletingBanner: false,
            q: '',
        }
    },
    created() {
        this.localBanners = this.banners.map((b: any) => ({ ...b }))
    },
    computed: {
        filteredBanners() {
            if (!this.q) return this.localBanners
            const query = this.q.toLowerCase()
            return this.localBanners.filter(b =>
                (b.banner_name || '').toLowerCase().includes(query)
            )
        },
    },
    methods: {
        hasPermission(perm: string) {
            const perms = (this as any).$page.props.permission || {}
            return !!perms[perm]
        },
        toggleDropdown(id: number) {
            this.dropdownOpen = this.dropdownOpen === id ? null : id
        },
        handleOutsideClick(event: MouseEvent) {
            const dropdowns = document.querySelectorAll('.dropdown-menu')
            const clickedInside = Array.from(dropdowns).some(el => el.contains(event.target as Node))
            if (!clickedInside) this.dropdownOpen = null
        },
        goToEdit(id: number) {
            ; (this as any).$inertia.visit(route('banner.edit', id))
        },
        openDeleteModal(banner: any) {
            this.bannerDeleteMeta = { id: banner.id, banner_name: banner.banner_name }
            this.bannerDeleteOpen = true
            this.dropdownOpen = null
        },
        closeDeleteModal() {
            this.bannerDeleteOpen = false
        },
        confirmDeleteBanner() {
            this.deletingBanner = true
            const bannerId = this.bannerDeleteMeta.id
            const index = this.localBanners.findIndex((b: any) => b.id === bannerId)
            let backup = null

            if (index !== -1) {
                backup = this.localBanners[index]
                this.localBanners.splice(index, 1)
            }

            ; (this as any).$inertia.delete(route('banner.destroy', bannerId), {
                preserveScroll: true,
                onSuccess: () => {
                    this.deletingBanner = false
                    this.bannerDeleteOpen = false
                },
                onError: () => {
                    if (backup) this.localBanners.splice(index, 0, backup)
                    this.deletingBanner = false
                },
            })
        },
        onToggleChange(banner: any, checked: boolean) {
            const newStatus = checked ? this.ACTIVE_VALUE : this.INACTIVE_VALUE
            const idx = this.localBanners.findIndex((b: any) => b.id === banner.id)
            const prevStatus = banner.status

            if (idx !== -1) {
                this.localBanners[idx].status = newStatus
            }

            ; (this as any).$inertia.post(
                route('banner.toggleStatus', banner.id),
                { status: newStatus },
                {
                    preserveScroll: true,
                    onError: () => {
                        this.localBanners[idx].status = prevStatus
                    },
                }
            )
        },
    },
    mounted() {
        document.addEventListener('click', this.handleOutsideClick)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleOutsideClick)
    },
}
</script>

<style scoped>
.line-clamp-2,
.line-clamp-3 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    -webkit-line-clamp: 2;
}

.line-clamp-3 {
    -webkit-line-clamp: 3;
}

.dropdown-menu {
    animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

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
