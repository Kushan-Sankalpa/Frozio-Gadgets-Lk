<script setup lang="ts">
import AppLayout from '@/Backend/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, reactive, watch } from 'vue'
import { route } from 'ziggy-js'

type TechProduct = {
  id: number
  label: string
  model: string
  brand?: string | null
  category?: string | null
  price: number
  warranty?: string | null
  storages: string[]
  colors: string[]
}

type ShoeProduct = {
  id: number
  label: string
  name: string
  brand?: string | null
  category?: string | null
  price: number
  regular_price: number
  sizes: string[]
}

type InvoiceItem = {
  id?: number | null
  item_no: number
  product_type: 'tech' | 'shoe'
  product_id: number | null
  model_name: string
  storage: string
  color: string
  size: string
  imei_serial: string
  warranty: string
  is_preorder: boolean
  description: string
  qty: number
  regular_price: number
  discount_type: '' | 'percentage' | 'fixed'
  discount_value: number | null
  discount_percent_display: number | null
  discounted_unit_price: number
  line_total: number
}

type InvoicePayload = {
  id: number
  invoice_no: string
  invoice_date: string
  customer_name: string
  customer_contact_number: string
  customer_address?: string | null
  customer_email?: string | null
  sales_person?: string | null
  ship_date?: string | null
  ship_via?: string | null
  payment_type?: 'cash' | 'card' | '' | null
  paid_amount: number
  subtotal: number
  total_discount: number
  tax_amount: number
  grand_total: number
  balance_due: number
  notes?: string | null
  terms?: string | null
  status: 'draft' | 'finalized' | 'cancelled'
  pdf_path?: string | null
  pdf_url?: string | null
  items: InvoiceItem[]
}

const props = defineProps<{
  mode: 'create' | 'edit'
  invoice?: InvoicePayload | null
  techProducts: TechProduct[]
  shoeProducts: ShoeProduct[]
  nextInvoiceNo: string
  shop: {
    name: string
    address_lines: string[]
    phone?: string
    website?: string
    logo_url?: string
  }
}>()

const isEdit = computed(() => props.mode === 'edit' && !!props.invoice?.id)

const form = useForm({
  invoice_no: props.invoice?.invoice_no ?? props.nextInvoiceNo,
  invoice_date: props.invoice?.invoice_date ?? new Date().toISOString().slice(0, 10),
  customer_name: props.invoice?.customer_name ?? '',
  customer_contact_number: props.invoice?.customer_contact_number ?? '',
  customer_address: props.invoice?.customer_address ?? '',
  customer_email: props.invoice?.customer_email ?? '',
  sales_person: props.invoice?.sales_person ?? '',
  ship_date: props.invoice?.ship_date ?? '',
  ship_via: props.invoice?.ship_via ?? '',
  payment_type: props.invoice?.payment_type ?? '',
  paid_amount: props.invoice?.paid_amount ?? 0,
  tax_amount: props.invoice?.tax_amount ?? 0,
  notes: props.invoice?.notes ?? '',
  terms: props.invoice?.terms ?? '',
  status: props.invoice?.status ?? 'draft',
  submit_action: 'draft',
  items: (props.invoice?.items ?? []).map((item, index) => ({
    ...item,
    item_no: index + 1,
  })) as InvoiceItem[],
})

const techSearch = reactive({ keyword: '' })
const shoeSearch = reactive({ keyword: '' })

const draftItem = reactive<InvoiceItem>({
  item_no: 1,
  product_type: 'tech',
  product_id: null,
  model_name: '',
  storage: '',
  color: '',
  size: '',
  imei_serial: '',
  warranty: '',
  is_preorder: false,
  description: '',
  qty: 1,
  regular_price: 0,
  discount_type: '',
  discount_value: null,
  discount_percent_display: null,
  discounted_unit_price: 0,
  line_total: 0,
})

const filteredTechProducts = computed(() => {
  const keyword = techSearch.keyword.trim().toLowerCase()
  if (!keyword) return props.techProducts

  return props.techProducts.filter((p) =>
    [p.label, p.brand, p.category].filter(Boolean).join(' ').toLowerCase().includes(keyword)
  )
})

