<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 container mx-auto">

            <!-- Card -->
            <div
                class="relative  rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">

                <!-- Header -->
                <div
                    class="flex items-center justify-end gap-3 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700/60">


                    <Link :href="route('users.create')" class="btn-primary whitespace-nowrap text-sm py-2 px-3" v-if="hasPermission('user.create')">
                        <i class="bx bx-plus text-lg"></i>
                        <span class="ml-1">Add Users</span>
                    </Link>

                    <button v-if="hasPermission('user.order')" @click="openOrderDrawer" class="btn-secondary whitespace-nowrap text-sm py-2 px-3">
                        <i class="bx bx-sort text-lg"></i>
                        <span class="ml-1">Change Order</span>
                    </button>

                </div>

                <!-- Body -->
                <div class="p-5">
                    <DataTable id="usersTable" :url="route('users.getdata')" :columns="columns"
                        :columnDefs="columnDefs">
                        <template #header>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- ORDER OFFCANVAS -->
        <div v-if="orderDrawer" class="fixed inset-0 bg-black/30 z-40" @click="closeOrderDrawer"></div>

        <div v-if="orderDrawer"
            class="fixed right-0 top-0 h-full w-full sm:w-96 bg-white dark:bg-zinc-900 shadow-xl z-50 p-4 flex flex-col">

            <!-- HEADER -->
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xl font-semibold">Reorder Team Members</h2>

                <!-- CLOSE BUTTON -->
                <button @click="closeOrderDrawer"
                    class="h-8 w-8 flex items-center justify-center rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>

            <p class="text-sm text-zinc-500 mb-4">
                Drag & drop to reorder. The new order will affect listings across the system.
            </p>

            <!-- DRAGGABLE LIST -->
            <ul id="sortableUsers" class="space-y-2 overflow-y-auto flex-1 pr-2">
                <li v-for="u in orderUsers" :key="u.id"
                    class="p-3 rounded-lg border bg-zinc-50 dark:bg-zinc-800 flex items-center gap-3 cursor-move">

                    <div class="h-10 w-10 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                        <img v-if="u.avatar_url" :src="u.avatar_url" class="h-full w-full object-cover" />
                        <div v-else class="flex items-center justify-center h-full w-full text-lg font-bold">
                            {{ u.name.charAt(0) }}
                        </div>
                    </div>

                    <div>
                        <div class="font-medium">{{ u.name }}</div>
                        <div class="text-xs text-zinc-500">{{ u.email }}</div>
                    </div>

                    <i class="bx bx-menu-alt-left ml-auto text-xl opacity-60"></i>
                </li>
            </ul>

            <button class="btn-primary mt-4 w-full" @click="saveOrder">
                Save Order
            </button>
        </div>

                <!-- DELETE CONFIRMATION MODAL -->
        <Transition name="overlay-fade">
            <div v-if="userDeleteOpen" class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4"
                @click.self="closeDeleteModal">
                <Transition name="modal-pop" appear>
                    <div v-show="userDeleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">

                        <div class="mb-4 flex items-start justify-between">
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Permanently delete user
                            </h4>
                            <button class="ml-4 rounded-full p-1 text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                @click="closeDeleteModal">
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p class="mb-2 text-base text-zinc-700 dark:text-zinc-200 font-medium">
                            {{ userDeleteMeta.name }}
                        </p>

                        <p class="mb-6 text-base text-zinc-600 dark:text-zinc-300">
                            This user will be permanently deleted. This action cannot be undone.
                        </p>

                        <div class="flex justify-end gap-3">
                            <button class="btn-secondary text-base cursor-pointer" @click="closeDeleteModal">
                                Cancel
                            </button>

                            <button
                                class="btn-primary bg-rose-600 hover:bg-rose-700 text-white text-base cursor-pointer"
                                :disabled="deletingUser" @click="confirmDeleteUser">
                                {{ deletingUser ? "Deleting…" : "Delete" }}
                            </button>
                        </div>

                    </div>
                </Transition>
            </div>
        </Transition>


    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { onMounted } from 'vue'
import type { BreadcrumbItem } from '@/types'
import Sortable from "sortablejs"
import { ref } from "vue"
import axios from "axios"
import { getCurrentInstance } from 'vue'

const { appContext } = getCurrentInstance()!
const showMessage = appContext.config.globalProperties.showMessage

const orderDrawer = ref(false)
const orderUsers = ref([])

const userDeleteOpen = ref(false)
const userDeleteMeta = ref({ id: null, name: "" })
const deletingUser = ref(false)

const page = usePage()
const hasPermission = (perm: string): boolean => {
    const perms = page.props.permission || {}
    return !!perms[perm]
}
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: route('users') },
]

// Delete modal methods
const openDeleteModal = (userId, userName) => {
    userDeleteMeta.value = { id: userId, name: userName };
    userDeleteOpen.value = true;
}

