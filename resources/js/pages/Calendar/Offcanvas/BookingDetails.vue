<template>
    <transition name="fade">
        <div v-if="show" class="pointer-events-none fixed inset-0 z-[150] flex">
            <div
                class="hidden flex-1 bg-gradient-to-br from-neutral-900/60 to-neutral-800/40 md:block"
                @click="close"
            ></div>

            <aside
                ref="printArea"
                id="booking-details-print"
                class="pointer-events-auto relative ml-auto flex h-full w-full max-w-xl flex-col bg-gradient-to-b from-white to-neutral-50 shadow-2xl md:rounded-l-3xl"
            >
                <header
                    class="relative border-b border-neutral-100/80 bg-white/80 px-6 py-6 backdrop-blur-sm"
                >
                    <div>
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1"
                        >
                            <div
                                class="size-1.5 animate-pulse rounded-full bg-blue-500"
                            ></div>
                            <span
                                class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700"
                            >
                                Booking
                            </span>
                        </div>
                        <h1
                            class="mt-3 text-3xl font-bold tracking-tight text-neutral-900"
                        >
                            Booking details
                        </h1>
                        <p
                            class="mt-2 text-sm text-neutral-600"
                            v-if="booking"
                        >
                            #{{ booking.id }} •
                            {{ booking.date_formatted || booking.date || 'N/A' }}
                        </p>

                        <div
                            class="mt-4 flex flex-wrap items-center gap-2 no-print"
                            v-if="summary || booking"
                        >
                            <div class="relative">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-full border border-neutral-200 bg-white px-3 py-1.5 text-xs font-medium text-neutral-800 shadow-sm hover:border-neutral-300 hover:bg-neutral-50"
                                    @click.stop="toggleActions"
                                >
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg
                                            class="h-3.5 w-3.5 text-neutral-500"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M5 8h14M5 12h14M5 16h14"
                                            />
                                        </svg>
                                        <span>Actions</span>
                                    </span>
                                    <span class="text-[10px] text-neutral-500">▾</span>
                                </button>

                                <transition name="fade">
                                    <div
                                        v-if="actionsOpen"
                                        class="absolute right-0 z-20 mt-2 w-56 rounded-2xl border border-neutral-200 bg-white py-1 text-xs text-neutral-800 shadow-xl"
                                    >
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between px-3 py-2 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-60"
                                            @click="onDownloadPdf"
                                            :disabled="isDownloading || !hasPrintableContent"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <svg
                                                    class="h-3.5 w-3.5 text-neutral-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"
                                                    />
                                                </svg>
                                                <span>Download PDF</span>
                                            </span>
                                            <span class="text-[11px] text-neutral-400">
                                                {{ isDownloading ? 'Preparing…' : '' }}
                                            </span>
                                        </button>

                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between px-3 py-2 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-60"
                                            @click="onPrint"
                                            :disabled="isPrinting || !hasPrintableContent"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <svg
                                                    class="h-3.5 w-3.5 text-neutral-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M7 8V4h10v4M7 16h10v4H7z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M5 12h14a2 2 0 012 2v4h-3M4 18v-4a2 2 0 012-2"
                                                    />
                                                </svg>
                                                <span>Print</span>
                                            </span>
                                            <span class="text-[11px] text-neutral-400">
                                                {{ isPrinting ? 'Preparing…' : '' }}
                                            </span>
                                        </button>

                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between px-3 py-2 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-60"
                                            @click="onEmailReceipt"
                                            :disabled="isEmailing || !booking || !hasPrintableContent"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <svg
                                                    class="h-3.5 w-3.5 text-neutral-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M4 6h16v12H4z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M4 7l8 6 8-6"
                                                    />
                                                </svg>
                                                <span>Email receipt</span>
                                            </span>
                                            <span class="text-[11px] text-neutral-400">
                                                {{ isEmailing ? 'Sending…' : '' }}
                                            </span>
                                        </button>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="no-print absolute right-6 top-6 flex size-10 cursor-pointer items-center justify-center rounded-full border border-neutral-200 bg-white text-neutral-400 transition-all duration-200 hover:scale-105 hover:border-neutral-300 hover:bg-neutral-50 hover:text-neutral-800 active:scale-95"
                        @click="close"
                    >
                        ✕
                    </button>
                </header>

                <main class="flex-1 space-y-5 overflow-y-auto px-6 py-6">
                    <section
                        v-if="summary"
                        class="group rounded-2xl border border-neutral-100 bg-gradient-to-br from-neutral-50 to-neutral-100/50 px-5 py-5 shadow-sm transition-all duration-200 hover:shadow-md"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="text-xs font-bold uppercase tracking-[0.16em] text-neutral-500"
                            >
                                Summary
                            </div>
                            <div
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold shadow-sm"
                                :class="statusBadgeClass"
                            >
                                {{ capitalize(summary.status || 'scheduled') }}
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div
                                class="rounded-xl border border-neutral-100 bg-white/60 px-3 py-3"
                            >
                                <div class="mb-1 text-xs text-neutral-500">
                                    Total
                                </div>
                                <div
                                    class="tabular-nums text-lg font-bold text-neutral-900"
                                >
                                    {{ currencySymbol }}
                                    {{ formatNumber(summary.total_price) }}
                                </div>
                            </div>
                            <div
                                class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-3 py-3"
                            >
                                <div class="mb-1 text-xs text-emerald-600">
                                    Paid
                                </div>
                                <div
                                    class="tabular-nums text-lg font-bold text-emerald-700"
                                >
                                    {{ currencySymbol }}
                                    {{ formatNumber(summary.total_paid) }}
                                </div>
                            </div>
                            <div
                                class="rounded-xl border border-neutral-100 bg-white/60 px-3 py-3"
                            >
                                <div class="mb-1 text-xs text-neutral-500">
                                    Remaining
                                </div>
                                <div
                                    class="tabular-nums text-lg font-bold"
                                    :class="
                                        summary.remaining > 0
                                            ? 'text-amber-600'
                                            : 'text-emerald-700'
                                    "
                                >
                                    {{ currencySymbol }}
                                    {{ formatNumber(summary.remaining) }}
                                </div>
                            </div>
                            <div
                                class="rounded-xl border border-neutral-100 bg-white/60 px-3 py-3"
                            >
                                <div class="mb-1 text-xs text-neutral-500">
                                    Has sales
                                </div>
                                <div
                                    class="text-lg font-bold text-neutral-900"
                                >
                                    {{ summary.has_sales ? 'Yes' : 'No' }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="client || staff"
                        class="grid gap-3 md:grid-cols-2"
                    >
                        <div
                            v-if="client"
                            class="group flex items-center gap-4 rounded-2xl border border-neutral-200 bg-white px-5 py-4 shadow-sm transition-all duration-200 hover:border-neutral-300 hover:shadow-md"
                        >
                            <div
                                class="grid size-12 place-items-center rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-base font-bold text-white shadow-md transition-transform duration-200 group-hover:scale-105"
                            >
                                {{
                                    initials(
                                        client.name || client.full_name,
                                    )
                                }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div
                                    class="truncate text-base font-semibold text-neutral-900"
                                >
                                    {{ client.name || client.full_name }}
                                </div>
                                <div
                                    v-if="client.email"
                                    class="mt-0.5 truncate text-xs text-neutral-500"
                                >
                                    {{ client.email }}
                                </div>
                                <div
                                    v-if="client.phone"
                                    class="truncate text-xs text-neutral-500"
                                >
                                    {{ client.phone }}
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="staff"
                            class="group flex items-center gap-4 rounded-2xl border border-neutral-200 bg-white px-5 py-4 shadow-sm transition-all duration-200 hover:border-neutral-300 hover:shadow-md"
                        >
                            <div
                                class="grid size-12 place-items-center rounded-full bg-gradient-to-br from-neutral-600 to-neutral-700 text-base font-bold text-white shadow-md transition-transform duration-200 group-hover:scale-105"
                            >
                                {{ initials(staff.name) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div
                                    class="truncate text-base font-semibold text-neutral-900"
                                >
                                    {{ staff.name }}
                                </div>
                                <div
                                    class="truncate text-xs text-neutral-500"
                                >
                                    Staff
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="sale"
                        class="rounded-2xl border border-neutral-200 bg-white px-5 py-5 shadow-sm transition-all duration-200 hover:shadow-md"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div
                                class="text-xs font-bold uppercase tracking-[0.16em] text-neutral-500"
                            >
                                Latest sale
                            </div>
                            <div
                                class="text-xs font-medium text-neutral-400"
                            >
                                {{ formatDateTime(sale.created_at) }}
                            </div>
                        </div>

                        <div class="space-y-2 text-sm text-neutral-600">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span
                                    class="tabular-nums font-semibold text-neutral-900"
                                >
                                    {{ currencySymbol }}
                                    {{ formatNumber(sale.base_amount) }}
                                </span>
                            </div>
                            <div
                                v-if="sale.tax_amount > 0"
                                class="flex justify-between"
                            >
                                <span>Tax</span>
                                <span
                                    class="tabular-nums font-semibold text-neutral-900"
                                >
                                    {{ currencySymbol }}
                                    {{ formatNumber(sale.tax_amount) }}
                                </span>
                            </div>
                            <div
                                v-if="sale.tip_amount > 0"
                                class="flex justify-between"
                            >
                                <span>Tip</span>
                                <span
                                    class="tabular-nums font-semibold text-neutral-900"
                                >
                                    {{ currencySymbol }}
                                    {{ formatNumber(sale.tip_amount) }}
                                </span>
                            </div>
                            <div
                                class="mt-3 flex justify-between border-t-2 border-neutral-100 pt-3 text-lg font-bold text-neutral-900"
                            >
                                <span>Total (with tip)</span>
                                <span class="tabular-nums">
                                    {{ currencySymbol }}
                                    {{ formatNumber(sale.total_with_tip) }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between rounded-lg border-t bg-neutral-50 px-3 py-2.5 text-sm"
                        >
                            <span class="text-neutral-600">
                                Payment method
                            </span>
                            <span
                                class="font-bold text-neutral-900"
                            >
                                {{ paymentLabelFor(sale.payment_method) }}
                            </span>
                        </div>
                    </section>

                    <!-- all sales -->
                    <section
                        v-if="sales && sales.length"
                        class="rounded-2xl border border-neutral-200 bg-white px-5 py-5 shadow-sm"
                    >
                        <div
                            class="mb-4 text-xs font-bold uppercase tracking-[0.16em] text-neutral-500"
                        >
                            All sales
                        </div>

                        <div class="space-y-3 text-sm">
                            <div
                                v-for="s in sales"
                                :key="s.id"
                                class="space-y-2 rounded-xl border border-neutral-200 bg-gradient-to-br from-neutral-50 to-neutral-100/50 px-4 py-3 transition-all duration-200 hover:shadow-md"
                            >
                                <div
                                    class="flex items-center justify-between"
                                >
                                    <div
                                        class="text-base font-bold text-neutral-900"
                                    >
                                        {{ currencySymbol }}
                                        {{ formatNumber(s.total_with_tip) }}
                                    </div>
                                    <div
                                        class="text-xs font-medium text-neutral-500"
                                    >
                                        {{ formatDateTime(s.created_at) }}
                                    </div>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-neutral-500">
                                        Method
                                    </span>
                                    <span
                                        class="font-semibold text-neutral-700"
                                    >
                                        {{ paymentLabelFor(s.payment_method) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-neutral-500">
                                        Paid
                                    </span>
                                    <span
                                        class="tabular-nums font-semibold text-emerald-700"
                                    >
                                        {{ currencySymbol }}
                                        {{ formatNumber(s.total_paid) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-neutral-500">
                                        Remaining after this sale
                                    </span>
                                    <span
                                        class="tabular-nums font-semibold text-neutral-700"
                                    >
                                        {{ currencySymbol }}
                                        {{ formatNumber(s.remaining) }}
                                    </span>
                                </div>

                                <!-- payments breakdown inside this sale -->
                                <div
                                    v-if="s.payments && s.payments.length"
                                    class="mt-3 space-y-1.5 border-t border-neutral-200 pt-3"
                                >
                                    <div
                                        class="text-[11px] font-bold uppercase tracking-[0.16em] text-neutral-500"
                                    >
                                        Payments
                                    </div>
                                    <div
                                        v-for="(p, idx) in s.payments"
                                        :key="idx"
                                        class="flex justify-between text-xs text-neutral-600"
                                    >
                                        <span>
                                            {{
                                                paymentLabelFor(
                                                    p.method || p.type,
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="tabular-nums font-semibold text-neutral-900"
                                        >
                                            {{ currencySymbol }}
                                            {{ formatNumber(p.amount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- services -->
                    <section
                        v-if="services && services.length"
                        class="rounded-2xl border border-neutral-200 bg-white px-5 py-5 shadow-sm"
                    >
                        <div
                            class="mb-4 text-xs font-bold uppercase tracking-[0.16em] text-neutral-500"
                        >
                            Services
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="svc in services"
                                :key="svc.id"
                                class="flex items-start justify-between gap-4 rounded-xl border border-neutral-200 bg-gradient-to-br from-neutral-50 to-neutral-100/50 px-4 py-3 text-sm transition-all duration-200 hover:shadow-md"
                            >
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="truncate text-base font-bold text-neutral-900"
                                    >
                                        {{ svc.label }}
                                    </div>
                                    <div
                                        class="mt-1 flex items-center gap-2 text-xs text-neutral-500"
                                    >
                                        <span
                                            class="inline-flex items-center gap-1"
                                        >
                                            <svg
                                                class="h-3 w-3"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                ></path>
                                            </svg>
                                            {{ svc.duration }} min
                                        </span>
                                        <span
                                            v-if="svc.extra_minutes"
                                            class="text-amber-600"
                                        >
                                            (+{{ svc.extra_minutes }} min)
                                        </span>
                                    </div>
                                    <div
                                        class="mt-1 text-xs text-neutral-400"
                                    >
                                        {{ formatDateTime(svc.starts_at) }}
                                        <span v-if="svc.ends_at">
                                            –
                                            {{ formatDateTime(svc.ends_at) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <div
                                        class="tabular-nums text-base font-bold text-neutral-900"
                                    >
                                        {{ currencySymbol }}
                                        {{
                                            formatNumber(
                                                svc.final_price ?? svc.price,
                                            )
                                        }}
                                    </div>
                                    <div
                                        v-if="svc.discount_value"
                                        class="mt-1 text-[11px] font-semibold text-emerald-600"
                                    >
                                        -{{ svc.discount_type }}
                                        {{ svc.discount_value }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- notes -->
                    <section
                        v-if="booking && booking.notes"
                        class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-amber-100/50 px-5 py-4 text-sm shadow-sm"
                    >
                        <div class="mb-3 flex items-center gap-2">
                            <svg
                                class="h-4 w-4 text-amber-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                ></path>
                            </svg>
                            <div
                                class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700"
                            >
                                Notes
                            </div>
                        </div>
                        <p
                            class="whitespace-pre-line leading-relaxed text-neutral-800"
                        >
                            {{ booking.notes }}
                        </p>
                    </section>
                </main>
            </aside>
        </div>
    </transition>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import axios from 'axios';
import html2canvas from 'html2canvas-pro';
import jsPDF from 'jspdf';

export default defineComponent({
    name: 'BookingDetails',

    data() {
        return {
            actionsOpen: false as boolean,
            isDownloading: false as boolean,
            isPrinting: false as boolean,
            isEmailing: false as boolean,
        };
    },

    computed: {
        show(): boolean {
            return this.$store.getters.bookingDetailsShow;
        },
        meta(): any {
            return this.$store.getters.bookingDetailsMeta || {};
        },

        currencySymbol(): string {
            return (
                this.meta.currencySymbol ||
                this.meta.currency_symbol ||
                'LKR'
            );
        },

        booking(): any {
            return this.meta.booking || null;
        },
        sale(): any {
            return this.meta.sale || null;
        },
        sales(): any[] {
            return Array.isArray(this.meta.sales) ? this.meta.sales : [];
        },
        summary(): any {
            return this.meta.summary || null;
        },

        client(): any {
            return (
                (this.booking && this.booking.client) ||
                this.meta.client ||
                null
            );
        },
        staff(): any {
            return (
                (this.booking && this.booking.staff) ||
                this.meta.staff ||
                null
            );
        },
        services(): any[] {
            if (this.booking && Array.isArray(this.booking.services)) {
                return this.booking.services;
            }
            if (this.sale && Array.isArray(this.sale.services)) {
                return this.sale.services;
            }
            return [];
        },

        statusBadgeClass(): string {
            const status = (this.summary?.status || '').toLowerCase();
            if (status === 'completed' || status === 'paid') {
                return 'bg-emerald-100 text-emerald-700 ring-2 ring-emerald-200';
            }
            if (status === 'cancelled' || status === 'canceled') {
                return 'bg-red-100 text-red-700 ring-2 ring-red-200';
            }
            if (status === 'pending') {
                return 'bg-amber-100 text-amber-700 ring-2 ring-amber-200';
            }
            return 'bg-neutral-100 text-neutral-700 ring-2 ring-neutral-200';
        },

        hasPrintableContent(): boolean {
            return !!(
                this.booking ||
                this.summary ||
                (this.sales && this.sales.length) ||
                (this.services && this.services.length)
            );
        },
    },

    methods: {
        toggleActions() {
            this.actionsOpen = !this.actionsOpen;
        },

        close() {
            this.actionsOpen = false;
            this.$store.commit('CLOSE_BOOKING_DETAILS');
        },

        formatNumber(v: number | string | null | undefined): string {
            const n = Number(v) || 0;
            return n.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        formatDateTime(dt?: string | null): string {
            if (!dt) return '';
            const d = new Date(dt);
            if (Number.isNaN(d.getTime())) return dt as string;
            return d.toLocaleString(undefined, {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        paymentLabelFor(method: string | null | undefined): string {
            if (!method) return 'Payment';
            switch (method) {
                case 'cash':
                    return 'Cash';
                case 'card':
                    return 'Card';
                case 'gift-card':
                    return 'Gift card';
                case 'split':
                    return 'Split payment';
                case 'other':
                    return 'Other';
                default:
                    return method;
            }
        },

        initials(name: string | undefined): string {
            return String(name || '')
                .trim()
                .split(/\s+/)
                .map((p) => p[0]?.toUpperCase())
                .join('')
                .slice(0, 3);
        },

        capitalize(value: string): string {
            if (!value) return '';
            return value.charAt(0).toUpperCase() + value.slice(1);
        },

        getPrintElement(): HTMLElement | null {
            const el = this.$refs.printArea as HTMLElement | undefined;
            return el || null;
        },

        pdfFilename(): string {
            const id = this.booking?.id ?? 'booking';
            return `booking-${id}-receipt.pdf`;
        },

        async renderToCanvas(el: HTMLElement): Promise<HTMLCanvasElement> {
            const canvas = await html2canvas(el, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
                ignoreElements: (element: HTMLElement) =>
                    element.classList?.contains('no-print'),
            });
            return canvas;
        },

     
        async buildPdf(el: HTMLElement): Promise<any> {
            const canvas = await this.renderToCanvas(el);
            const imgData = canvas.toDataURL('image/jpeg', 0.98);

            const pdf = new jsPDF('p', 'mm', 'a4');

            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            const marginX = 10; 
            const marginY = 10; 
            const contentWidth = pageWidth - marginX * 2;
            const contentHeight = pageHeight - marginY * 2;

            const imgWidth = contentWidth;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let heightLeft = imgHeight;
            let position = marginY;

            pdf.addImage(imgData, 'JPEG', marginX, position, imgWidth, imgHeight);
            heightLeft -= contentHeight;

            while (heightLeft > 0) {
                pdf.addPage();
                position = marginY - (imgHeight - heightLeft);
                pdf.addImage(
                    imgData,
                    'JPEG',
                    marginX,
                    position,
                    imgWidth,
                    imgHeight,
                );
                heightLeft -= contentHeight;
            }

            return pdf;
        },

        async buildPdfBlob(el: HTMLElement): Promise<Blob> {
            const pdf = await this.buildPdf(el);
            return pdf.output('blob') as Blob;
        },

        async onDownloadPdf(): Promise<void> {
            const el = this.getPrintElement();
            if (!el) return;

            if (!this.hasPrintableContent) {
                window.alert('There is nothing to export yet.');
                return;
            }

            this.isDownloading = true;

            try {
                const pdf = await this.buildPdf(el);
                pdf.save(this.pdfFilename());
            } catch (e) {
                console.error('Failed to generate PDF', e);
                window.alert('Failed to generate PDF. Please try again.');
            } finally {
                this.isDownloading = false;
                this.actionsOpen = false;
            }
        },

        async onPrint(): Promise<void> {
            const el = this.getPrintElement();
            if (!el) return;

            if (!this.hasPrintableContent) {
                window.alert('There is nothing to print yet.');
                return;
            }

            this.isPrinting = true;

            try {
                const pdf = await this.buildPdf(el);
                pdf.autoPrint();
                const blob = pdf.output('blob') as Blob;
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, '_blank');
                setTimeout(() => URL.revokeObjectURL(blobUrl), 60_000);
            } catch (e) {
                console.error('Failed to print PDF', e);
                window.alert('Failed to print. Please try again.');
            } finally {
                this.isPrinting = false;
                this.actionsOpen = false;
            }
        },

        async onEmailReceipt(): Promise<void> {
            const booking: any = this.booking || null;
            if (!booking || !booking.id) return;

            if (!this.hasPrintableContent) {
                window.alert('There is nothing to send yet.');
                return;
            }

            let defaultEmail =
                (this.client && (this.client.email as string)) || '';
            let email = defaultEmail;

            if (!email) {
                email = (window.prompt(
                    'Send receipt to email:',
                    '',
                ) || '') as string;
            }

            if (!email) {
                return;
            }

            const el = this.getPrintElement();
            if (!el) {
                window.alert(
                    'Unable to generate receipt. Please reload and try again.',
                );
                return;
            }

            this.isEmailing = true;

            try {
                const blob = await this.buildPdfBlob(el);

                const formData = new FormData();
                formData.append('email', email);
                formData.append('pdf', blob, this.pdfFilename());

                const url = (window as any).route
                    ? (window as any).route(
                          'bookings.email-receipt',
                          booking.id,
                      )
                    : `/bookings/${booking.id}/email-receipt`;

                await axios.post(url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                console.log('Receipt emailed successfully to', email);
            } catch (e) {
                console.error('Failed to email receipt', e);
                window.alert(
                    'Failed to email receipt. Please try again.',
                );
            } finally {
                this.isEmailing = false;
                this.actionsOpen = false;
            }
        },
    },
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease-out, transform 0.3s ease-out;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateX(24px);
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* For browser print (Ctrl+P) */
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
