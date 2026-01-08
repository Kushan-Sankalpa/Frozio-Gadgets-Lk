<template>
    <Transition name="completed-offcanvas">
        <div v-if="show" class="fixed inset-0 z-[260] flex">
            <!-- backdrop -->
            <div class="flex-1 bg-black/40 cursor-pointer" @click="$emit('close')"></div>

            <!-- panel -->
            <div class="relative flex h-full w-full max-w-2xl flex-col bg-white">
                <!-- body  -->
                <div class="flex flex-1 overflow-hidden">
                    <!-- left tabs -->
                    <div class="w-32 border-r bg-neutral-50 border-t border-neutral-200">
                        <button type="button"
                            class="flex w-full cursor-pointer items-center justify-between border-l-4 px-6 py-6 text-xs md:text-sm font-medium"
                            :class="activeTab === 'details'
                                    ? 'bg-[var(--brand,_var(--brand-fallback))]/10 text-neutral-900 border-[var(--brand,_var(--brand-fallback))]'
                                    : 'text-neutral-500 hover:bg-neutral-100 border-transparent'
                                " @click="activeTab = 'details'">
                            <div class="flex items-center gap-2">
                                <i class="bx bx-detail text-base"></i>
                                <span>Details</span>
                            </div>
                        </button>
                        <button type="button"
                            class="flex w-full cursor-pointer items-center justify-between border-l-4 px-6 py-6 text-xs md:text-sm font-medium"
                            :class="activeTab === 'activity'
                                    ? 'bg-[var(--brand,_var(--brand-fallback))]/10 text-neutral-900 border-[var(--brand,_var(--brand-fallback))]'
                                    : 'text-neutral-500 hover:bg-neutral-100 border-transparent'
                                " @click="activeTab = 'activity'">
                            <div class="flex items-center gap-2">
                                <i class="bx bx-history text-base"></i>
                                <span>Activity</span>
                            </div>
                        </button>
                    </div>

                    <!-- main content area -->
                    <div class="flex-1 flex flex-col overflow-hidden">
                        <div class="border-neutral-200 px-6 py-4">
                            <div class="flex items-start justify-between">
                                <!-- Left side-->
                                <div class="flex flex-col">
                                    <!-- completed pill -->
                                    <span class="inline-flex items-center rounded-full bg-[var(--brand,_var(--brand-fallback))]/10 px-5 py-2.5
                                        text-sm font-semibold text-[var(--brand,_var(--brand-fallback))] w-fit mb-3">
                                        <i class="bx bx-check-circle mr-2 text-base"></i>
                                        Completed
                                    </span>

                                    <div class="flex flex-col">
                                        <span class="text-xl md:text-2xl font-bold text-neutral-900">
                                            Sale
                                        </span>
                                        <span class="text-[11px] md:text-xs text-neutral-500 mt-0.5">
                                            {{ headerDateLabel }}
                                            <span v-if="bookingStaffName">
                                                <!-- · {{ bookingStaffName }} -->
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Right side -->
                                <div class="flex items-center gap-2">
                                    <!-- <button
                                        v-if="canRebook"
                                        type="button"
                                        class="inline-flex cursor-pointer items-center rounded-full bg-neutral-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800 transition-colors"
                                        @click="$emit('rebook', booking && (booking as any).id)"
                                    >
                                        Rebook
                                    </button> -->

                                    <!-- more options + dropdown -->
                                    <div class="relative">
                                        <button type="button"
                                            class="flex h-8 w-10 cursor-pointer items-center justify-center rounded-full hover:bg-neutral-100"
                                            @click.stop="toggleMoreMenu">
                                            <span class="sr-only">More options</span>
                                            <i class="bx bx-dots-vertical-rounded text-xl text-neutral-600"></i>
                                        </button>

                                        <Transition name="dropdown">
                                            <div v-if="moreMenuOpen" ref="moreDropdown"
                                                class="absolute right-0 mt-2 w-44 rounded-2xl border border-neutral-200 bg-white shadow-xl z-[300]">
                                                <button type="button"
                                                    class="flex w-full items-center px-4 py-2.5 text-sm hover:bg-neutral-50 cursor-pointer"
                                                    @click="onExportPdf">
                                                    <i class="bx bx-file-pdf text-base text-rose-500"></i>
                                                    <span>Print Receipt</span>
                                                </button>
                                                <!-- <button
                                                    type="button"
                                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm hover:bg-neutral-50"
                                                    @click="onExportExcel"
                                                >
                                                    <i class="bx bx-file text-base text-emerald-500"></i>
                                                    <span>Export Excel</span>
                                                </button> -->

                                                <button type="button"
                                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm hover:bg-neutral-50 cursor-pointer"
                                                    @click="onDownloadPdf">
                                                    <!-- <i class="bx bx-download text-base text-sky-600"></i> -->
                                                    <span>Download PDF</span>
                                                </button>


                                                <button v-if="canEditSales" type="button"
                                                    class="flex w-full items-center gap-2 px-4 py-2.5 text-sm hover:bg-neutral-50 cursor-pointer"
                                                    @click="onEditSales">
                                                    <span>Edit Sales</span>
                                                </button>


                                            </div>
                                        </Transition>
                                    </div>

                                    <button type="button"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full hover:bg-neutral-100"
                                        @click="$emit('close')">
                                        <span class="sr-only">Close</span>
                                        <i class="bx bx-x text-lg text-neutral-600"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- body content  -->
                        <div class="flex-1 overflow-y-auto px-6 py-5">
                            <div v-if="loading" class="py-16 text-center text-sm text-neutral-500">
                                Loading sale details…
                            </div>

                            <template v-else>
                                <div v-if="!booking">
                                    <p class="text-sm text-neutral-500">
                                        Booking data could not be loaded.
                                    </p>
                                </div>

                                <!-- DETAILS TAB -->
                                <div v-else-if="activeTab === 'details'" class="space-y-4">
                                    <!-- client card -->
                                    <div
                                        class="flex items-center gap-3 rounded-2xl border border-neutral-200 bg-white px-4 py-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-neutral-900 text-base font-semibold text-white">
                                            {{ clientInitials }}
                                        </div>
                                        <div class="min-w-0">
                                            <div
                                                class="truncate text-[15px] md:text-base font-semibold text-neutral-900">
                                                {{ clientName }}
                                            </div>

                                            <div class="mt-0.5 space-y-0.5 text-[11px] md:text-xs text-neutral-500">
                                                <div v-if="clientEmail" class="truncate">
                                                    {{ clientEmail }}
                                                </div>
                                                <div v-if="clientPhone" class="truncate">
                                                    {{ clientPhone }}
                                                </div>
                                                <div v-if="clientNote" class="truncate">
                                                    {{ clientNote }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- sale details -->
                                    <div class="space-y-4 rounded-2xl border border-neutral-200 bg-white px-5 py-4">
                                        <div class="flex items-baseline justify-between">
                                            <div>
                                                <div class="text-[15px] md:text-base font-semibold">
                                                    {{ saleNumberLabel }}
                                                </div>
                                                <div class="text-[11px] md:text-xs text-neutral-500">
                                                    {{ headerDateLabel }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- services list -->
                                        <div v-if="services.length" class="space-y-3 border-t border-neutral-100 pt-3">
                                          <div v-for="svc in services" :key="svc.id"
  class="flex items-start justify-between gap-4"
>
  <div class="min-w-0">
    <div class="truncate text-[14px] md:text-[15px] font-medium text-neutral-900">
      {{ svc.label }}
    </div>
    <div class="text-[11px] md:text-xs text-neutral-500">
      <span v-if="svc.durationLabel">{{ svc.durationLabel }}</span>
      <span v-if="svc.staffName">
        <span v-if="svc.durationLabel"> · </span>{{ svc.staffName }}
      </span>
    </div>
  </div>

  <div class="flex flex-col items-end shrink-0 text-right">
    <div class="whitespace-nowrap text-sm md:text-[15px] font-semibold tabular-nums">
      {{ currencySymbol }} {{ formatAmount(svc.finalTotal) }}
    </div>

    <div v-if="svc.discountTotal > 0" class="mt-0.5 text-[11px] text-rose-600 whitespace-nowrap">
      Discount: -{{ currencySymbol }} {{ formatAmount(svc.discountTotal) }}
    </div>
  </div>
</div>

                                        </div>

                                        <!-- totals -->
                                        <div class="space-y-2 border-t border-neutral-100 pt-4">
                                            <div class="flex justify-between">
                                                <span class="text-neutral-500">Subtotal</span>
                                                <span class="tabular-nums">
                                                    {{ currencySymbol }} {{ formatAmount(subtotal) }}
                                                </span>
                                            </div>

                                            <div v-if="servicesDiscounts > 0" class="flex justify-between">
                                                <span class="text-neutral-500">Discounts</span>
                                                <span class="tabular-nums">
                                                    -{{ currencySymbol }} {{ formatAmount(servicesDiscounts) }}
                                                </span>
                                            </div>

                                            <div v-if="tipAmount > 0" class="flex justify-between">
                                                <span class="text-neutral-500">Tip</span>
                                                <span class="tabular-nums">
                                                    {{ currencySymbol }} {{ formatAmount(tipAmount) }}
                                                </span>
                                            </div>

                                            <div class="flex justify-between text-[13px] md:text-[15px] font-semibold">
                                                <span class="text-neutral-900">Total</span>
                                                <span class="tabular-nums text-neutral-900">
                                                    {{ currencySymbol }} {{ formatAmount(totalWithTip) }}
                                                </span>
                                            </div>
                                        </div>


                                        <!-- payment line -->
                                        <div class="mt-3 border-t border-neutral-100 pt-3 text-[11px] md:text-xs">
                                            <div class="flex justify-between">
                                                <span class="text-neutral-500">
                                                    Paid with
                                                    {{ paymentMethodLabel }}
                                                </span>
                                                <span
                                                    class="tabular-nums text-sm md:text-[15px] font-semibold text-neutral-900">
                                                    {{ currencySymbol }}
                                                    {{ formatAmount(totalPaid) }}
                                                </span>
                                            </div>
                                            <div class="mt-1 text-[11px] md:text-xs text-neutral-500">
                                                {{ paidAtLabel }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACTIVITY TAB -->
                                <div v-else class="space-y-5">
                                    <div class="space-y-1">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {{ currentMonth }}
                                        </div>
                                    </div>

                                    <div v-if="activityItems.length" class="space-y-4">
                                        <div v-for="item in activityItems" :key="item.id"
                                            class="flex items-start gap-3">
                                            <div class="flex-1 rounded-xl border border-neutral-200 bg-white px-4 py-3">
                                                <div class="text-sm font-semibold">
                                                    {{ item.title }}
                                                </div>
                                                <div class="text-xs text-neutral-500">
                                                    {{ item.subtitle }}
                                                </div>
                                                <div v-if="item.meta" class="mt-1 text-[11px] text-neutral-500">
                                                    {{ item.meta }}
                                                </div>


                                                <div v-if="item.byline" class="mt-1 text-[11px] text-neutral-600">
                                                    {{ item.byline }}
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div v-else class="text-xs text-neutral-500">
                                        No recent activity for this sale.
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';


export default defineComponent({
    name: 'CompletedBooking',

    props: {
        show: { type: Boolean, default: false },
        loading: { type: Boolean, default: false },
        booking: { type: Object, default: null },
        sale: { type: Object, default: null },
        sales: { type: Array, default: () => [] },
        summary: { type: Object, default: null },
        currencySymbol: { type: String, default: 'LKR' },
    },

    emits: ['close', 'rebook', 'edit-sales'],


    data() {
        return {
            activeTab: 'details' as 'details' | 'activity',
            moreMenuOpen: false,
        };
    },

    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },

    computed: {
        servicesSubtotal(): number {
            return this.services.reduce((sum: number, s: any) => sum + (Number(s.baseTotal) || 0), 0);
        },

        servicesDiscounts(): number {
            return this.services.reduce((sum: number, s: any) => sum + (Number(s.discountTotal) || 0), 0);
        },

        servicesTotalAfterDiscount(): number {
            return Math.max(0, this.servicesSubtotal - this.servicesDiscounts);
        },

        // subtotal(): number {
        //     return this.servicesSubtotal;
        // },

        // totalWithTip(): number {
        //     return this.servicesTotalAfterDiscount + this.tipAmount;
        // },

        primarySale(): any | null {
            const sale: any = this.sale;
            if (sale && Object.keys(sale).length) {
                return sale;
            }
            const list: any[] = Array.isArray(this.sales)
                ? (this.sales as any[])
                : [];
            return list.length ? list[list.length - 1] : null;
        },

        client(): any | null {
            const b: any = this.booking || {};
            return b.client || null;
        },

        clientName(): string {
            const b: any = this.booking || {};
            const c: any = this.client;

            if (c) {
                const first = c.first_name ?? c.firstName ?? '';
                const last = c.last_name ?? c.lastName ?? '';
                let name = `${first} ${last}`.trim();

                if (!name) {
                    name = c.name ?? c.full_name ?? '';
                }

                if (name) {
                    return name;
                }
            }

            const page: any = (this as any).$page?.props || {};
            const clients: any[] = Array.isArray(page.clients)
                ? page.clients
                : [];
            const clientId = c?.id ?? b.client_id ?? null;

            if (clientId && clients.length) {
                const match = clients.find(
                    (cl: any) => String(cl.id) === String(clientId),
                );
                if (match?.name) {
                    return match.name;
                }
            }

            const email = c?.email ?? b.client_email ?? '';
            if (email) return email;

            return 'Walk-in';
        },

        clientEmail(): string {
            return this.client?.email || '';
        },

        clientPhone(): string {
            return (
                this.client?.phone ||
                this.client?.phone_number ||
                this.client?.mobile ||
                ''
            );
        },

        clientNote(): string {
            return this.client?.note || this.client?.notes || '';
        },

        clientInitials(): string {
            const name = this.clientName || '';
            const initials =
                name
                    .trim()
                    .split(/\s+/)
                    .map((p: string) => p[0]?.toUpperCase())
                    .join('')
                    .slice(0, 2) || 'C';
            return initials;
        },

        bookingStaffName(): string {
            const b: any = this.booking || {};
            return b.staff?.name || '';
        },

        headerDateLabel(): string {
            const src: any =
                this.primarySale || (this.booking as any) || {};
            const raw =
                src.created_at ||
                src.date_formatted ||
                src.date ||
                src.starts_at ||
                src.startsAt;
            if (!raw) return '';
            const iso =
                typeof raw === 'string' ? raw.replace(' ', 'T') : String(raw);
            const d = new Date(iso);
            if (Number.isNaN(d.getTime())) return '';
            return d.toLocaleDateString(undefined, {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },

        canEditSales(): boolean {
            const perms: any = (this as any).$page?.props?.permission || {};
            return !!perms['sales.edit'];
        },
      services(): any[] {
  const b: any = this.booking || {};
  const list: any[] = Array.isArray(b.services) ? b.services : [];

  return list.map((svc: any) => {
    const baseTotal = Math.max(0, Number(svc.price ?? 0) || 0);
    const finalTotal = Math.max(0, Number(svc.final_price ?? baseTotal) || 0);
    const discountTotal = Math.max(0, baseTotal - finalTotal);

    const minutes =
      Number(svc.duration_minutes ?? svc.duration ?? 0) +
      Number(svc.extra_minutes ?? 0);

    let durationLabel = '';
    if (minutes > 0) {
      if (minutes >= 60) {
        const h = Math.floor(minutes / 60);
        const m = minutes % 60;
        durationLabel = h + 'h' + (m ? ' ' + m + 'min' : '');
      } else {
        durationLabel = minutes + 'min';
      }
    }

    return {
      id: svc.id,
      label: svc.label || 'Service',
      durationLabel,
      staffName: svc.staff?.name || svc.staff_name || '',
      baseTotal,
      discountTotal,
      finalTotal,

      discount_type: svc.discount_type || '',
      discount_value: svc.discount_value ?? null,
    };
  });
},

        subtotal(): number {
            if (
                this.primarySale &&
                (this.primarySale as any).base_amount != null
            ) {
                return Number((this.primarySale as any).base_amount) || 0;
            }
            if (this.services.length) {
                return this.services.reduce(
                    (sum: number, s: any) => sum + (Number(s.total) || 0),
                    0,
                );
            }
            if (this.summary && (this.summary as any).total_price != null) {
                return Number((this.summary as any).total_price) || 0;
            }
            return 0;
        },

        tipAmount(): number {
            if (
                this.primarySale &&
                (this.primarySale as any).tip_amount != null
            ) {
                return Number((this.primarySale as any).tip_amount) || 0;
            }
            return 0;
        },

        totalWithTip(): number {
            if (
                this.primarySale &&
                (this.primarySale as any).total_with_tip != null
            ) {
                return Number((this.primarySale as any).total_with_tip) || 0;
            }
            return this.subtotal + this.tipAmount;
        },

        totalPaid(): number {
            if (
                this.primarySale &&
                (this.primarySale as any).total_paid != null
            ) {
                return Number((this.primarySale as any).total_paid) || 0;
            }
            if (this.summary && (this.summary as any).total_paid != null) {
                return Number((this.summary as any).total_paid) || 0;
            }
            return this.totalWithTip;
        },

        paymentMethodLabel(): string {
            const raw =
                (this.primarySale as any)?.payment_method ||
                (this.summary as any)?.payment_method ||
                '';
            const s = String(raw).toLowerCase();
            const map: Record<string, string> = {
                cash: 'Cash',
                card: 'Card',
                split: 'Split',
                other: 'Other',
            };
            return map[s] || 'Cash';
        },

        paidAtLabel(): string {
            const raw = (this.primarySale as any)?.created_at;
            if (!raw) return '';
            const iso = String(raw).replace(' ', 'T');
            const d = new Date(iso);
            if (Number.isNaN(d.getTime())) return '';
            const datePart = d.toLocaleDateString(undefined, {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
            const timePart = d.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
            return `${datePart} at ${timePart}`;
        },

        saleNumberLabel(): string {
            if (this.primarySale && (this.primarySale as any).id) {
                return `Sale #${(this.primarySale as any).id}`;
            }
            return 'Sale';
        },

        canRebook(): boolean {
            return !!(this.booking && (this.booking as any).id);
        },

        activityItems(): any[] {
            const list: any[] = Array.isArray(this.sales) ? (this.sales as any[]) : [];
            const baseList = list.length ? list : this.primarySale ? [this.primarySale] : [];

            const page: any = (this as any).$page?.props || {};
            const staffList: any[] = Array.isArray(page.staff) ? page.staff : []; // optional fallback

 const resolvePlacedByName = (): string => {
  const b: any = this.booking || {};

  // ✅ best case: backend sends placed_by_user (your showDetails already does)
  const direct =
    b?.placed_by_user?.name ||
    b?.placedBy?.name || // just in case you ever send it as placedBy
    '';

  if (direct) return direct;

  // fallback: placed_by id -> lookup in page staff list (if exists)
  const placedById = b?.placed_by ?? null;
  if (placedById && staffList.length) {
    const match = staffList.find((u: any) => String(u.id) === String(placedById));
    if (match?.name) return match.name;
  }

  return '';
};

            const resolveSource = (s: any): string => {
                // adjust keys to your backend
                return (
                    s?.source ||
                    s?.via ||
                    s?.origin ||
                    s?.channel ||
                    (s?.is_webhook ? 'webhook' : '') ||
                    ''
                );
            };

            return baseList.map((s: any) => {
                const createdAt = s.created_at
                    ? new Date(String(s.created_at).replace(' ', 'T'))
                    : null;

                const subtitle = createdAt
                    ? createdAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    : '';

                const statusRaw =
                    (s?.status ?? (this.summary as any)?.status ?? (this.booking as any)?.status ?? 'completed');

                const status = String(statusRaw).toLowerCase();
                const prettyStatus = status
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, (c) => c.toUpperCase());

   const actorName = resolvePlacedByName();
    const source = resolveSource(s);

                // decide label word
                const verb =
                    status === 'approved' ? 'Approved by' :
                        status === 'completed' ? 'Completed by' :
                            'Updated by';

   const byline =
  actorName || source
    ? `Payment collected by ${actorName || '—'}${source ? ' | ' + source : ''}`
    : '';

                return {
                    id: s.id,
                    title: `Sale ${s.id} created`,
                    subtitle: subtitle ? `Today at ${subtitle}` : '',
                    meta: `Status: ${prettyStatus}`,
                    byline,
                };
            });
        },

        currentMonth(): string {
            const now = new Date();
            return now.toLocaleDateString(undefined, { month: 'long' });
        },
    },
    watch: {
        show(val: boolean) {
            if (val) {
                this.activeTab = 'details';
                this.moreMenuOpen = false;
            }
        },
    },

    methods: {
        async openReceiptPrintDialog(filenameBase: string) {
  const html = this.buildExportHtml();

  // A full document; title often becomes the default PDF name in some browsers
  const doc = `<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${filenameBase}</title>
  <style>
    /* helps print background colors */
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  </style>
</head>
<body>
  ${html}
</body>
</html>`;

  const win = window.open('', '_blank', 'width=900,height=650');
  if (!win) {
    alert('Popup blocked. Please allow popups for printing.');
    return;
  }

  win.document.open();
  win.document.write(doc);
  win.document.close();

  // wait images + fonts (so logo renders before print)
  const waitImages = () =>
    Promise.all(
      Array.from(win.document.images).map((img) =>
        img.complete
          ? Promise.resolve(true)
          : new Promise((res) => {
              img.onload = () => res(true);
              img.onerror = () => res(true);
            }),
      ),
    );

  try {
    await waitImages();
    // @ts-ignore
    if (win.document.fonts?.ready) {
      // @ts-ignore
      await win.document.fonts.ready;
    }
  } catch (e) {
    // ignore
  }

  win.focus();
  win.print();

  // optional: close after print dialog is done
  win.addEventListener('afterprint', () => win.close(), { once: true });
},

        toggleMoreMenu() {
            this.moreMenuOpen = !this.moreMenuOpen;

            if (this.moreMenuOpen) {
                this.$nextTick(() => {
                    document.addEventListener('click', this.handleClickOutside);
                });
            } else {
                document.removeEventListener('click', this.handleClickOutside);
            }
        },

        closeMoreMenu() {
            this.moreMenuOpen = false;
            document.removeEventListener('click', this.handleClickOutside);
        },

        formatAmount(value: number | string | null | undefined): string {
            if (value == null) return '0';
            const num =
                typeof value === 'number'
                    ? value
                    : Number(String(value).replace(/[^\d.-]/g, ''));
            if (Number.isNaN(num)) return String(value);
            return num.toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
        },

async onDownloadPdf() {
    this.closeMoreMenu();

    const saleId = (this.primarySale as any)?.id;
    const filename = saleId ? `Invoice-${saleId}.pdf` : 'Invoice.pdf';

    try {
        const { default: html2pdf } = await import('html2pdf.js');

        // Create a temporary container
        const tempContainer = document.createElement('div');
        tempContainer.style.position = 'fixed';
        tempContainer.style.left = '-99999px';
        tempContainer.style.top = '0';
        tempContainer.style.width = '210mm';
        tempContainer.style.background = '#ffffff';
        
        // Get HTML content
        let htmlContent = this.buildExportHtml();
        
        // Clean ALL oklch colors from the entire HTML content
        htmlContent = this.cleanOklchColors(htmlContent);
        
        tempContainer.innerHTML = htmlContent;
        document.body.appendChild(tempContainer);

        // Additional cleanup: Remove any remaining oklch references
        this.removeAllOklchReferences(tempContainer);

        // Wait for images to load - IMPORTANT: Use absolute URL for logo
        const images = tempContainer.querySelectorAll('img');
        await Promise.all(
            Array.from(images).map((img) => {
                // Ensure logo URL is absolute
                if (img.src && !img.src.startsWith('http')) {
                    img.src = 'https://saptify.driftbarber.com/assets/images/Asset%202%201.png';
                }
                
                return img.complete
                    ? Promise.resolve()
                    : new Promise((resolve) => {
                        img.onload = () => {
                            console.log('Image loaded:', img.src);
                            resolve(true);
                        };
                        img.onerror = (err) => {
                            console.warn('Image failed to load:', img.src, err);
                            resolve(true); // Continue even if image fails
                        };
                    });
            })
        );

        // Wait a bit longer for everything to render
        await new Promise(resolve => setTimeout(resolve, 300));

        const invoiceElement = tempContainer.querySelector('.invoice-scope');
        if (!invoiceElement) {
            throw new Error('Invoice element not found');
        }

        // Temporarily adjust element for PDF generation
        const originalWidth = invoiceElement.style.width;
        invoiceElement.style.width = '100%';
        invoiceElement.style.maxWidth = 'none';

        await (html2pdf() as any)
            .set({
                filename,
                margin: [10, 10, 10, 10], // Reduced margins
                html2canvas: {
                    scale: 2,
                    backgroundColor: '#ffffff',
                    useCORS: true,
                    logging: true,
                    letterRendering: true,
                    allowTaint: true,
                    onclone: (clonedDoc: any) => {
                        // Clean oklch from cloned document
                        this.removeAllOklchReferences(clonedDoc);
                        
                        // Ensure all images have absolute URLs
                        const clonedImages = clonedDoc.querySelectorAll('img');
                        clonedImages.forEach((img: HTMLImageElement) => {
                            if (img.src && !img.src.startsWith('http')) {
                                img.src = 'https://saptify.driftbarber.com/assets/images/Asset%202%201.png';
                            }
                            // Add crossOrigin for CORS
                            img.crossOrigin = 'anonymous';
                        });
                        
                        // Adjust styling for PDF
                        const clonedInvoice = clonedDoc.querySelector('.invoice-scope');
                        if (clonedInvoice) {
                            clonedInvoice.style.padding = '0';
                            clonedInvoice.style.margin = '0';
                            clonedInvoice.style.width = '100%';
                            clonedInvoice.style.maxWidth = 'none';
                            
                            // Fix footer position
                            const footer = clonedInvoice.querySelector('.footer');
                            if (footer) {
                                footer.style.position = 'absolute';
                                footer.style.bottom = '0';
                            }
                        }
                    }
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'portrait',
                    compress: true
                },
            })
            .from(invoiceElement)
            .save();

        // Restore original width
        invoiceElement.style.width = originalWidth;

        // Cleanup
        tempContainer.remove();
    } catch (err) {
        console.error('Download PDF failed', err);
        alert((err as any)?.message || 'Could not download the PDF.');
    } finally {
        this.cleanupAfterPdf();
    }
},

    cleanOklchColors(html: string): string {
        // Define color replacements for common oklch values
        const colorReplacements = [
            // Neutral colors
            { pattern: /oklch\([^)]*0\.97[^)]*\)/g, replacement: '#f3f4f6' }, // gray-100
            { pattern: /oklch\([^)]*0\.96[^)]*\)/g, replacement: '#f8fafc' }, // very light gray
            { pattern: /oklch\([^)]*0\.92[^)]*\)/g, replacement: '#f1f5f9' }, // slate-100
            { pattern: /oklch\([^)]*0\.87[^)]*\)/g, replacement: '#e2e8f0' }, // slate-200
            { pattern: /oklch\([^)]*0\.71[^)]*\)/g, replacement: '#94a3b8' }, // slate-400
            { pattern: /oklch\([^)]*0\.24[^)]*\)/g, replacement: '#374151' }, // gray-700
            { pattern: /oklch\([^)]*0\.14[^)]*\)/g, replacement: '#111827' }, // gray-900
            
            // Brand/primary colors
            { pattern: /oklch\([^)]*0\.58[^)]*27\.325[^)]*\)/g, replacement: '#dc2626' }, // red-600
            { pattern: /oklch\([^)]*0\.28[^)]*256\.847[^)]*\)/g, replacement: '#3b82f6' }, // blue-500
            { pattern: /oklch\([^)]*0\.65[^)]*142\.5[^)]*\)/g, replacement: '#10b981' }, // emerald-500
            { pattern: /oklch\([^)]*0\.62[^)]*241\.997[^)]*\)/g, replacement: '#8b5cf6' }, // violet-500
            
            // Fallback for any remaining oklch
            { pattern: /oklch\([^)]+\)/g, replacement: '#111827' },
            
            // Also handle CSS custom properties that might contain oklch
            { pattern: /var\([^)]*oklch[^)]*\)/g, replacement: '#111827' },
        ];
        
        let cleanedHtml = html;
        
        // Replace all oklch occurrences
        colorReplacements.forEach(({ pattern, replacement }) => {
            cleanedHtml = cleanedHtml.replace(pattern, replacement);
        });
        
        // Also clean any class names containing oklch
        cleanedHtml = cleanedHtml.replace(/class="[^"]*oklch[^"]*"/g, 'class=""');
        
        return cleanedHtml;
    },
    
    removeAllOklchReferences(element: any) {
        if (!element) return;
        
        // Clean inline styles
        const elementsWithStyle = element.querySelectorAll('[style]');
        elementsWithStyle.forEach((el: any) => {
            const style = el.getAttribute('style');
            if (style && style.includes('oklch')) {
                const cleanedStyle = style.replace(/oklch\([^)]+\)/g, '#111827');
                el.setAttribute('style', cleanedStyle);
            }
        });
        
        // Clean style tags
        const styleTags = element.querySelectorAll('style');
        styleTags.forEach((style: any) => {
            if (style.textContent.includes('oklch')) {
                style.textContent = this.cleanOklchColors(style.textContent);
            }
        });
        
        // Clean any data attributes that might contain oklch
        const allElements = element.querySelectorAll('*');
        allElements.forEach((el: any) => {
            for (const attr of el.attributes) {
                if (attr.value && attr.value.includes('oklch')) {
                    el.removeAttribute(attr.name);
                }
            }
        });
    },

        onEditSales() {
            this.closeMoreMenu();

            const saleId =
                this.primarySale && (this.primarySale as any).id
                    ? (this.primarySale as any).id
                    : null;

            if (!saleId) return;

            this.$emit('edit-sales', saleId);
        },





