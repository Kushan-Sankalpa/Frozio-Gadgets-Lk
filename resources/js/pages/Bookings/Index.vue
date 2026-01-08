<template>
    <div>

        <Head title="Bookings" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="container mx-auto p-4">
                <div
                    class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                    <div
                        class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700/60">
                        <div>
                            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Bookings
                            </h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Your bookings list will appear here.
                            </p>
                        </div>
                    </div>

                    <div class="m-4 px-4">
                        <!-- FILTERS (custom server-side params, not datatable column filters) -->
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="md:col-span-4">
                                <SelectInputComponent id="status" label="Status" v-model="draftFilters.status"
                                    :options="statusOptions" />

                            </div>

                            <div class="md:col-span-3">
                                <label class="mb-1 block text-xs text-zinc-500">Date from</label>
                                <input v-model="draftFilters.date_from" type="date"
                                    class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" />
                            </div>

                            <div class="md:col-span-3">
                                <label class="mb-1 block text-xs text-zinc-500">Date to</label>
                                <input v-model="draftFilters.date_to" type="date"
                                    class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" />
                            </div>

                            <!-- <div>
                                <label class="mb-1 block text-xs text-zinc-500">Staff ID</label>
                                <input
                                    v-model="draftFilters.staff_id"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                                    placeholder="e.g. 12"
                                />
                            </div> -->

                            <!-- <div>
                                <label class="mb-1 block text-xs text-zinc-500">Search</label>
                                <input
                                    v-model="draftFilters.q"
                                    type="text"
                                    class="w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                                    placeholder="Client / Staff / Booking ID"
                                    @keydown.enter.prevent="applyFilters"
                                />
                            </div> -->

                            <div class="md:col-span-5 flex flex-wrap gap-2 mb-4">
                                <button @click="applyFilters"
                                    class="cursor-pointer rounded-lg bg-black px-4 py-2 text-sm text-white">
                                    Apply
                                </button>
                                <button @click="resetFilters"
                                    class="cursor-pointer rounded-lg border border-zinc-200 px-4 py-2 text-sm dark:border-zinc-700">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- DATATABLE -->
                        <DataTable :key="tableKey" id="bookingsTable" :url="dataUrl" :columns="columns"
                            :columnDefs="columnDefs" :order="[[0, 'desc'], [1, 'desc']]">
                            <template #header>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Client</th>
                                    <th>Services</th>

                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>

            <!-- OFFCANVAS BACKDROP -->
            <div v-if="showCanvas" class="fixed inset-0 z-[9999] flex justify-end bg-black/40" @click.self="closeCanvas"
                :class="animating === 'out' ? 'animate-fadeOut' : 'animate-fadeIn'">
                <!-- SKELETON -->
                <div v-if="loadingCanvas"
                    class="flex h-full w-full max-w-[1100px] flex-col gap-6 bg-white p-6 md:flex-row dark:bg-zinc-900">
                    <div class="w-full space-y-4 md:w-[260px]">
                        <div class="mt-8 space-y-2">
                            <div class="skeleton h-3 w-24"></div>
                            <div class="skeleton h-4 w-40"></div>
                            <div class="skeleton h-3 w-32"></div>
                        </div>

                        <div class="space-y-2">
                            <div class="skeleton h-3 w-24"></div>
                            <div class="skeleton h-4 w-40"></div>
                        </div>

                        <div class="space-y-2">
                            <div class="skeleton h-3 w-24"></div>
                            <div class="skeleton h-4 w-40"></div>
                        </div>

                        <hr class="my-4" />

                        <div class="space-y-2">
                            <div class="skeleton h-8 w-full"></div>
                            <div class="skeleton h-8 w-full"></div>
                            <div class="skeleton h-8 w-full"></div>
                            <div class="skeleton h-8 w-full"></div>
                        </div>
                    </div>

                    <div class="flex-1 space-y-4 overflow-y-auto">
                        <div class="skeleton h-6 w-32"></div>
                        <div class="skeleton h-24 w-full"></div>
                        <div class="skeleton h-24 w-full"></div>
                        <div class="skeleton h-24 w-full"></div>
                    </div>
                </div>

                <!-- REAL CONTENT -->
                <div v-else
                    class="relative flex h-full w-full max-w-[1100px] flex-col bg-white shadow-2xl md:flex-row dark:bg-zinc-900"
                    :class="animating === 'in'
                            ? 'animate-slideIn'
                            : animating === 'out'
                                ? 'animate-slideOut'
                                : ''
                        ">
                    <!-- CLOSE BUTTON -->
                    <button @click="closeCanvas"
                        class="absolute top-4 left-4 z-[99999] flex h-10 w-10 cursor-pointer items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-100 hover:text-black dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <i class="bx bx-x text-2xl"></i>
                    </button>

                    <!-- LEFT SUMMARY COLUMN -->
                    <div
                        class="flex w-full flex-col gap-6 overflow-y-auto border-r border-zinc-200 px-6 pt-16 pb-6 md:w-[260px] dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 uppercase">Date &amp; Time</div>
                        <div class="space-y-1 text-sm">
                            <p>{{ dateLabel }}</p>
                            <p>{{ timeRangeLabel }}</p>
                        </div>

                        <div>
                            <p class="mb-1 text-xs text-zinc-500 uppercase">Status</p>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="statusChipClass">
                                {{ statusLabel }}
                            </span>
                            <p v-if="statusChangedByName" class="mt-1 text-[11px] text-zinc-500">
                                Changed by {{ statusChangedByName }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <p class="mb-1 text-xs text-zinc-500 uppercase">Client</p>
                                <p class="text-sm font-medium">{{ clientName }}</p>
                                <p class="text-xs text-zinc-500">{{ clientPhoneLabel }}</p>
                            </div>

                           <div>
  <p class="mb-1 text-xs text-zinc-500 uppercase">Staff members</p>

  <div class="text-sm">
    <template v-if="staffMembers.length === 0">
      Unassigned
    </template>

    <template v-else-if="staffMembers.length === 1">
      {{ staffMembers[0] }}
    </template>

    <ul v-else class="list-disc pl-4 space-y-1">
      <li v-for="n in staffMembers" :key="n">{{ n }}</li>
    </ul>
  </div>
</div>

                        </div>

                        <div>
                            <p class="mb-1 text-xs text-zinc-500 uppercase">Total</p>
                            <p class="text-lg font-semibold">
                                {{ currencySymbol }} {{ formatAmount(totalAmount) }}
                            </p>
                        </div>

                        <hr class="my-1" />

                        <!-- TABS -->
                                     <div class="flex gap-2 overflow-x-auto pb-2 md:flex-col md:gap-0 md:overflow-x-visible">
                <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
                    class="flex-shrink-0 cursor-pointer rounded-lg px-3 py-2 text-sm transition md:w-full md:text-left"
                    :class="activeTab === tab
                            ? 'bg-[#ff2000]/10 font-semibold text-[#ff2000]'
                            : 'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800'
                        ">
                    {{ tab }}
                </button>
            </div>
                    </div>

                    <!-- RIGHT CONTENT COLUMN -->
                    <div class="flex-1 overflow-y-auto bg-zinc-50 pb-6 dark:bg-zinc-800">
                        <div
                            class="sticky top-0 z-[10] mb-0 flex items-center justify-between bg-zinc-50 px-4 pt-4 pb-3 dark:bg-zinc-800">
                            <h3 class="text-lg font-semibold md:text-xl">{{ activeTab }}</h3>
                        </div>

                        <!-- OVERVIEW TAB -->
                        <section v-show="activeTab === 'Overview'" class="space-y-4 px-4 md:space-y-6 md:px-6">
                            <div
                                class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm md:rounded-2xl md:p-5 dark:border-zinc-700 dark:bg-zinc-900">
                                <h4 class="mb-3 text-sm font-semibold">Summary</h4>
                                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Booking ID</p>
                                        <p class="mt-1">{{ bookingSummary.booking_id || '#' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Created</p>
                                        <p class="mt-1">{{ createdAtLabel }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Placed by</p>
                                        <p class="mt-1">{{ placedByName }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Status</p>
                                        <p class="mt-1">{{ statusLabel }}</p>
                                    </div>
<div
  v-if="['cancel','cancelled'].includes((selectedBooking.status || '').toString())"
  class="sm:col-span-1 lg:col-span-2 lg:col-start-2"
>
  <p class="text-xs text-zinc-500 uppercase">Cancellation reason</p>
  <p class="mt-1 text-sm text-zinc-900 dark:text-zinc-100 break-words">
    {{ selectedBooking.cancel_reson || '—' }}
  </p>
</div>

                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Status changed by</p>
                                        <p class="mt-1">{{ statusChangedByName || '-' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Total services</p>
                                        <p class="mt-1">{{ totalServicesCount }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Total paid</p>
                                        <p class="mt-1">
                                            {{ currencySymbol }} {{ formatAmount(bookingSummary.total_paid || 0) }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Remaining</p>
                                        <p class="mt-1">
                                            {{ currencySymbol }} {{ formatAmount(bookingSummary.remaining || 0) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                                <h4 class="mb-4 text-sm font-semibold">Client</h4>
                                <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Name</p>
                                        <p class="mt-1">{{ clientName }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Phone</p>
                                        <p class="mt-1">{{ clientPhoneLabel }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Email</p>
                                        <p class="mt-1">{{ clientEmailLabel }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- SERVICES TAB -->
                        <section v-show="activeTab === 'Services'" class="space-y-4 px-6">
                            <div v-if="!selectedBooking.services || !selectedBooking.services.length"
                                class="py-6 text-center text-sm text-zinc-500">
                                No services added to this booking.
                            </div>

                            <div v-else v-for="svc in selectedBooking.services" :key="svc.id"
                                class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                                <div class="flex justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ svc.label }}
                                        </p>
                                        <p class="mt-1 text-xs text-zinc-500">
                                            {{ serviceTimeRange(svc) }} · {{ svc.duration_minutes }} min
                                            <span v-if="svc.extra_minutes" class="text-[11px]">
                                                (+{{ svc.extra_minutes }} min)
                                            </span>
                                        </p>
                                        <p class="mt-1 text-xs text-zinc-500">
  Staff: {{ serviceStaffName(svc) }}
</p>
                                    </div>

                                    <div class="text-right text-sm font-semibold">
                                        {{ currencySymbol }}
                                        {{ formatAmount(svc.final_price || svc.price || 0) }}
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- PAYMENTS TAB -->
                        <section v-show="activeTab === 'Payments'" class="space-y-4 px-4 md:px-6">
                            <div
                                class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm md:rounded-2xl md:p-5 dark:border-zinc-700 dark:bg-zinc-900">
                                <h4 class="mb-3 text-sm font-semibold">Payment summary</h4>
                                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Total</p>
                                        <p class="mt-1">
                                            {{ currencySymbol }}
                                            {{ formatAmount(bookingSummary.total_price || 0) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Paid</p>
                                        <p class="mt-1">
                                            {{ currencySymbol }}
                                            {{ formatAmount(bookingSummary.total_paid || 0) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-zinc-500 uppercase">Remaining</p>
                                        <p class="mt-1">
                                            {{ currencySymbol }}
                                            {{ formatAmount(bookingSummary.remaining || 0) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!bookingSales.length" class="py-6 text-center text-sm text-zinc-500">
                                No payments recorded for this booking yet.
                            </div>

                            <div v-else class="space-y-3 md:space-y-4">
                                <div v-for="sale in bookingSales" :key="sale.id"
                                    class="rounded-lg border border-zinc-200 bg-white p-3 shadow-sm md:rounded-xl md:p-4 dark:border-zinc-700 dark:bg-zinc-900">
                                    <div class="flex justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold truncate">
                                                {{ sale.payment_method || 'Payment' }}
                                            </p>
                                            <p class="mt-1 text-xs text-zinc-500">
                                                {{ formatDateTime(sale.created_at) }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 text-right text-sm font-semibold">
                                            {{ currencySymbol }} {{ formatAmount(sale.total_paid || 0) }}
                                        </div>
                                    </div>

                                    <p v-if="sale.remaining !== undefined" class="mt-1 text-xs text-zinc-500 break-words">
                                        Remaining after this sale:
                                        {{ currencySymbol }} {{ formatAmount(sale.remaining) }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- NOTES TAB -->
                        <section v-show="activeTab === 'Notes'" class="px-6">
                            <div
                                class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm md:rounded-2xl md:p-5 dark:border-zinc-700 dark:bg-zinc-900">
                                <h4 class="mb-4 text-sm font-semibold">Notes</h4>
                                <p class="text-sm whitespace-pre-line break-words text-zinc-700 dark:text-zinc-300">
                                    {{ selectedBooking.notes ? selectedBooking.notes : 'No notes for this booking.' }}
                                </p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </AppLayout>
    </div>
</template>

<script>
import DataTable from '@/components/Datatable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { route } from 'ziggy-js';
import SelectInputComponent from '@/components/SelectInputComponent.vue';

export default {
    components: { AppLayout, DataTable, Head, SelectInputComponent },

    data() {
        return {
            statusOptions: [
                { id: '', name: 'All' },
                { id: 'scheduled', name: 'Scheduled' },
                { id: 'arrived', name: 'Arrived' },
                { id: 'started', name: 'Started' },
                { id: 'completed', name: 'Completed' },
                { id: 'payment_pending', name: 'Payment Pending' },
                { id: 'no_show', name: 'No Show' },
                { id: 'cancel', name: 'Cancelled' },
                { id: 'rejected', name: 'Rejected' },
                { id: 'blocked_time', name: 'Blocked Time' },
            ],
            breadcrumbs: [{ title: 'Bookings', href: route('bookings.index') }],

            // FILTERS
            draftFilters: {
                status: '',
                date_from: '',
                date_to: '',
                staff_id: '',
                q: '',
                // branch_id: '', // uncomment if you add branch filter in UI + controller
            },
            appliedFilters: {
                status: '',
                date_from: '',
                date_to: '',
                staff_id: '',
                q: '',
                // branch_id: '',
            },

            tableKey: 1,

            // OFFCANVAS
            showCanvas: false,
            loadingCanvas: false,
            animating: false,

            selectedBooking: {},
            bookingSummary: {},
            bookingSales: [],
            latestSale: null,
            currencySymbol: 'LKR',

            activeTab: 'Overview',
            tabs: ['Overview', 'Services', 'Payments', 'Notes'],

            // DATATABLE CONFIG
            columns: [
                { data: 'date', name: 'date' },
                { data: 'time', name: 'time' },
                { data: 'client_name', name: 'client_name' },
                { data: 'services_count', name: 'services_count' },
                { data: 'total_price', name: 'total_price' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],

            columnDefs: [
                { targets: 0, className: 'whitespace-nowrap' },
                { targets: 1, className: 'whitespace-nowrap' },
                {
  targets: 3,
  className: 'whitespace-nowrap text-center',
  render: (d) => {
    const n = parseInt(d || 0, 10);
    return `<span class="font-semibold">${isNaN(n) ? 0 : n}</span>`;
  },
},

                {
                
                    targets: 4,
                    className: 'whitespace-nowrap',
                    render: (d) => {
                        const amount = parseFloat(d || 0);
                        return `LKR ${amount.toFixed(2)}`;
                    },
                },
                {
                    targets: 5,
                    className: 'whitespace-nowrap',
                    render: (status) => {
                        const s = (status || 'scheduled').toString();
                        const label = s.replace('_', ' ');
                        let cls = 'bg-green-100 text-green-700';

                        switch (s) {
                            case 'cancel':
                            case 'cancelled':
                            case 'rejected':
                                cls = 'bg-red-100 text-red-700';
                                break;
                            case 'no_show':
                                cls = 'bg-orange-100 text-orange-700';
                                break;
                            case 'payment_pending':
                            case 'pending_payment':
                            case 'pending':
                                cls = 'bg-yellow-100 text-yellow-700';
                                break;
                            case 'blocked_time':
                                cls = 'bg-zinc-200 text-zinc-700';
                                break;
                            case 'scheduled':
                            case 'arrived':
                            case 'started':
                                cls = 'bg-blue-100 text-blue-700';
                                break;
                            case 'completed':
                                cls = 'bg-green-100 text-green-700';
                                break;
                        }

                        return `<span class="px-2 py-1 rounded-full text-xs font-medium ${cls}">${label}</span>`;
                    },
                },
                {
                    targets: 6,
                    className: 'whitespace-nowrap',
                    render: (_, __, r) => {
                        return `
                            <button data-id="${r.id}" class="tw-btn tw-btn-view" title="View Booking">
                                <i class="bx bx-show"></i>
                            </button>
                        `;
                    },
                },
            ],
        };
    },

    computed: {
        dataUrl() {
            const p = { ...this.appliedFilters };
            Object.keys(p).forEach((k) => {
                if (p[k] === null || p[k] === undefined || String(p[k]).trim() === '') {
                    delete p[k];
                }
            });
            return route('bookings.getdata', p);
        },

        placedByName() {
            const b = this.selectedBooking || {};
            const p = b.placed_by_user || null;
            if (p && p.name) return p.name;
            if (b.placed_by_name) return b.placed_by_name;
            return '-';
        },

        statusChangedByName() {
            const b = this.selectedBooking || {};
            const sc = b.status_changed_by || null;
            if (sc && sc.name) return sc.name;

            const s = (b.status || '').toString();

            if (['cancel', 'cancelled'].includes(s)) {
                if (b.cancelled_by_user?.name) return b.cancelled_by_user.name;
                if (b.approved_by_user?.name) return b.approved_by_user.name;
                if (b.placed_by_user?.name) return b.placed_by_user.name;
                return null;
            }

            if (s === 'rejected') {
                if (b.rejected_by_user?.name) return b.rejected_by_user.name;
                if (b.approved_by_user?.name) return b.approved_by_user.name;
                if (b.placed_by_user?.name) return b.placed_by_user.name;
                return null;
            }

            if (s === 'blocked_time') {
                if (b.blocked_by_user?.name) return b.blocked_by_user.name;
                if (b.placed_by_user?.name) return b.placed_by_user.name;
                return null;
            }

            if (
                ['scheduled', 'arrived', 'started', 'completed', 'no_show', 'payment_pending', 'pending', 'pending_payment'].includes(
                    s,
                )
            ) {
                if (b.approved_by_user?.name) return b.approved_by_user.name;
                if (b.placed_by_user?.name) return b.placed_by_user.name;
                return null;
            }

            if (b.approved_by_user?.name) return b.approved_by_user.name;
            if (b.placed_by_user?.name) return b.placed_by_user.name;
            return null;
        },

        dateLabel() {
            return this.selectedBooking.date || '-';
        },

        timeRangeLabel() {
            const start = this.selectedBooking.starts_at ? this.formatTime(this.selectedBooking.starts_at) : null;
            const end = this.selectedBooking.ends_at ? this.formatTime(this.selectedBooking.ends_at) : null;

            if (!start && !end) return '-';
            if (start && !end) return start;
            return `${start} – ${end}`;
        },
        staffMembers() {
  const b = this.selectedBooking || {};
  const svcs = Array.isArray(b.services) ? b.services : [];

  // take staff from each service
  const names = svcs
    .map((s) => (s && s.staff && s.staff.name ? String(s.staff.name).trim() : ''))
    .filter(Boolean);

  const unique = Array.from(new Set(names));

  // fallback to booking.staff if services have no staff
  if (unique.length === 0) {
    const fallback = b.staff && b.staff.name ? [String(b.staff.name).trim()] : [];
    return fallback.filter(Boolean);
  }

  return unique;
},


        statusLabel() {
            const s = this.selectedBooking.status || 'scheduled';
            return s.replace('_', ' ');
        },

        statusChipClass() {
            const s = this.selectedBooking.status || 'scheduled';
            switch (s) {
                case 'cancel':
                case 'cancelled':
                case 'rejected':
                    return 'bg-red-100 text-red-700';
                case 'no_show':
                    return 'bg-orange-100 text-orange-700';
                case 'payment_pending':
                case 'pending_payment':
                case 'pending':
                    return 'bg-yellow-100 text-yellow-700';
                case 'blocked_time':
                    return 'bg-zinc-200 text-zinc-700';
                case 'completed':
                    return 'bg-green-100 text-green-700';
                default:
                    return 'bg-blue-100 text-blue-700';
            }
        },

        clientName() {
            const b = this.selectedBooking || {};
            const c = b.client || null;

            if (b.client_name && b.client_name.toLowerCase() !== 'walk-in') return b.client_name;

            if (c) {
                const first = c.first_name || c.firstname || c.fname || '';
                const last = c.last_name || c.lastname || c.lname || '';
                const combined = [first, last].filter(Boolean).join(' ').trim();
                if (combined) return combined;

                if (c.client_name && c.client_name.toLowerCase() !== 'walk-in') return c.client_name;
                if (c.name && c.name.toLowerCase() !== 'walk-in') return c.name;
                if (c.full_name && c.full_name.toLowerCase() !== 'walk-in') return c.full_name;
            }

            const alt = b.client_full_name || b.clientName || b.name;
            if (alt && alt.toLowerCase() !== 'walk-in') return alt;

            return 'Walk-in';
        },

        clientPhoneLabel() {
            const b = this.selectedBooking || {};
            const c = b.client || null;
            return (c && (c.phone || c.mobile || c.telephone)) || b.client_phone || '-';
        },

        clientEmailLabel() {
            const b = this.selectedBooking || {};
            const c = b.client || null;
            return (c && (c.email || c.email_address)) || b.client_email || '-';
        },

        staffName() {
            const s = this.selectedBooking.staff || null;
            return (s && s.name) || 'Unassigned';
        },

        totalAmount() {
            return this.selectedBooking.total_price || 0;
        },

        totalServicesCount() {
            return Array.isArray(this.selectedBooking.services) ? this.selectedBooking.services.length : 0;
        },

        createdAtLabel() {
            return this.selectedBooking.created_at ? this.formatDateTime(this.selectedBooking.created_at) : '-';
        },
    },

    mounted() {
        const $ = window.jQuery;
        if (!$) return;

    // Initial attachment of event listener
    this.attachViewButtonListeners();

        // action button click (datatable renders this HTML)
        $('#bookingsTable').on('click', 'button.tw-btn.tw-btn-view', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            if (id) this.openCanvas(id);
        });
            $(document).on('draw.dt', '#bookingsTable', () => {
        this.attachViewButtonListeners();
    });
    },

    methods: {
        // IMPORTANT: re-render the DataTable when url changes
        applyFilters() {
            this.appliedFilters = { ...this.draftFilters };
            this.tableKey += 1;

            this.$nextTick(() => {
            this.attachViewButtonListeners();
        });
        },

        resetFilters() {
            this.draftFilters = { status: '', date_from: '', date_to: '', staff_id: '', q: '' };
            this.appliedFilters = { ...this.draftFilters };
            this.tableKey += 1;

            this.$nextTick(() => {
            this.attachViewButtonListeners();
        });
        },

            // Helper method to attach click listeners to view buttons
    attachViewButtonListeners() {
        const $ = window.jQuery;
        if (!$) return;
        
        // Remove existing listeners first to avoid duplicates
        $('#bookingsTable').off('click', 'button.tw-btn.tw-btn-view');
        
        // Attach new listener
        $('#bookingsTable').on('click', 'button.tw-btn.tw-btn-view', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            if (id) this.openCanvas(id);
        });
    },

        formatAmount(value) {
            const num = parseFloat(value || 0);
            if (isNaN(num)) return '0.00';
            return num.toFixed(2);
        },

        serviceStaffName(svc) {
  if (svc && svc.staff && svc.staff.name) return svc.staff.name;
  if (this.selectedBooking && this.selectedBooking.staff && this.selectedBooking.staff.name) {
    return this.selectedBooking.staff.name; // fallback
  }
  return 'Unassigned';
},


        formatTime(dateString) {
            const d = dateString ? new Date(dateString) : null;
            if (!d || isNaN(d.getTime())) return '-';
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        formatDateTime(dateString) {
            const d = dateString ? new Date(dateString) : null;
            if (!d || isNaN(d.getTime())) return '-';
            return d.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        serviceTimeRange(svc) {
            const start = svc.starts_at ? this.formatTime(svc.starts_at) : null;
            const end = svc.ends_at ? this.formatTime(svc.ends_at) : null;
            if (!start && !end) return '-';
            if (start && !end) return start;
            return `${start} – ${end}`;
        },

        async openCanvas(id) {
            this.showCanvas = true;
            this.animating = 'in';
            this.loadingCanvas = true;

            try {
                const res = await axios.get(route('bookings.details', id));
                this.selectedBooking = res.data.booking || {};
                this.bookingSummary = res.data.summary || {};
                this.bookingSales = res.data.sales || [];
                this.latestSale = res.data.sale || null;
                this.currencySymbol = res.data.currency_symbol || 'LKR';
                this.activeTab = 'Overview';
            } finally {
                setTimeout(() => {
                    this.loadingCanvas = false;
                }, 300);
            }
        },

        closeCanvas() {
            this.animating = 'out';
            setTimeout(() => {
                this.showCanvas = false;
                this.animating = false;
                this.selectedBooking = {};
                this.bookingSummary = {};
                this.bookingSales = [];
                this.latestSale = null;
            }, 350);
        },
    },
};
</script>

<style scoped>
@keyframes slideIn {
    from {
        transform: translateX(100%);
    }

    to {
        transform: translateX(0);
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(100%);
    }
}

.animate-slideIn {
    animation: slideIn 0.35s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}

.animate-slideOut {
    animation: slideOut 0.35s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }

    to {
        opacity: 0;
    }
}

.animate-fadeOut {
    animation: fadeOut 0.25s ease-out forwards;
}

.skeleton {
    background: rgba(0, 0, 0, 0.08);
    border-radius: 6px;
    position: relative;
    overflow: hidden;
}

.dark .skeleton {
    background: rgba(255, 255, 255, 0.08);
}

.skeleton::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    height: 100%;
    width: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
    animation: shimmer 1.2s infinite;
}

@keyframes shimmer {
    100% {
        left: 100%;
    }
}
</style>