const filteredShoeProducts = computed(() => {
  const keyword = shoeSearch.keyword.trim().toLowerCase()
  if (!keyword) return props.shoeProducts

  return props.shoeProducts.filter((p) =>
    [p.label, p.brand, p.category].filter(Boolean).join(' ').toLowerCase().includes(keyword)
  )
})

const selectedTechProduct = computed(() => {
  if (draftItem.product_type !== 'tech') return null
  return props.techProducts.find((p) => p.id === Number(draftItem.product_id)) ?? null
})

const selectedShoeProduct = computed(() => {
  if (draftItem.product_type !== 'shoe') return null
  return props.shoeProducts.find((p) => p.id === Number(draftItem.product_id)) ?? null
})

const techStorages = computed(() => selectedTechProduct.value?.storages ?? [])
const techColors = computed(() => selectedTechProduct.value?.colors ?? [])
const shoeSizes = computed(() => selectedShoeProduct.value?.sizes ?? [])

function resetDraftItem(type: 'tech' | 'shoe' = 'tech') {
  draftItem.item_no = form.items.length + 1
  draftItem.product_type = type
  draftItem.product_id = null
  draftItem.model_name = ''
  draftItem.storage = ''
  draftItem.color = ''
  draftItem.size = ''
  draftItem.imei_serial = ''
  draftItem.warranty = ''
  draftItem.is_preorder = false
  draftItem.description = ''
  draftItem.qty = 1
  draftItem.regular_price = 0
  draftItem.discount_type = ''
  draftItem.discount_value = null
  draftItem.discount_percent_display = null
  draftItem.discounted_unit_price = 0
  draftItem.line_total = 0
}

function onTypeChange(type: 'tech' | 'shoe') {
  resetDraftItem(type)
}

watch(
  () => draftItem.product_id,
  () => {
    if (draftItem.product_type === 'tech' && selectedTechProduct.value) {
      draftItem.model_name = selectedTechProduct.value.model
      draftItem.regular_price = Number(selectedTechProduct.value.price || 0)
      draftItem.discounted_unit_price = Number(selectedTechProduct.value.price || 0)
      draftItem.warranty = selectedTechProduct.value.warranty ?? ''
      draftItem.storage = ''
      draftItem.color = ''
      draftItem.size = ''
      recalcDraft()
    }

    if (draftItem.product_type === 'shoe' && selectedShoeProduct.value) {
      draftItem.model_name = selectedShoeProduct.value.name
      draftItem.regular_price = Number(selectedShoeProduct.value.price || 0)
      draftItem.discounted_unit_price = Number(selectedShoeProduct.value.price || 0)
      draftItem.storage = ''
      draftItem.color = ''
      draftItem.size = ''
      draftItem.imei_serial = ''
      draftItem.warranty = ''
      draftItem.is_preorder = false
      recalcDraft()
    }
  }
)

watch(
  () => [
    draftItem.product_type,
    draftItem.model_name,
    draftItem.storage,
    draftItem.color,
    draftItem.size,
    draftItem.imei_serial,
    draftItem.warranty,
    draftItem.is_preorder,
    draftItem.qty,
    draftItem.regular_price,
    draftItem.discount_type,
    draftItem.discount_value,
  ],
  () => recalcDraft(),
  { deep: true }
)

function recalcDraft() {
  const regularPrice = Number(draftItem.regular_price || 0)
  const qty = Math.max(1, Number(draftItem.qty || 1))
  const discountType = draftItem.discount_type
  const discountValue = Number(draftItem.discount_value || 0)

  let discountedUnitPrice = regularPrice
  let discountPercentDisplay: number | null = null

  if (discountType === 'percentage') {
    discountedUnitPrice = regularPrice - ((regularPrice * discountValue) / 100)
    discountPercentDisplay = discountValue
  } else if (discountType === 'fixed') {
    discountedUnitPrice = regularPrice - discountValue
    discountPercentDisplay = null
  }

  discountedUnitPrice = Math.max(0, discountedUnitPrice)

  draftItem.discounted_unit_price = Number(discountedUnitPrice.toFixed(2))
  draftItem.discount_percent_display = discountPercentDisplay !== null ? Number(discountPercentDisplay.toFixed(2)) : null
  draftItem.line_total = Number((discountedUnitPrice * qty).toFixed(2))
  draftItem.description = buildDescription(draftItem)
}

