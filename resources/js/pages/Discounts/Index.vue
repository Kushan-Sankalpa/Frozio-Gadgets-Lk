<template>
    <div>

        <Head title="Discounts" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

                <div class="relative rounded-xl border border-zinc-200 bg-white shadow-sm
                            dark:border-zinc-700/60 dark:bg-zinc-900">

                    <!-- Header -->
                    <div class="flex items-center justify-between gap-3 border-b border-zinc-200 px-4 py-3
                                dark:border-zinc-700/60 flex-wrap">
                        <div>
                            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Discounts</h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Manage discount rules within your business.
                            </p>
                        </div>

                        <Link v-if="hasPermission('discounts.create')" class="btn-primary mt-3 sm:mt-0"
                            :href="route('discounts.create')">
                        Add
                        </Link>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-5">
                        <div class="space-y-4">
                            <!-- SEARCH -->
                            <div class="relative w-full ">
                                <input v-model="q" type="text" placeholder="Search discount name" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-base
                                       text-zinc-800 placeholder-zinc-400 focus:border-indigo-500
                                       focus:ring focus:ring-indigo-200 dark:border-zinc-700
                                       dark:bg-zinc-800 dark:text-zinc-100" />
                                <i class="bx bx-search absolute right-3 top-2.5 text-zinc-400"></i>
                            </div>

                            <hr>

                            <!-- DISCOUNT CARDS -->
                             <template v-if="filteredDiscounts.length > 0">
                            <div v-for="item in filteredDiscounts" :key="item.id" class="group relative flex items-start sm:items-center gap-3 sm:gap-4 rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors p-3 sm:p-4 cursor-pointer" @click="goToEdit(item.id)">

                                <!-- IMAGE -->
                                <div
                                    class="flex h-16 w-16 sm:h-20 sm:w-20 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-zinc-200 dark:bg-zinc-800 mx-auto sm:mx-0">
                                    <img v-if="item.media && item.media.length && item.media[0].original_url"
                                        :src="item.media[0].original_url" class="object-cover w-full h-full" />

                                    <div v-else class="flex flex-col items-center text-zinc-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-8sm:w-8 text-zinc-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm3 0l3 3 2-2 4 4 3-3" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- LEFT DETAILS -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 capitalize truncate">
                                        {{ item.name }}
                                    </h4>

                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1 line-clamp-2">
                                        {{ item.description || 'No description' }}
                                    </p>

                                    <div class="mt-3 text-sm text-zinc-700 dark:text-zinc-300 space-y-1 hidden sm:block">
                                        <p><strong>Type:</strong> {{ item.discount_type }}</p>
                                        <p><strong>Amount:</strong> {{ item.discount_amount }}</p>
                                        <p><strong>Priority:</strong> {{ item.priority }}</p>
                                    </div>

                                        <!-- MOBILE ONLY: Compact details -->
                                        <div class="mt-2 sm:hidden flex flex-wrap gap-2">
                                            <span class="text-xs text-zinc-700 dark:text-zinc-300 px-2">
                                                Type: {{ item.discount_type }}
                                            </span>
                                            <span class="text-xs text-zinc-700 dark:text-zinc-300 px-2">
                                                Amount: {{ item.discount_amount }}
                                            </span>
                                            <span class="text-xs text-zinc-700 dark:text-zinc-300 px-2 ">
                                                Priority:{{ item.priority }}
                                            </span>
                                        </div>
                                </div>

                                <!-- RIGHT ACTIONS -->
                                <div class="flex items-center justify-between sm:justify-end gap-3 relative" @click.stop>

                                    <!-- STATUS BADGE -->
                                    <span :class="item.status === ACTIVE_VALUE
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                        : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'"
                                        class="px-3 py-1 text-xs font-semibold rounded-full whitespace-nowrap">
                                        {{ item.status === ACTIVE_VALUE ? 'Active' : 'Inactive' }}
                                    </span>

                                    <!-- THREE DOTS MENU BUTTON -->
                                    <button class="p-2 rounded-md hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                                        @click="toggleDropdown(item.id)">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6 text-zinc-600 dark:text-zinc-300" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <circle cx="12" cy="5" r="1.5" />
                                            <circle cx="12" cy="12" r="1.5" />
                                            <circle cx="12" cy="19" r="1.5" />
                                        </svg>
                                    </button>

                                    <!-- DROPDOWN MENU -->
                                    <div v-if="dropdownOpen === item.id" class="absolute right-0 top-10 z-30 w-40 rounded-xl shadow-lg
                   bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 overflow-hidden">
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
                                            @click="toggleStatus(item)">
                                            {{ item.status === ACTIVE_VALUE ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            </template>
                        <!-- NO RESULTS MESSAGE -->
                            <div v-else class="py-8 text-center">
                                <p class="text-zinc-500 dark:text-zinc-400">
                                    <template v-if="q">
                                        No discounts found for "{{ q }}"
                                    </template>
                                    <template v-else>
                                        No discounts available
                                    </template>
                                </p>
                            </div>
                        </div>




                    </div>
                </div>
            </div>
        </AppLayout>

        <!-- DELETE CONFIRMATION MODAL -->
        <Transition name="overlay-fade">
            <div v-if="discountDeleteOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4"
                @click.self="closeDeleteModal">
                <Transition name="modal-pop" appear>
                    <div v-show="discountDeleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">

                        <div class="mb-4 flex items-start justify-between">
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Permanently delete discount
                            </h4>
                            <button class="ml-4 rounded-full p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeDeleteModal">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p class="mb-2 text-base text-zinc-700 dark:text-zinc-200 font-medium">
                            {{ discountDeleteMeta.name }}
                        </p>

                        <p class="mb-6 text-base text-zinc-600 dark:text-zinc-300">
                            This discount will be permanently deleted. This action cannot be undone.
                        </p>

                        <div class="flex justify-end gap-3">
                            <button class="btn-secondary text-base cursor-pointer" @click="closeDeleteModal">
                                Cancel
                            </button>

                            <button
                                class="btn-primary bg-rose-600 hover:bg-rose-700 text-white text-base cursor-pointer"
                                :disabled="deleting" @click="confirmDelete">
                                {{ deleting ? "Deleting…" : "Delete" }}
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

export default {
    name: "DiscountsPage",
    components: { AppLayout, Head, Link },

    props: {
        discounts: Array,
    },

    data() {
        return {
            breadcrumbs: [{ title: "Discounts", href: route("discounts") }],
            q: "",
            localRecords: this.discounts ? [...this.discounts] : [],
            ACTIVE_VALUE: 1,
            INACTIVE_VALUE: 0,

            dropdownOpen: false,

            discountDeleteOpen: false,
            discountDeleteMeta: { id: null, name: "" },
            deleting: false,
        }
    },

    computed: {
        filteredDiscounts() {
            if (!this.q) return this.localRecords
            const query = this.q.toLowerCase().trim()
            return this.localRecords.filter(d => d.name.toLowerCase().includes(query))
        }
    },

    methods: {
        toggleDropdown(id) {
            this.dropdownOpen = this.dropdownOpen === id ? null : id;
        },

        toggleStatus(item) {
            const newStatus = item.status === this.ACTIVE_VALUE
                ? this.INACTIVE_VALUE
                : this.ACTIVE_VALUE;

            this.$inertia.post(
                route("discounts.toggleStatus", item.id),
                { status: newStatus },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        item.status = newStatus;
                        this.dropdownOpen = null;

                        this.$root.showMessage(
                            newStatus === this.ACTIVE_VALUE
                                ? "Discount activated"
                                : "Discount deactivated"
                        );
                    }
                }
            );
        },
        hasPermission(perm) {
            const perms = this.$page.props.permission || {}
            return !!perms[perm]
        },

        goToEdit(id) {
            this.$inertia.visit(route("discounts.edit", id))
        },

        openDeleteModal(item) {
            this.discountDeleteMeta = { id: item.id, name: item.name }
            this.discountDeleteOpen = true
        },

        closeDeleteModal() {
            this.discountDeleteOpen = false
        },

        confirmDelete() {
            this.deleting = true
            const id = this.discountDeleteMeta.id

            this.$inertia.delete(route("discounts.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    this.localRecords = this.localRecords.filter(r => r.id !== id)
                    this.discountDeleteOpen = false
                    this.deleting = false
                    this.$root.showMessage("Discount deleted successfully!")
                },
                onError: () => {
                    this.deleting = false
                    this.$root.showMessage("Failed to delete discount.")
                }
            })
        },

        route,
    },
}
</script>
