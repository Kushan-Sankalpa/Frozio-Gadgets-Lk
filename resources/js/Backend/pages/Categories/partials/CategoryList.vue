<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="flex items-center justify-between mb-4">
      <div class="font-semibold text-neutral-800">All Categories</div>

      <button
        class="inline-flex items-center rounded-full border border-red-500 px-4 py-2 text-sm font-medium text-red-500 hover:bg-red-500 hover:text-white transition"
        @click="openCreate"
        type="button"
      >
        + New Category
      </button>
    </div>

    <!-- ✅ wrap table to bind click handler only here -->
    <div ref="tableWrap" @click="onTableClick">
      <DataTable
        id="categoriesTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 60px;">#</th>
            <th>Name</th>
            <th style="width: 120px;">Status</th>
            <th style="width: 220px;">Actions</th>
          </tr>
        </template>
      </DataTable>
    </div>

    <CreateUpdate
      :show="showModal"
      :mode="modalMode"
      :category="selectedCategory"
      @close="closeModal"
      @saved="onSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import DataTable from '@/Backend/components/DataTable.vue'
import CreateUpdate from './CreateUpdate.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

type CategoryRow = {
  id: number
  name: string
  status: 'active' | 'inactive'
  image_url?: string | null
}

const dataUrl = computed(() => route('categories.data'))

const reloadKey = ref<number>(0)
const showModal = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const selectedCategory = ref<CategoryRow | null>(null)

const columns = [
  { data: 'id', name: 'id' },
  { data: 'name', name: 'name' },
  { data: 'status_badge', name: 'status', orderable: true, searchable: true },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [
  { targets: [2, 3], render: (data: any) => data },
]

function openCreate() {
  modalMode.value = 'create'
  selectedCategory.value = null
  showModal.value = true
}

function openEdit(row: CategoryRow) {
  modalMode.value = 'edit'
  selectedCategory.value = row
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function onSaved() {
  showModal.value = false
  reloadKey.value = Date.now()
}

function onTableClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  const btn = target.closest('button[data-action]') as HTMLButtonElement | null
  if (!btn) return

  // ✅ stop anything else
  e.preventDefault()
  e.stopPropagation()

  const action = btn.dataset.action
  const payload = btn.dataset.payload
  if (!action || !payload) return

  let row: CategoryRow | null = null
  try { row = JSON.parse(payload) } catch { row = null }
  if (!row) return

  if (action === 'edit') {
    openEdit(row)
    return
  }

  if (action === 'delete') {
    const ok = confirm(`Delete category "${row.name}"? This cannot be undone.`)
    if (!ok) return

    router.delete(route('categories.destroy', row.id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>