function buildDescription(item: InvoiceItem) {
  if (item.product_type === 'tech') {
    const bits = [
      item.model_name,
      item.storage,
      item.color,
    ].filter(Boolean)

    const details = []

    if (item.imei_serial) details.push(`IMEI: ${item.imei_serial}`)
    if (item.warranty) details.push(`Warranty: ${item.warranty}`)
    if (item.is_preorder) details.push('Pre-Order')

    return [...bits, ...details].join(' / ')
  }

  return [item.model_name, item.size].filter(Boolean).join(' / ')
}

function addDraftItem() {
  recalcDraft()

  if (!draftItem.product_id || !draftItem.model_name || !draftItem.description) {
    alert('Please select a product and complete the required item details.')
    return
  }

  form.items.push({
    ...JSON.parse(JSON.stringify(draftItem)),
    item_no: form.items.length + 1,
  })

  syncItemNumbers()
  resetDraftItem(draftItem.product_type)
}

function removeItem(index: number) {
  form.items.splice(index, 1)
  syncItemNumbers()
}

function syncItemNumbers() {
  form.items = form.items.map((item, index) => ({
    ...item,
    item_no: index + 1,
  }))
}

const subtotal = computed(() =>
  form.items.reduce((sum, item) => sum + (Number(item.regular_price || 0) * Number(item.qty || 1)), 0)
)

const totalDiscount = computed(() =>
  Number((subtotal.value - form.items.reduce((sum, item) => sum + Number(item.line_total || 0), 0)).toFixed(2))
)

const grandTotalBeforeTax = computed(() =>
  Number(form.items.reduce((sum, item) => sum + Number(item.line_total || 0), 0).toFixed(2))
)

const grandTotal = computed(() =>
  Number((grandTotalBeforeTax.value + Number(form.tax_amount || 0)).toFixed(2))
)

const balanceDue = computed(() =>
  Number(Math.max(0, grandTotal.value - Number(form.paid_amount || 0)).toFixed(2))
)

function resetForm() {
  if (!confirm('Reset the invoice form?')) return

  form.invoice_no = props.nextInvoiceNo
  form.invoice_date = new Date().toISOString().slice(0, 10)
  form.customer_name = ''
  form.customer_contact_number = ''
  form.customer_address = ''
  form.customer_email = ''
  form.sales_person = ''
  form.ship_date = ''
  form.ship_via = ''
  form.payment_type = ''
  form.paid_amount = 0
  form.tax_amount = 0
  form.notes = ''
  form.terms = ''
  form.status = 'draft'
  form.items = []
  resetDraftItem('tech')
}

function submit(action: 'draft' | 'finalize') {
  form.clearErrors()
  form.submit_action = action

  const payload = {
    ...form.data(),
    paid_amount: Number(form.paid_amount || 0),
    tax_amount: Number(form.tax_amount || 0),
    items: form.items.map((item, index) => ({
      ...item,
      item_no: index + 1,
      description: buildDescription(item),
      discounted_unit_price: Number(item.discounted_unit_price || 0),
      line_total: Number(item.line_total || 0),
      regular_price: Number(item.regular_price || 0),
      qty: Number(item.qty || 1),
      discount_value: item.discount_value !== null && item.discount_value !== undefined ? Number(item.discount_value) : null,
      discount_percent_display: item.discount_percent_display !== null && item.discount_percent_display !== undefined
        ? Number(item.discount_percent_display)
        : null,
    })),
  }

  if (!isEdit.value) {
    form.transform(() => payload).post(route('invoices.store'), {
      preserveScroll: true,
      onFinish: () => form.transform((d) => d),
    })
    return
  }

  form.transform(() => ({
    ...payload,
    _method: 'PUT',
  })).post(route('invoices.update', props.invoice!.id), {
    preserveScroll: true,
    onFinish: () => form.transform((d) => d),
  })
}
</script>