const closeDeleteModal = () => {
    userDeleteOpen.value = false;
}

const confirmDeleteUser = () => {
    deletingUser.value = true;
    const id = userDeleteMeta.value.id;

    router.post(
        route('users.remove'),
        { id },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Reload the DataTable
                const $ = (window as any).jQuery
                if ($) {
                    $('#usersTable').DataTable().ajax.reload(null, false);
                }
                userDeleteOpen.value = false;
                deletingUser.value = false;
                showMessage("User deleted successfully!");
            },
            onError: () => {
                deletingUser.value = false;
                showMessage("Failed to delete user.");
            },
        }
    )
}

const openOrderDrawer = () => {
    orderDrawer.value = true

    axios.get(route('users.order.list'))
        .then((response) => {

            orderUsers.value = response.data.sortedUsers || []

            setTimeout(() => {
                const el = document.getElementById("sortableUsers")
                if (el) {
                    Sortable.create(el, {
                        animation: 150,
                        handle: ".bx-menu-alt-left",

                        onEnd: (evt) => {
                            // Update Vue array based on drag
                            const moved = orderUsers.value.splice(evt.oldIndex, 1)[0]
                            orderUsers.value.splice(evt.newIndex, 0, moved)
                        }
                    })
                }
            }, 50)
        })
}



const closeOrderDrawer = () => {
    orderDrawer.value = false
}

const saveOrder = () => {
    const sorted = orderUsers.value.map((u, index) => ({
        id: u.id,
        order: index + 1
    }))

    axios.post(route('users.order.update'), { sorted })
        .then(() => {
            showMessage("Order updated")


        })
        .catch((err) => {
            console.error("Failed to update order:", err)
        })
}

const columns = [
    { data: 'name', name: 'name' },
    { data: 'email', name: 'email' },
    { data: 'role', name: 'role' },
    { data: 'created_at', name: 'created_at' },
    { data: 'action', name: 'action', orderable: false, searchable: false },
]

// Tailwind-flavored renderers
const columnDefs = [

    {
        targets: 0,
        className: 'whitespace-nowrap',
        render: (data: any, type: any, row: any) => {
            if (type !== 'display') return data

            const name = data && data !== '—' ? data : ''
            const fallback = row.email || ''
            const source = (name || fallback).trim()
            const initial = source.charAt(0).toUpperCase() || '?'
            const safeName = name || '—'
            const avatar = row.avatar_url || ''

            let avatarHtml

            if (avatar) {

                avatarHtml = `
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 overflow-hidden">
            <img src="${avatar}"
                 alt="${safeName}"
                 class="h-full w-full object-cover" />
          </div>
        `
            } else {

                avatarHtml = `
          <div class="flex h-12 w-12 items-center justify-center rounded-full
                      bg-rose-100 text-rose-700 text-lg font-semibold uppercase">
            ${initial}
          </div>
        `
            }

            return `
        <div class="flex items-center gap-3">
          ${avatarHtml}
          <span>${safeName}</span>
        </div>
      `
        },
    },

    // Date
    {
        targets: 3, className: 'whitespace-nowrap',
        render: (d: any) => {
            const dt = new Date(d)
            return !d ? '—' : (isNaN(+dt) ? d : dt.toLocaleDateString())
        },
    },
    // Actions
    {
        targets: 4, className: 'whitespace-nowrap',
        render: (_d: any, _t: any, r: any) => {
            if (r.name === 'Super Admin') return ''

            let buttons = ''

            if (hasPermission('user.edit')) {
                buttons += `
          <button data-id="${r.id}" class="tw-btn tw-btn-primary mr-1" title="Edit User">
            <i class="bx bx-edit"></i>
          </button>`
            }

            if (hasPermission('user.delete')) {
                buttons += `
          <button data-id="${r.id}" class="tw-btn tw-btn-danger" title="Delete User">
            <i class="bx bx-trash"></i>
          </button>`
            }

            return buttons || ''
        },
    },
]

onMounted(() => {
    const $: any = (window as any).jQuery
    if (!$) return
    $('#usersTable')
        .on('click', 'button.tw-btn.tw-btn-primary', function (this: HTMLButtonElement) {
            const id = this.getAttribute('data-id')
            if (id) router.visit(route('users.edit', id))
        })
        .on('click', 'button.tw-btn.tw-btn-danger', function (this: HTMLButtonElement) {
            const id = this.getAttribute('data-id')
            if (!id) return

            // Get user name from the row
            const row = $(this).closest('tr');
            const userName = row.find('td:first-child .user-name').text().trim() || 'User';
            
            openDeleteModal(id, userName);   
        })


})
</script>
<style scoped>
@media (max-width: 360px) {
    :deep div.dataTables_wrapper div.dataTables_filter input {
        max-width: 150px !important;
        min-width: 150px !important;
    }
}
/* Modal transitions */
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
