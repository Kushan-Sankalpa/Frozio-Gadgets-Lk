<template>

    <Head title="Roles & Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" container mx-auto">

            <!-- Card -->
            <div
                class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">

                <!-- Header -->
                <div
                    class="flex items-center justify-end gap-3 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700/60">

                    <Link class="btn-primary" :href="route('roles.create')"
                        v-if="hasPermission('roles-permissions.create')">
                    <i class='bx bx-plus'></i> <span class="ml-1">Add Role</span>
                    </Link>

                </div>

                <!-- Body -->
                <div class="p-5">
                    <DataTable id="rolesTable" :url="route('roles.getdata')" :columns="columns"
                        :columnDefs="columnDefs">
                        <template #header>
                            <tr>
                                  <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs sm:text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
                                <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs sm:text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>  
                            </tr>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable from '@/components/Datatable.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { onMounted } from 'vue'
import type { BreadcrumbItem } from '@/types'

const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles & Permissions', href: route('users') },
]

const columns = [
    { data: 'name', name: 'name' },
    { data: 'action', name: 'action', orderable: false, searchable: false },
]
const hasPermission = (perm: string): boolean => {
    const perms = page.props.permission || {}
    return !!perms[perm]
}
// Tailwind-flavored renderers
const columnDefs = [
  {
    targets: 1,
    className: 'whitespace-nowrap',
    render: (_d: any, _t: any, r: any) => {
      if (r.name === 'Super Admin') return ''

      let buttons = ''

      // ✅ Use the helper function instead of `page`
      if (hasPermission('roles-permissions.edit')) {
        buttons += `
          <button data-id="${r.id}" class="tw-btn tw-btn-primary mr-1" title="Edit Role">
            <i class="bx bx-edit"></i>
          </button>`
      }

      if (hasPermission('roles-permissions.view')) {
        buttons += `
          <button data-id="${r.id}" class="tw-btn tw-btn-permission mr-1" title="Permissions">
            <i class="bx bx-lock-open"></i>
          </button>`
      }

      if (hasPermission('roles-permissions.delete')) {
        buttons += `
          <button data-id="${r.id}" class="tw-btn tw-btn-danger" title="Delete Role">
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
    $('#rolesTable')
        .on('click', 'button.tw-btn.tw-btn-primary', function (this: HTMLButtonElement) {
            const id = this.getAttribute('data-id')
            if (id) router.visit(route('roles.edit', id))
        })
        .on('click', 'button.tw-btn.tw-btn-permission', function (this: HTMLButtonElement) {
            const id = this.getAttribute('data-id')
            if (id) router.visit(route('roles.permissions', id))
        })
        .on('click', 'button.tw-btn.tw-btn-danger', function (this: HTMLButtonElement) {
            const id = this.getAttribute('data-id')
            if (!id) return
            if (confirm('Delete this Role?')) {
                router.delete(route('roles.destroy', id), {
                    preserveScroll: true,
                    onSuccess: () => {
                        const $: any = (window as any).jQuery
                        $('#rolesTable').DataTable().ajax.reload(null, false)
                    }
                })
            }
        })


})
</script>
<style scoped></style>
