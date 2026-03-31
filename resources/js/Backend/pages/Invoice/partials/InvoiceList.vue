<template>
  <div class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <div class="font-semibold text-neutral-800">All Invoices</div>
        <div class="text-sm text-neutral-500">Search and filter invoices, then change order status or open actions from smooth dropdown menus.</div>
      </div>

      <button
        class="inline-flex items-center rounded-full border border-red-500 px-4 py-2 text-sm font-medium text-red-500 transition hover:bg-red-500 hover:text-white"
        @click="openCreate"
        type="button"
      >
        + New Invoice
      </button>
    </div>

    <div v-if="flashMessage" :class="flashMessage.type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700'" class="rounded-2xl border px-4 py-3 text-sm">
      {{ flashMessage.text }}
    </div>

    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_220px_220px_auto_auto]">
        <div>
          <label class="mb-1 block text-sm font-medium text-neutral-700">Search</label>
          <input
            v-model="filters.q"
            type="text"
            placeholder="Invoice no / customer name / contact"
            class="w-full rounded-xl border border-neutral-200 bg-white px-4 py-2.5 outline-none transition focus:border-slate-900"
            @keyup.enter="applyFilters"
          />
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-neutral-700">Order Status</label>
          <select
            v-model="filters.order_status"
            class="w-full rounded-xl border border-neutral-200 bg-white px-4 py-2.5 outline-none transition focus:border-slate-900"
          >
            <option value="">All statuses</option>
            <option value="reserved">Reserved</option>
            <option value="confirmed">Confirmed</option>
            <option value="dispatched">Dispatched</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-neutral-700">Payment</label>
          <select
            v-model="filters.payment_type"
            class="w-full rounded-xl border border-neutral-200 bg-white px-4 py-2.5 outline-none transition focus:border-slate-900"
          >
            <option value="">All payments</option>
            <option value="unpaid">Unpaid</option>
            <option value="mixed">Mixed</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="advance">Advance</option>
          </select>
        </div>

        <button
          type="button"
          class="mt-6 inline-flex h-[46px] items-center justify-center rounded-full bg-slate-950 px-5 text-sm font-semibold text-white transition hover:bg-slate-800"
          @click="applyFilters"
        >
          Apply
        </button>

        <button
          type="button"
          class="mt-6 inline-flex h-[46px] items-center justify-center rounded-full border border-neutral-200 px-5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-100"
          @click="resetFilters"
        >
          Reset
        </button>
      </div>
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
            <th style="width: 190px;">Order Status</th>
            <th>Invoice Status</th>
            <th>Payment</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Balance</th>
            <th style="width: 170px;">Actions</th>
          </tr>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios'
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import DataTable from '@/Backend/components/DataTable.vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const reloadKey = ref<number>(0)
const flashMessage = ref<{ type: 'success' | 'error'; text: string } | null>(null)

const filters = reactive({
  q: '',
  order_status: '',
  payment_type: '',
})

const dataUrl = computed(() => {
  const params = new URLSearchParams()

  if (filters.q) params.set('q', filters.q)
  if (filters.order_status) params.set('order_status_filter', filters.order_status)
  if (filters.payment_type) params.set('payment_type_filter', filters.payment_type)

  const query = params.toString()
  const url = route('invoices.data')

  return query ? `${url}?${query}` : url
})

const columns = [
  { data: 'id', name: 'id' },
  { data: 'invoice_no', name: 'invoice_no' },
  { data: 'invoice_date', name: 'invoice_date' },
  { data: 'customer_name', name: 'customer_name' },
  { data: 'customer_contact_number', name: 'customer_contact_number' },
  { data: 'sales_person', name: 'sales_person' },
  { data: 'order_status_dropdown', name: 'order_status', orderable: true, searchable: false },
  { data: 'status_badge', name: 'status', orderable: true, searchable: false },
  { data: 'payment_type_badge', name: 'payment_type', orderable: true, searchable: false },
  { data: 'grand_total', name: 'grand_total' },
  { data: 'paid_amount', name: 'paid_amount' },
  { data: 'balance_due', name: 'balance_due' },
  { data: 'actions_dropdown', name: 'actions', orderable: false, searchable: false },
]

const columnDefs = [
  { targets: [6, 7, 8, 12], render: (data: any) => data },
]

function applyFilters() {
  closeAllDropdowns()
  reloadKey.value = Date.now()
}

function resetFilters() {
  filters.q = ''
  filters.order_status = ''
  filters.payment_type = ''
  applyFilters()
}

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

function showFlash(type: 'success' | 'error', text: string) {
  flashMessage.value = { type, text }
  window.setTimeout(() => {
    if (flashMessage.value?.text === text) {
      flashMessage.value = null
    }
  }, 4000)
}

function closeAllDropdowns(except?: HTMLElement | null) {
  document.querySelectorAll<HTMLElement>('.table-dropdown-menu').forEach((menu) => {
    if (except && menu === except) return
    menu.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none')
    menu.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto')
  })
}

function toggleDropdown(button: HTMLElement) {
  const wrapper = button.closest('.table-dropdown')
  const menu = wrapper?.querySelector<HTMLElement>('.table-dropdown-menu')
  if (!menu) return

  const isOpen = !menu.classList.contains('invisible')
  closeAllDropdowns(menu)

  if (isOpen) {
    menu.classList.add('invisible', 'opacity-0', 'scale-95', 'pointer-events-none')
    menu.classList.remove('visible', 'opacity-100', 'scale-100', 'pointer-events-auto')
    return
  }

  menu.classList.remove('invisible', 'opacity-0', 'scale-95', 'pointer-events-none')
  menu.classList.add('visible', 'opacity-100', 'scale-100', 'pointer-events-auto')
}

async function changeOrderStatus(id: number, status: string) {
  try {
    await axios.patch(route('invoices.order-status', id), {
      order_status: status,
    })

    closeAllDropdowns()
    showFlash('success', 'Order status updated and customer email processed.')
    reloadKey.value = Date.now()
  } catch (error: any) {
    const message =
      error?.response?.data?.message ||
      error?.response?.data?.errors?.tracking_id?.[0] ||
      error?.response?.data?.errors?.delivery_agent?.[0] ||
      'Could not update the order status.'

    closeAllDropdowns()
    showFlash('error', message)
  }
}

function handleOutsideClick(event: MouseEvent) {
  const target = event.target as HTMLElement
  if (!target.closest('.table-dropdown')) {
    closeAllDropdowns()
  }
}

onMounted(() => {
  document.addEventListener('click', handleOutsideClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleOutsideClick)
})

function onTableClick(e: MouseEvent) {
  const target = e.target as HTMLElement

  const toggleBtn = target.closest('button[data-action="toggle-dropdown"]') as HTMLButtonElement | null
  if (toggleBtn) {
    e.preventDefault()
    e.stopPropagation()
    toggleDropdown(toggleBtn)
    return
  }

  const statusBtn = target.closest('button[data-action="change-order-status"]') as HTMLButtonElement | null
  if (statusBtn) {
    e.preventDefault()
    e.stopPropagation()

    const id = Number(statusBtn.dataset.id)
    const status = String(statusBtn.dataset.status || '')
    if (!id || !status) return

    changeOrderStatus(id, status)
    return
  }

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
        closeAllDropdowns()
        showFlash('success', 'Invoice deleted successfully.')
        reloadKey.value = Date.now()
      },
      onError: () => {
        showFlash('error', 'Could not delete the invoice.')
      },
    })
  }
}
</script>