<template>
  <AppLayout>
    <Head :title="isEdit ? 'Update Invoice' : 'Create Invoice'" />

    <div class="space-y-4 p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold">
            {{ isEdit ? 'Update Invoice' : 'Create Invoice' }}
          </h1>
          <p class="text-sm text-neutral-500">
            Build the invoice on the left and preview the PDF layout on the right.
          </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
          <Link
            :href="route('invoices.index')"
            class="inline-flex items-center justify-center rounded-full border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100"
          >
            Back
          </Link>

          <a
            v-if="isEdit && invoice?.pdf_url"
            :href="route('invoices.download', invoice.id)"
            target="_blank"
            class="inline-flex items-center justify-center rounded-full border border-emerald-200 px-4 py-2 text-sm font-medium text-emerald-600 transition hover:bg-emerald-50"
          >
            Download PDF
          </a>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <form
          @submit.prevent
          class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6"
        >
          <div class="rounded-2xl border border-neutral-200 p-4">
            <h2 class="mb-4 text-lg font-semibold text-neutral-800">Invoice Information</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Invoice Number</label>
                <input
                  v-model="form.invoice_no"
                  type="text"
                  readonly
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-2 outline-none"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Date</label>
                <input
                  v-model="form.invoice_date"
                  type="date"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
                <p v-if="form.errors.invoice_date" class="mt-1 text-sm text-red-600">{{ form.errors.invoice_date }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Sales Person</label>
                <input
                  v-model="form.sales_person"
                  type="text"
                  placeholder="Sales person"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Ship Date</label>
                <input
                  v-model="form.ship_date"
                  type="date"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Ship Via</label>
                <input
                  v-model="form.ship_via"
                  type="text"
                  placeholder="Courier / Pickup / Delivery"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Payment Type</label>
                <select
                  v-model="form.payment_type"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option value="">Select payment type</option>
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                </select>
                <p v-if="form.errors.payment_type" class="mt-1 text-sm text-red-600">{{ form.errors.payment_type }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Paid Amount</label>
                <input
                  v-model="form.paid_amount"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Tax Amount</label>
                <input
                  v-model="form.tax_amount"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Status</label>
                <select
                  v-model="form.status"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option value="draft">Draft</option>
                  <option value="finalized">Finalized</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Notes</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Terms / Remarks</label>
                <textarea
                  v-model="form.terms"
                  rows="3"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-neutral-200 p-4">
            <h2 class="mb-4 text-lg font-semibold text-neutral-800">Bill To</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Name</label>
                <input
                  v-model="form.customer_name"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
                <p v-if="form.errors.customer_name" class="mt-1 text-sm text-red-600">{{ form.errors.customer_name }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Contact Number</label>
                <input
                  v-model="form.customer_contact_number"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
                <p v-if="form.errors.customer_contact_number" class="mt-1 text-sm text-red-600">{{ form.errors.customer_contact_number }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Address</label>
                <input
                  v-model="form.customer_address"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Customer Email</label>
                <input
                  v-model="form.customer_email"
                  type="email"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-neutral-200 p-4">
            <div class="mb-4 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-neutral-800">Product Items</h2>

              <div class="flex gap-2">
                <button
                  type="button"
                  @click="onTypeChange('tech')"
                  :class="[
                    'rounded-full px-4 py-2 text-sm font-medium transition',
                    draftItem.product_type === 'tech'
                      ? 'bg-red-500 text-white'
                      : 'border border-neutral-200 text-neutral-700 hover:bg-neutral-100'
                  ]"
                >
                  Tech Product
                </button>

                <button
                  type="button"
                  @click="onTypeChange('shoe')"
                  :class="[
                    'rounded-full px-4 py-2 text-sm font-medium transition',
                    draftItem.product_type === 'shoe'
                      ? 'bg-red-500 text-white'
                      : 'border border-neutral-200 text-neutral-700 hover:bg-neutral-100'
                  ]"
                >
                  Shoe Product
                </button>
              </div>
            </div>

            <div v-if="draftItem.product_type === 'tech'" class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Search Tech Product</label>
                <input
                  v-model="techSearch.keyword"
                  type="text"
                  placeholder="Search model / brand / category"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Model</label>
                <select
                  v-model="draftItem.product_id"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option :value="null">Select tech product</option>
                  <option
                    v-for="product in filteredTechProducts"
                    :key="product.id"
                    :value="product.id"
                  >
                    {{ product.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Storage</label>
                <select
                  v-model="draftItem.storage"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option value="">Select storage</option>
                  <option v-for="storage in techStorages" :key="storage" :value="storage">
                    {{ storage }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Color</label>
                <select
                  v-model="draftItem.color"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option value="">Select color</option>
                  <option v-for="color in techColors" :key="color" :value="color">
                    {{ color }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">IMEI / Serial</label>
                <input
                  v-model="draftItem.imei_serial"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Warranty</label>
                <input
                  v-model="draftItem.warranty"
                  type="text"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div class="flex items-center gap-3">
                <input
                  id="preorder"
                  v-model="draftItem.is_preorder"
                  type="checkbox"
                  class="h-4 w-4 rounded border-neutral-300 text-red-500 focus:ring-red-500"
                />
                <label for="preorder" class="text-sm font-medium text-neutral-700">Pre-Order</label>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Qty</label>
                <input
                  v-model="draftItem.qty"
                  type="number"
                  min="1"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Regular Price</label>
                <input
                  v-model="draftItem.regular_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discount Type</label>
                <select
                  v-model="draftItem.discount_type"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option value="">No discount</option>
                  <option value="percentage">Percentage</option>
                  <option value="fixed">Fixed Amount</option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discount Value</label>
                <input
                  v-model="draftItem.discount_value"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discounted Price</label>
                <input
                  :value="draftItem.discounted_unit_price"
                  type="number"
                  readonly
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-2 outline-none"
                />
              </div>
            </div>

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Search Shoe Product</label>
                <input
                  v-model="shoeSearch.keyword"
                  type="text"
                  placeholder="Search shoe product"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Shoe Product</label>
                <select
                  v-model="draftItem.product_id"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option :value="null">Select shoe product</option>
                  <option
                    v-for="product in filteredShoeProducts"
                    :key="product.id"
                    :value="product.id"
                  >
                    {{ product.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Size</label>
                <select
                  v-model="draftItem.size"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option value="">Select size</option>
                  <option v-for="size in shoeSizes" :key="size" :value="size">
                    {{ size }}
                  </option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Color</label>
                <input
                  v-model="draftItem.color"
                  type="text"
                  placeholder="Optional color"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Qty</label>
                <input
                  v-model="draftItem.qty"
                  type="number"
                  min="1"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Regular Price</label>
                <input
                  v-model="draftItem.regular_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discount Type</label>
                <select
                  v-model="draftItem.discount_type"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                >
                  <option value="">No discount</option>
                  <option value="percentage">Percentage</option>
                  <option value="fixed">Fixed Amount</option>
                </select>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discount Value</label>
                <input
                  v-model="draftItem.discount_value"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full rounded-xl border border-neutral-200 px-4 py-2 outline-none focus:border-red-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-neutral-700">Discounted Price</label>
                <input
                  :value="draftItem.discounted_unit_price"
                  type="number"
                  readonly
                  class="w-full rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-2 outline-none"
                />
              </div>
            </div>

            <div class="mt-4 flex justify-end">
              <button
                type="button"
                @click="addDraftItem"
                class="rounded-full bg-red-500 px-5 py-2 text-sm font-medium text-white transition hover:bg-red-600"
              >
                Add This Product
              </button>
            </div>

            <div class="mt-6 overflow-x-auto" v-if="form.items.length">
              <table class="min-w-full border-collapse text-sm">
                <thead>
                  <tr class="bg-neutral-50">
                    <th class="border border-neutral-200 px-3 py-2 text-left">Item</th>
                    <th class="border border-neutral-200 px-3 py-2 text-left">Description</th>
                    <th class="border border-neutral-200 px-3 py-2 text-center">Qty</th>
                    <th class="border border-neutral-200 px-3 py-2 text-right">Unit Price</th>
                    <th class="border border-neutral-200 px-3 py-2 text-center">%</th>
                    <th class="border border-neutral-200 px-3 py-2 text-right">Total</th>
                    <th class="border border-neutral-200 px-3 py-2 text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in form.items" :key="`${item.product_type}-${index}`">
                    <td class="border border-neutral-200 px-3 py-2">{{ index + 1 }}</td>
                    <td class="border border-neutral-200 px-3 py-2">{{ item.description }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-center">{{ item.qty }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-right">{{ Number(item.regular_price).toFixed(2) }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-center">
                      {{ item.discount_type === 'percentage' ? (item.discount_percent_display ?? item.discount_value ?? 0) : '-' }}
                    </td>
                    <td class="border border-neutral-200 px-3 py-2 text-right">{{ Number(item.line_total).toFixed(2) }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-center">
                      <button
                        type="button"
                        @click="removeItem(index)"
                        class="rounded-full border border-red-200 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50"
                      >
                        Remove
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              <p v-if="form.errors.items" class="mt-2 text-sm text-red-600">{{ form.errors.items }}</p>
            </div>
          </div>

          <div class="rounded-2xl border border-neutral-200 p-4">
            <h2 class="mb-4 text-lg font-semibold text-neutral-800">Summary</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="rounded-xl bg-neutral-50 px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Subtotal</div>
                <div class="text-lg font-semibold text-neutral-800">{{ subtotal.toFixed(2) }}</div>
              </div>
              <div class="rounded-xl bg-neutral-50 px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Total Discount</div>
                <div class="text-lg font-semibold text-neutral-800">{{ totalDiscount.toFixed(2) }}</div>
              </div>
              <div class="rounded-xl bg-neutral-50 px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Grand Total</div>
                <div class="text-lg font-semibold text-neutral-800">{{ grandTotal.toFixed(2) }}</div>
              </div>
              <div class="rounded-xl bg-neutral-50 px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-neutral-500">Balance Due</div>
                <div class="text-lg font-semibold text-neutral-800">{{ balanceDue.toFixed(2) }}</div>
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button
              type="button"
              @click="resetForm"
              class="inline-flex items-center justify-center rounded-full border border-neutral-200 px-5 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-100"
            >
              Reset
            </button>

            <button
              type="button"
              @click="submit('draft')"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-full border border-yellow-300 px-5 py-2 text-sm font-medium text-yellow-700 transition hover:bg-yellow-50 disabled:opacity-50"
            >
              {{ form.processing ? 'Saving...' : 'Save Draft' }}
            </button>

            <button
              type="button"
              @click="submit('finalize')"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-full bg-red-500 px-6 py-2 text-sm font-medium text-white transition hover:bg-red-600 disabled:opacity-50"
            >
              {{ form.processing ? 'Generating...' : 'Create Invoice PDF' }}
            </button>
          </div>
        </form>

        <div class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-neutral-800">Live Invoice Preview</h2>
            <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-600">
              PDF Layout Preview
            </span>
          </div>

          <div class="mx-auto max-w-[800px] rounded-xl border border-neutral-200 bg-white p-6 text-sm text-neutral-800">
            <div class="grid grid-cols-3 gap-4 border-b border-neutral-200 pb-6">
              <div class="space-y-1 text-sm">
                <div v-for="line in shop.address_lines" :key="line">{{ line }}</div>
                <div v-if="shop.phone">Phone: {{ shop.phone }}</div>
              </div>

              <div class="flex items-start justify-center">
                <img
                  v-if="shop.logo_url"
                  :src="shop.logo_url"
                  alt="Logo"
                  class="max-h-20 max-w-[140px] object-contain"
                />
              </div>

              <div class="space-y-1 text-right">
                <div class="text-2xl font-bold tracking-wide">INVOICE</div>
                <div><span class="text-neutral-500">Date:</span> {{ form.invoice_date || '-' }}</div>
                <div><span class="text-neutral-500">Invoice No:</span> {{ form.invoice_no }}</div>
                <div><span class="text-neutral-500">Payment:</span> {{ form.payment_type || '-' }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 border-b border-neutral-200 py-5 md:grid-cols-2">
              <div>
                <div class="mb-2 font-semibold">Bill To</div>
                <div><span class="text-neutral-500">Name:</span> {{ form.customer_name || '-' }}</div>
                <div><span class="text-neutral-500">Contact:</span> {{ form.customer_contact_number || '-' }}</div>
                <div v-if="form.customer_address"><span class="text-neutral-500">Address:</span> {{ form.customer_address }}</div>
                <div v-if="form.customer_email"><span class="text-neutral-500">Email:</span> {{ form.customer_email }}</div>
              </div>

              <div class="md:text-right">
                <div v-if="form.sales_person"><span class="text-neutral-500">Sales Person:</span> {{ form.sales_person }}</div>
                <div v-if="form.ship_date"><span class="text-neutral-500">Ship Date:</span> {{ form.ship_date }}</div>
                <div v-if="form.ship_via"><span class="text-neutral-500">Ship Via:</span> {{ form.ship_via }}</div>
              </div>
            </div>

            <div class="overflow-x-auto py-5">
              <table class="min-w-full border-collapse text-sm">
                <thead>
                  <tr>
                    <th class="border border-neutral-200 bg-neutral-50 px-3 py-2 text-left">ITEM</th>
                    <th class="border border-neutral-200 bg-neutral-50 px-3 py-2 text-left">DESCRIPTION</th>
                    <th class="border border-neutral-200 bg-neutral-50 px-3 py-2 text-center">QTY</th>
                    <th class="border border-neutral-200 bg-neutral-50 px-3 py-2 text-right">UNIT PRICE</th>
                    <th class="border border-neutral-200 bg-neutral-50 px-3 py-2 text-center">%</th>
                    <th class="border border-neutral-200 bg-neutral-50 px-3 py-2 text-right">TOTAL</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!form.items.length">
                    <td colspan="6" class="border border-neutral-200 px-3 py-8 text-center text-neutral-400">
                      No invoice items added yet.
                    </td>
                  </tr>

                  <tr v-for="(item, index) in form.items" :key="`preview-${index}`">
                    <td class="border border-neutral-200 px-3 py-2">{{ index + 1 }}</td>
                    <td class="border border-neutral-200 px-3 py-2">{{ item.description }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-center">{{ item.qty }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-right">{{ Number(item.regular_price).toFixed(2) }}</td>
                    <td class="border border-neutral-200 px-3 py-2 text-center">
                      {{ item.discount_type === 'percentage' ? (item.discount_percent_display ?? item.discount_value ?? 0) : '-' }}
                    </td>
                    <td class="border border-neutral-200 px-3 py-2 text-right">{{ Number(item.line_total).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="ml-auto mt-2 w-full max-w-sm space-y-2">
              <div class="flex items-center justify-between border-b border-neutral-200 py-2">
                <span class="text-neutral-500">Subtotal</span>
                <span class="font-medium">{{ subtotal.toFixed(2) }}</span>
              </div>
              <div class="flex items-center justify-between border-b border-neutral-200 py-2">
                <span class="text-neutral-500">Total Discount</span>
                <span class="font-medium">{{ totalDiscount.toFixed(2) }}</span>
              </div>
              <div class="flex items-center justify-between border-b border-neutral-200 py-2">
                <span class="text-neutral-500">Tax</span>
                <span class="font-medium">{{ Number(form.tax_amount || 0).toFixed(2) }}</span>
              </div>
              <div class="flex items-center justify-between border-b border-neutral-200 py-2">
                <span class="text-neutral-500">Grand Total</span>
                <span class="font-semibold">{{ grandTotal.toFixed(2) }}</span>
              </div>
              <div class="flex items-center justify-between border-b border-neutral-200 py-2">
                <span class="text-neutral-500">Paid</span>
                <span class="font-medium">{{ Number(form.paid_amount || 0).toFixed(2) }}</span>
              </div>
              <div class="flex items-center justify-between py-2 text-base font-bold">
                <span>Balance Due</span>
                <span>{{ balanceDue.toFixed(2) }}</span>
              </div>
            </div>

            <div v-if="form.notes" class="mt-6 border-t border-neutral-200 pt-4">
              <div class="mb-1 font-semibold">Notes</div>
              <div class="text-neutral-700">{{ form.notes }}</div>
            </div>

            <div v-if="form.terms" class="mt-4">
              <div class="mb-1 font-semibold">Terms / Remarks</div>
              <div class="text-neutral-700">{{ form.terms }}</div>
            </div>

            <div class="mt-8 border-t border-neutral-200 pt-4 text-center text-xs text-neutral-500">
              {{ shop.website || 'www.froziohub.com' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>