<template>
  <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
      <div class="font-semibold text-neutral-800">All Invoices</div>

      <button
        class="inline-flex items-center rounded-full border border-red-500 px-4 py-2 text-sm font-medium text-red-500 transition hover:bg-red-500 hover:text-white"
        @click="openCreate"
        type="button"
      >
        + New Invoice
      </button>
    </div>

    <div @click="onTableClick">
      <DataTable
        id="invoicesTable"
        :url="dataUrl"
        :columns="columns"
        :columnDefs="columnDefs"
        :order="[[0, 'desc']]"
        :reloadKey="reloadKey"
      >
        <template #header>
          <tr>
            <th style="width: 70px;">#</th>
            <th>Invoice No</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Contact</th>
            <th>Sales Person</th>
            <th>Payment</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Balance</th>
            <th>Status</th>
            <th style="width: 320px;">Actions</th>
          </tr>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import DataTable from '@/Backend/components/DataTable.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const dataUrl = computed(() => route('invoices.data'))
const reloadKey = ref<number>(0)

const columns = [
  { data: 'id', name: 'id' },
  { data: 'invoice_no', name: 'invoice_no' },
  { data: 'invoice_date', name: 'invoice_date' },
  { data: 'customer_name', name: 'customer_name' },
  { data: 'customer_contact_number', name: 'customer_contact_number' },
  { data: 'sales_person', name: 'sales_person' },
  { data: 'payment_type', name: 'payment_type' },
  { data: 'grand_total', name: 'grand_total' },
  { data: 'paid_amount', name: 'paid_amount' },
  { data: 'balance_due', name: 'balance_due' },
  { data: 'status_badge', name: 'status', orderable: true, searchable: true },
  { data: 'actions', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [
  { targets: [10, 11], render: (data: any) => data },
]

function openCreate() {
  router.visit(route('invoices.create'))
}

function openEdit(id: number) {
  router.visit(route('invoices.edit', id))
}

function openView(id: number) {
  window.open(route('invoices.pdf', id), '_blank')
}

function openDownload(id: number) {
  window.open(route('invoices.download', id), '_blank')
}

function onTableClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  const btn = target.closest('button[data-action]') as HTMLButtonElement | null
  if (!btn) return

  e.preventDefault()
  e.stopPropagation()

  const action = btn.dataset.action
  const id = Number(btn.dataset.id)
  const name = btn.dataset.name || 'this invoice'

  if (!action || !id) return

  if (action === 'edit') {
    openEdit(id)
    return
  }

  if (action === 'view') {
    openView(id)
    return
  }

  if (action === 'download') {
    openDownload(id)
    return
  }

  if (action === 'delete') {
    const ok = confirm(`Delete invoice "${name}"? This cannot be undone.`)
    if (!ok) return

    router.delete(route('invoices.destroy', id), {
      preserveScroll: true,
      onSuccess: () => {
        reloadKey.value = Date.now()
      },
    })
  }
}
</script>