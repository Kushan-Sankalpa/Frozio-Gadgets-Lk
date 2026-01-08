<template>
    <transition name="fade">
        <div v-if="show" class="pointer-events-none fixed inset-0 z-[140] flex">
            <!-- backdrop -->
            <div
                class="hidden flex-1 bg-gradient-to-br from-neutral-900/60 to-neutral-800/40 md:block"
                @click="close"
            ></div>

            <!-- panel -->
            <aside
                class="pointer-events-auto relative ml-auto flex h-full w-full max-w-md flex-col bg-gradient-to-b from-white to-neutral-50 shadow-2xl md:rounded-l-3xl"
            >

                                        <div class="flex flex-col items-center text-center mt-8">
                        <!-- Tick in circle -->
                        <div class="grid size-14 place-items-center rounded-full bg-gradient-to-br from-amber-500 to-rose-600 text-white text-2xl font-bold shadow-lg shadow-rose-500/30 mb-4">
                            ✓
                        </div>
                                        </div>
                    <div>
                        <h1 class="mt-1 text-2xl font-bold text-neutral-900 tracking-tight text-center">
                            Payment successful
                        </h1>
                        <p class="mt-1 text-sm text-neutral-600 leading-relaxed text-center">
                            Sale has been recorded for this booking.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="absolute top-6 right-6 flex size-10 items-center justify-center rounded-full border border-neutral-200 bg-white text-neutral-400 hover:bg-neutral-50 hover:text-neutral-800 hover:border-neutral-300 transition-all duration-200 hover:scale-105 active:scale-95"
                        @click="close"
                    >
                        ✕
                    </button>

                <!-- body -->
                <main class="flex-1 overflow-y-auto px-6 py-6 pb-0">
                    <div class="space-y-5 pb-6">
                        <!-- big check -->
                        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-rose-600 px-5 py-4 shadow-lg shadow-rose-500/20 hover:shadow-xl hover:shadow-rose-500/30 transition-all duration-300">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative flex items-center justify-between">
                                
                                    <div class="text-2xl font-bold text-white tracking-tight">
                                        {{ currencySymbol }} {{ totalWithTipFormatted }}
                                    </div>
                                    <div class="text-sm text-white font-medium bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm">
                                        Paid with {{ paymentLabel }}
                                    </div>
                                
                            </div>
                        </div>

                        <!-- client -->
                        <div
                            v-if="client"
                            class="group rounded-2xl bg-white border border-neutral-100 px-5 py-4 flex items-center gap-4 shadow-sm hover:shadow-md hover:border-neutral-200 transition-all duration-200"
                        >
                            <div
                                class="grid size-12 place-items-center rounded-full bg-gradient-to-br from-neutral-800 to-neutral-900 text-base font-bold text-white shadow-md group-hover:scale-105 transition-transform duration-200"
                            >
                                {{ initials(client.name) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-base font-semibold text-neutral-900">
                                    {{ client.name }}
                                </div>
                                <div v-if="client.email" class="truncate text-sm text-neutral-500 mt-0.5">
                                    {{ client.email }}
                                </div>
                            </div>
                        </div>

                        <!-- breakdown -->
                        <div class="rounded-2xl border border-neutral-200 bg-white px-5 py-5 shadow-sm space-y-3">
                            <div class="flex justify-between text-sm text-neutral-600">
                                <span>Subtotal</span>
                                <span class="tabular-nums font-medium text-neutral-900">
                                    {{ currencySymbol }} {{ baseAmountFormatted }}
                                </span>
                            </div>
                            <div
                                v-if="taxAmount > 0"
                                class="flex justify-between text-sm text-neutral-600"
                            >
                                <span>Tax</span>
                                <span class="tabular-nums font-medium text-neutral-900">
                                    {{ currencySymbol }} {{ taxAmountFormatted }}
                                </span>
                            </div>
                            <div
                                v-if="tipAmount > 0"
                                class="flex justify-between text-sm text-neutral-600"
                            >
                                <span>Tip</span>
                                <span class="tabular-nums font-medium text-neutral-900">
                                    {{ currencySymbol }} {{ tipAmountFormatted }}
                                </span>
                            </div>
                            <div class="pt-0 flex justify-between text-lg font-bold text-neutral-900">
                                <span>Total</span>
                                <span class="tabular-nums">
                                    {{ currencySymbol }} {{ totalWithTipFormatted }}
                                </span>
                            </div>

                            <div
                                v-if="payments && payments.length"
                                class="pt-4 mt-3 border-t space-y-2 text-xs"
                            >
                                <div class="font-bold uppercase tracking-[0.16em] text-neutral-500">
                                    Payments
                                </div>
                                <div class="space-y-2">
                                    <div
                                        v-for="(p, idx) in payments"
                                        :key="idx"
                                        class="flex justify-between text-sm text-neutral-700 py-1"
                                    >
                                        <span class="font-medium">{{ paymentLabelFor(p.method) }}</span>
                                        <span class="tabular-nums font-semibold text-neutral-900">
                                            {{ currencySymbol }} {{ formatNumber(p.amount) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- actions - sticky footer -->
                    <div class="sticky bottom-0 left-0 right-0 bg-gradient-to-t from-white via-white to-transparent px-0 py-4 border-t border-neutral-100 space-y-3">
                        <div class="flex flex-col items-center">
                        <button
                            v-if="!loadingDetails && bookingId"
                            type="button"
                            class="w-full rounded-full bg-gradient-to-r from-neutral-900 to-neutral-800 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-neutral-900/20 hover:shadow-xl hover:shadow-neutral-900/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                            @click="openBookingDetails"
                        >
                            View sale / booking details
                        </button>

                        <button
                            v-if="loadingDetails"
                            type="button"
                            class="w-full rounded-full bg-gradient-to-r from-neutral-900 to-neutral-800 px-5 py-3.5 text-sm font-semibold text-white shadow-lg opacity-60 cursor-not-allowed"
                            disabled
                        >
                            <span class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading details...
                            </span>
                        </button>

                        <button
                            type="button"
                            class="w-full rounded-full border border-neutral-200 shadow-sm bg-white px-5 py-3 text-sm font-semibold text-neutral-800 hover:bg-neutral-50 hover:border-neutral-300 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 cursor-pointer"
                            @click="close"
                        >
                            Close
                        </button>
                        </div>
                    </div>
                </main>
            </aside>
        </div>
    </transition>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import axios from 'axios';

export default defineComponent({
    name: 'SuccessPayment',

    data() {
        return {
            loadingDetails: false as boolean,
        };
    },

    computed: {
        show(): boolean {
            return this.$store.getters.successPaymentShow;
        },
        meta(): any {
            return this.$store.getters.successPaymentMeta || {};
        },

        bookingId(): number | null {
            const fromMeta = this.meta.bookingId ?? this.meta.booking_id;
            const fromBooking = this.meta.booking?.id;

            const raw = fromMeta ?? fromBooking;
            const num = Number(raw);

            return Number.isFinite(num) && num > 0 ? num : null;
        },

        client(): any {
            return this.meta.client || null;
        },

        currencySymbol(): string {
            return this.meta.currencySymbol || this.meta.currency_symbol || 'LKR';
        },

        baseAmount(): number {
            return Number(this.meta.base_amount ?? 0);
        },
        taxAmount(): number {
            return Number(this.meta.tax_amount ?? 0);
        },
        tipAmount(): number {
            return Number(this.meta.tip_amount ?? 0);
        },
        totalWithTip(): number {
            return Number(this.meta.total_with_tip ?? 0);
        },
        payments(): any[] {
            return Array.isArray(this.meta.payments) ? this.meta.payments : [];
        },
        paymentMethod(): string | null {
            return (
                this.meta.payment_method ||
                this.meta.payment_option_name ||
                null
            );
        },

        baseAmountFormatted(): string {
            return this.formatNumber(this.baseAmount);
        },
        taxAmountFormatted(): string {
            return this.formatNumber(this.taxAmount);
        },
        tipAmountFormatted(): string {
            return this.formatNumber(this.tipAmount);
        },
        totalWithTipFormatted(): string {
            return this.formatNumber(this.totalWithTip);
        },

        paymentLabel(): string {
            return this.paymentMethod
                ? this.paymentLabelFor(this.paymentMethod)
                : '—';
        },
    },

    methods: {
        close() {
            this.$store.commit('CLOSE_SUCCESS_PAYMENT');
        },

        async openBookingDetails() {
            if (!this.bookingId || this.loadingDetails) return;

            this.loadingDetails = true;

            try {
                const response = await axios.get(`/bookings/${this.bookingId}/details`);
                const data = response.data || {};

                this.$store.commit('CLOSE_SUCCESS_PAYMENT');

                this.$store.commit('OPEN_BOOKING_DETAILS', {
                    bookingId: data.booking?.id ?? this.bookingId,
                    booking: data.booking ?? null,
                    sale: data.sale ?? null,
                    sales: data.sales ?? [],
                    summary: data.summary ?? null,
                    currencySymbol: data.currency_symbol ?? this.currencySymbol,
                });
            } catch (error) {
                console.error('Failed to load booking details', error);

                this.$store.commit('CLOSE_SUCCESS_PAYMENT');
                this.$store.commit('OPEN_BOOKING_DETAILS', {
                    bookingId: this.bookingId,
                    booking: this.meta.booking || null,
                    sale: null,
                    sales: [],
                    summary: null,
                    currencySymbol: this.currencySymbol,
                });
            } finally {
                this.loadingDetails = false;
            }
        },

        paymentLabelFor(method: string): string {
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
                    return method || 'Payment';
            }
        },

        formatNumber(v: number): string {
            const n = Number(v) || 0;
            return n.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        initials(name: string): string {
            return String(name || '')
                .trim()
                .split(/\s+/)
                .map((p) => p[0]?.toUpperCase())
                .join('')
                .slice(0, 3);
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

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>