buildExportHtml(): string {
    const currency = this.currencySymbol;
    const fmt = (v: number | string) => {
        const num = typeof v === 'number' ? v : Number(String(v).replace(/[^\d.-]/g, ''));
        if (Number.isNaN(num)) return '0.00';
        return num.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };
    
    // Format amounts with 2 decimals
    const fmt2 = (v: number | string) => {
        const num = typeof v === 'number' ? v : Number(String(v).replace(/[^\d.-]/g, ''));
        if (Number.isNaN(num)) return '0.00';
        return num.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    };
    
    // Invoice number formatting
    const invoiceNumber = this.primarySale && (this.primarySale as any).invoice_number
        ? (this.primarySale as any).invoice_number
        : `IN${String((this.primarySale as any)?.id || '001').padStart(3, '0')}`;
    
    // Booking ID
    const bookingId = (this.booking as any)?.id || '';
    
    // Staff name (issued by)
    const issuedBy = this.bookingStaffName || 'Staff';
    
    // Date formatting
    const createdDate = this.primarySale?.created_at 
        ? new Date(String((this.primarySale as any).created_at).replace(' ', 'T')).toLocaleDateString(undefined, {
            month: 'short',
            day: '2-digit',
            year: 'numeric'
        })
        : this.headerDateLabel;
    
    const createdTime = this.primarySale?.created_at 
        ? new Date(String((this.primarySale as any).created_at).replace(' ', 'T')).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        })
        : '';
    
    // Tax amount 
    const taxAmount = (this.primarySale as any)?.tax_amount || (this.primarySale as any)?.tax || 0;
    
    // Calculate discount
    const discountAmount = this.servicesDiscounts || 0;

    
    // Services rows
    const fallbackStaff = issuedBy;
    const servicesRows = this.services.map((svc: any) => {
        const serviceLabel = svc.label || 'Service';
        const rowStaff = svc.staffName || fallbackStaff;
        const lineTotal = svc.finalTotal || 0;
        
        return `
            <tr>
                <td>${serviceLabel}</td>
                <td class="center nowrap">${rowStaff}</td>
                <td class="right nowrap">${fmt2(lineTotal)}</td>
            </tr>
        `;
    }).join('');

    return `
<style data-receipt-style="1">
    /* DOMPDF-safe styling (avoid flex/grid; use tables + block layout) */
    @page { margin: 18mm 18mm 18mm 18mm; }
    
    .invoice-scope, .invoice-scope * {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
    font-size: 12px;
    color: #111827 !important;
    margin: 0;
    padding: 0;
    background: #ffffff !important;
    box-sizing: border-box;
}

    
    /* Layout helpers */
    .invoice-scope table { width: 100%; border-collapse: collapse; }
    .invoice-scope .right { text-align: right; }
    .invoice-scope .center { text-align: center; }
    .invoice-scope .nowrap { white-space: nowrap; }
    
    /* Header */
    .invoice-scope .header td { vertical-align: top; }
    .invoice-scope .title {
        font-size: 34px;
        font-weight: 700;
        margin: 0 0 6px 0;
        letter-spacing: -0.02em;
        color: #111827;
    }
    
    /* Meta (Invoice number + Booking id) */
    .invoice-scope .meta-table { width: auto; border-collapse: collapse; }
    .invoice-scope .meta-table td { padding: 2px 0; }
    .invoice-scope .meta-label {
        width: 115px;
        color: #9ca3af;
        font-size: 12px;
    }
    .invoice-scope .meta-value {
        color: #111827;
        font-weight: 700;
        font-size: 12px;
        padding-left: 18px;
        white-space: nowrap;
    }
    
    .invoice-scope .logo-wrap { text-align: right !important;
    vertical-align: top !important; }
    .invoice-scope .logo {
        width: 84px !important;
        height: 84px !important;
        object-fit: contain !important;
        display: block !important;
        margin-left: auto !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Cards */
    .invoice-scope .card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }
    .invoice-scope .card-pad { padding: 14px 16px; }
    
    .invoice-scope .two-col td { vertical-align: top; padding: 14px 16px; }
    .invoice-scope .two-col .sep { width: 1px; padding: 0; background: #e5e7eb; }
    
    .invoice-scope .small-label {
        font-size: 12px;
        color: #9ca3af;
        margin: 0 0 6px 0;
    }
    .invoice-scope .strong { font-weight: 700; color: #111827; line-height: 1.4; }
    .invoice-scope .muted { color: #6b7280; }

    .invoice-scope td .strong {
    display: block;
    line-height: 1.4;
    margin-bottom: 2px;
}
.invoice-scope .two-col td { 
    vertical-align: top; 
    padding: 16px 18px; /* Increased padding slightly */
}
    
    /* Services table box */
    .invoice-scope .box {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    .invoice-scope .svc thead th {
        background: #f3f4f6 !important;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }
    .invoice-scope .svc thead th.center { text-align: center; }
    .invoice-scope .svc thead th.right { text-align: right; }
    
    .invoice-scope .svc tbody td {
        padding: 12px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
    }
    .invoice-scope .svc tbody tr:last-child td { border-bottom: none; }

        /* Add this important rule to catch any stray oklch */
   .invoice-scope *[style*="oklch"], 
.invoice-scope *[class*="oklch"] {
    background-color: transparent !important;
    color: inherit !important;
}

    
    /* Column alignment */
    .invoice-scope .col-service { width: 55%; }
    .invoice-scope .col-staff { width: 25%; }
    .invoice-scope .col-total { width: 20%; }
    
    /* Totals (right card) */
    .invoice-scope .totals-wrap { width: 100%; margin-top: 16px; }
    .invoice-scope .totals-table td { padding: 7px 0; }
    .invoice-scope .totals-table .label { color: #6b7280; }
    .invoice-scope .totals-table .val { text-align: right; white-space: nowrap; }
    .invoice-scope .totals-divider { border-top: 1px solid #e5e7eb; margin: 12px 0; }
    .invoice-scope .grand { font-weight: 700; font-size: 13px; }
    
    .invoice-scope .notes-rule { border-top: 1px solid #e5e7eb; margin: 6px 0 12px 0; }
    
    /* Footer bar */
  /* make invoice act as positioning parent */
.invoice-scope { 
    position: relative; 
    padding-bottom: 22mm; /* keeps content from overlapping footer */
}

/* footer should NOT be fixed to viewport */
.invoice-scope .footer {
    position: absolute;
    left: -18mm;
    right: -18mm;
    bottom: 0;
    height: 20mm;
    background: #c45b3a !important;
    color: #ffffff !important;
    font-size: 13px;
    line-height: 14mm;
}

    .invoice-scope .footer table { width: 100%; height: 14mm; border-collapse: collapse; }
    .invoice-scope .footer td { padding: 0 18mm; vertical-align: middle; line-height: 14mm; }
    .invoice-scope .footer .right { text-align: right; }

        /* CRITICAL FIX: Ensure logo is never hidden */
  .invoice-scope div[style*="position: fixed"][style*="left: -9999px"] {
    display: none !important;
}

    
    .invoice-scope img[src*="Asset%202%201.png"] {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        max-width: 100% !important;
        height: auto !important;
    }
</style>

<div class="invoice-scope">
    <!-- Logo URL - update with your actual logo -->
    <div style="position: fixed; left: -9999px;">
        <img src="https://saptify.driftbarber.com/assets/images/Asset%202%201.png" 
             onload="this.parentNode.remove()" />
    </div>

    <!-- HEADER -->
    <table class="header">
        <tr>
            <td style="width: 82%;">
                <div class="title">Invoice</div>
                
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Invoice Number</td>
                        <td class="meta-value">${invoiceNumber}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Booking Id</td>
                        <td class="meta-value">${bookingId}</td>
                    </tr>
                </table>
            </td>
            
            <td class="logo-wrap" style="width: 28%;">
                <img class="logo" 
                     src="https://saptify.driftbarber.com/assets/images/Asset%202%201.png" 
                     alt="Logo">
            </td>
        </tr>
    </table>
    
    <div style="height: 18px;"></div>
    
    <!-- BILLED TO / ISSUED BY -->
    <div class="card">
        <table class="two-col">
            <tr>
                <td style="width: 58%;">
                    <div class="small-label">Billed To</div>
                    <div class="strong" style="margin-bottom: 6px;">${this.clientName}</div>
                    ${this.clientEmail ? `<div style="margin-bottom: 3px;">${this.clientEmail}</div>` : ''}
                    ${this.clientPhone ? `<div>${this.clientPhone}</div>` : ''}
                </td>
                
                <td class="sep"></td>
                
                <td style="width: 42%;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td class="small-label" style="padding:5px; width: 70px;">Issued By</td>
                            <td style="padding:5px; " class="strong">${issuedBy}</td>
                        </tr>
                        <tr><td style="height: 12px;"></td><td></td></tr>
                        <tr>
                            <td class="small-label" style="padding:5px; width: 70px; vertical-align: top;">Created</td>
                            <td style="padding:5px;">
                                <div class="strong" style="line-height: 1.4; margin-bottom: 3px;">${createdDate}</div>
                                <div class="strong" style="line-height: 1.4;">${createdTime}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    
    <div style="height: 16px;"></div>
    
    <!-- SERVICES TABLE -->
    <div class="box">
        <table class="svc">
            <thead>
                <tr>
                    <th class="col-service">Service</th>
                    <th class="col-staff center nowrap">Staff</th>
                    <th class="col-total right nowrap">Total (${currency})</th>
                </tr>
            </thead>
            <tbody>
                ${servicesRows || '<tr><td class="muted" colspan="3">—</td></tr>'}
            </tbody>
        </table>
    </div>
    
    <!-- TOTALS (RIGHT) -->
    <div class="totals-wrap">
        <table>
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%;">
                    <div class="card card-pad">
                        <table class="totals-table">
                            <tr>
                                <td class="label">Subtotal</td>
                                <td class="val">${currency} ${fmt2(this.subtotal)}</td>
                            </tr>
                            ${discountAmount > 0 ? `
                            <tr>
                                <td class="label">Discount</td>
                                <td class="val">-${currency} ${fmt2(discountAmount)}</td>
                            </tr>
                            ` : ''}
                            ${taxAmount > 0 ? `
                            <tr>
                                <td class="label">Tax</td>
                                <td class="val">${currency} ${fmt2(taxAmount)}</td>
                            </tr>
                            ` : ''}
                            ${this.tipAmount > 0 ? `
                            <tr>
                                <td class="label">Tip</td>
                                <td class="val">${currency} ${fmt2(this.tipAmount)}</td>
                            </tr>
                            ` : ''}
                        </table>
                        
                        <div class="totals-divider"></div>
                        
                        <table>
                            <tr>
                                <td class="grand">Total</td>
                                <td class="right nowrap grand">${currency} ${fmt2(this.totalWithTip)}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    

    
    <!-- FOOTER BAR -->
    <div class="footer">
        <table>
            <tr>
                <td class="nowrap"></td>
                <td class="right nowrap"></td>
            </tr>
        </table>
    </div>
</div>
`;
},

        cleanupAfterPdf() {
            try {
                document
                    .querySelectorAll('.html2canvas-container')
                    .forEach((el) => el.parentNode?.removeChild(el));

                document
                    .querySelectorAll('.html2pdf__overlay, .html2pdf__progress')
                    .forEach((el) => el.parentNode?.removeChild(el));

                document
                    .querySelectorAll('.modal-backdrop')
                    .forEach((el) => el.parentNode?.removeChild(el));

                document.body.classList.remove('modal-open');
                (document.body as HTMLElement).style.pointerEvents = '';
            } catch (e) {
                console.warn('cleanupAfterPdf failed', e);
            }
        },
        handleClickOutside(event) {
            const moreButton = this.$el.querySelector('button[aria-label="More options"]') ||
                this.$el.querySelector('button:has(.bx-dots-vertical-rounded)');
            const dropdown = this.$refs.moreDropdown;

            if (this.moreMenuOpen &&
                dropdown &&
                !dropdown.contains(event.target) &&
                moreButton &&
                !moreButton.contains(event.target)) {
                this.closeMoreMenu();
            }
        },
 async onExportPdf() {
  this.closeMoreMenu();

  const saleId = (this.primarySale as any)?.id;
  const filenameBase = saleId ? `Sale-${saleId}` : 'Sale';

  await this.openReceiptPrintDialog(filenameBase);
},


        async onExportExcel() {
            this.closeMoreMenu();

            try {
                const xlsx: any = await import('xlsx');
                const wb = xlsx.utils.book_new();

                const rows: any[] = [];

                rows.push(['Sale', this.saleNumberLabel]);
                rows.push(['Date', this.headerDateLabel]);
                rows.push(['Client', this.clientName]);
                if (this.clientEmail) rows.push(['Email', this.clientEmail]);
                if (this.clientPhone) rows.push(['Phone', this.clientPhone]);
                rows.push([]);
                rows.push(['#', 'Service', 'Duration', 'Staff', 'Total']);

                this.services.forEach((svc: any, idx: number) => {
                    rows.push([
                        idx + 1,
                        svc.label || '',
                        svc.durationLabel || '',
                        svc.staffName || '',
                        Number(svc.total) || 0,
                    ]);
                });

                rows.push([]);
                rows.push(['', '', '', 'Subtotal', this.subtotal]);
                if (this.tipAmount > 0) {
                    rows.push(['', '', '', 'Tip', this.tipAmount]);
                }
                rows.push(['', '', '', 'Total', this.totalWithTip]);
                rows.push(['', '', '', 'Total Paid', this.totalPaid]);

                const ws = xlsx.utils.aoa_to_sheet(rows);
                xlsx.utils.book_append_sheet(wb, ws, 'Sale');

                const filename =
                    (this.primarySale && (this.primarySale as any).id
                        ? `Sale-${(this.primarySale as any).id}`
                        : 'sale') + '.xlsx';

                xlsx.writeFile(wb, filename);
            } catch (err) {
                console.error('Failed to export Excel', err);
                alert(
                    'Excel export requires the "xlsx" package. Please install it first.',
                );
            }
        },
    },
});
</script>

<style scoped>
.completed-offcanvas-enter-active,
.completed-offcanvas-leave-active {
    transition:
        opacity 0.16s ease,
        transform 0.16s ease;
}

.completed-offcanvas-enter-from,
.completed-offcanvas-leave-to {
    opacity: 0;
}

.completed-offcanvas-enter-from>div:last-child {
    transform: translateX(100%);
}

.completed-offcanvas-leave-to>div:last-child {
    transform: translateX(100%);
}

.tabular-nums {
    font-variant-numeric: tabular-nums;
}

/* simple dropdown transition */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.16s ease, transform 0.16s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px) scale(0.98);
}
</style>
