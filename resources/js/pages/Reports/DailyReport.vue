<template>
    <div>
        <Head title="Daily sale report" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <!-- Export toast -->
            <div v-if="exportToast.visible" class="fixed top-4 inset-x-0 z-50 flex justify-center pointer-events-none">
                <div class="pointer-events-auto flex items-center gap-3 rounded-full bg-black px-4 py-2 text-sm text-white shadow-lg">
                    <span>{{ exportToast.message }}</span>

                    <span
                        v-if="exportToast.isLoading"
                        class="h-4 w-4 rounded-full border border-white/40 border-t-white animate-spin"
                    ></span>

                    <button
                        type="button"
                        class="ml-1 flex items-center justify-center rounded-full p-1 text-xs opacity-70 hover:opacity-100"
                        @click="exportToast.visible = false"
                    >
                        <i class="bx bx-x text-lg"></i>
                    </button>
                </div>
            </div>

            <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div class="relative rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900">
                    <!-- HEADER -->
                    <div
                        class="flex items-center justify-between gap-3 flex-wrap
                               border-b border-zinc-200 px-4 py-3
                               dark:border-zinc-700/60"
                    >
                        <div>
                            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Daily report (Sales with Team)
                            </h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                View team performance for a selected day (services, net totals, and tips).
                            </p>
                        </div>

                        <Link class="btn-secondary mt-3 sm:mt-0" :href="route('reports.index')">
                            <i class="bx bx-left-arrow-alt mr-1" />
                            Back to reports
                        </Link>
                    </div>

                    <!-- BODY -->
                    <div class="p-5 space-y-4">
                        <!-- Filters row -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <!-- LEFT: date + today + branch + apply -->
                            <div class="flex flex-wrap gap-3 items-end">

                                 <button  type="button"
  class="btn-secondary h-10 min-w-[9rem] !rounded-full px-4 inline-flex items-center justify-center"
   @click="setToday">
                                    Today
                                </button>
                                <!-- Date (single day) -->
                                <div class="flex flex-col relative">
                                    <label class="mb-1 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                        Date
                                    </label>

                                    <VueDatePicker
                                        v-model="selectedDate"
                                        :enable-time-picker="false"
                                        :clearable="false"
                                        :close-on-auto-apply="true"
                                        :auto-apply="true"
                                        class="inline-block"
                                    >
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-full border border-zinc-300
                                                       bg-white px-4 py-2 text-sm text-zinc-700 shadow-sm
                                                       hover:border-orange-500
                                                       focus:outline-none focus:ring-2 focus:ring-indigo-500/50
                                                       dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-100 cursor-pointer"
                                            >
                                                <span>{{ dateLabel }}</span>
                                                <i class="bx bx-calendar text-lg"></i>
                                            </button>
                                        </template>
                                    </VueDatePicker>
                                </div>

                                <!-- Today button -->
                                <!-- <button  type="button"
  class="btn-secondary h-10 min-w-[9rem] !rounded-full px-4 inline-flex items-center justify-center"
   @click="setToday">
                                    Today
                                </button> -->

                                <!-- Branch dropdown -->
                                <div class="flex flex-col relative z-30">
                                    <label class="mb-1 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                                        Branch
                                    </label>

                                    <button
                                        type="button"
                                        class="inline-flex h-10 items-center justify-between rounded-full border border-zinc-300 px-4
                                               text-sm text-zinc-800 bg-white
                                               focus:border-orange-500 focus:ring focus:ring-orange-200
                                               dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer min-w-[9rem]"
                                        @click.stop="toggleBranchDropdown"
                                    >
                                        <span class="truncate">{{ selectedBranchLabel }}</span>
                                        <i class="bx bx-chevron-down ml-2 text-base"></i>
                                    </button>

                                    <div
                                        v-if="branchDropdownOpen"
                                        class="absolute left-0 top-full mt-2 w-full rounded-xl border border-zinc-200 bg-white py-1 shadow-lg
                                               z-50 dark:border-zinc-700 dark:bg-zinc-900"
                                        @click.stop
                                    >
                                        <button
                                            type="button"
                                            class="flex w-full items-center px-3 py-2 text-left text-sm
                                                   text-zinc-700 hover:bg-zinc-100
                                                   dark:text-zinc-100 dark:hover:bg-zinc-800/80 rounded-lg"
                                            @click="selectBranch('')"
                                        >
                                            All branches
                                        </button>

                                        <button
                                            v-for="branch in branches"
                                            :key="branch.id"
                                            type="button"
                                            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm
                                                   text-zinc-700 hover:bg-zinc-100
                                                   dark:text-zinc-100 dark:hover:bg-zinc-800/80 rounded-lg"
                                            @click="selectBranch(branch.id)"
                                        >
                                            <span class="truncate">{{ branch.name }}</span>
                                            <span
                                                v-if="String(filtersLocal.branch_id) === String(branch.id)"
                                                class="ml-2 h-2 w-2 rounded-full bg-orange-500"
                                            ></span>
                                        </button>
                                    </div>
                                </div>

                                                  <button
  type="button"
  class="btn-primary h-10 min-w-[9rem] !rounded-full px-4 inline-flex items-center justify-center"
  @click="applyFilters"
>
  Apply filters
</button>
                            </div>

                            <!-- RIGHT: Options dropdown -->
                            <div class="flex w-full sm:w-auto items-center justify-end sm:justify-start">
                                <div class="relative w-full sm:w-auto">
                                    <button
                                        type="button"
                                        class="inline-flex w-full sm:w-auto items-center justify-center gap-1 h-10
                                               rounded-full border border-zinc-300 px-4 cursor-pointer
                                               text-sm text-zinc-800
                                               focus:border-orange-500 focus:ring focus:ring-orange-200
                                               dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                        @click.stop="toggleOptions"
                                    >
                                        <i class="bx bx-dots-vertical-rounded text-lg"></i>
                                        <span>Options</span>
                                        <i class="bx bx-chevron-down text-xs"></i>
                                    </button>

                                    <div
                                        v-if="optionsOpen"
                                        class="absolute right-0 z-20 mt-2 w-full sm:w-40 max-w-[90vw]
                                               rounded-lg border border-zinc-200 bg-white py-2 text-sm shadow-lg
                                               dark:border-zinc-700 dark:bg-zinc-800"
                                        @click.stop
                                    >
                                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide
                                                  text-zinc-500 dark:text-zinc-400">
                                            Export
                                        </p>

                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2 px-3 py-1.5 text-left
                                                   text-zinc-700 hover:bg-zinc-100
                                                   dark:text-zinc-100 dark:hover:bg-zinc-700/80 cursor-pointer"
                                            @click="exportExcel"
                                        >
                                            <i class="bx bx-spreadsheet text-base" />
                                            <span>Excel</span>
                                        </button>

                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-2 px-3 py-1.5 text-left
                                                   text-zinc-700 hover:bg-zinc-100
                                                   dark:text-zinc-100 dark:hover:bg-zinc-700/80 cursor-pointer"
                                            @click="exportPdf"
                                        >
                                            <i class="bx bxs-file-pdf text-base" />
                                            <span>PDF</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <!-- Summary cards -->
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="summary-card">
                                <span class="accent"></span>
                                <p class="kicker">Total appointments</p>
                                <p class="value">{{ summary.totalAppointments }}</p>
                            </div>

                            <div class="summary-card">
                                <span class="accent"></span>
                                <p class="kicker">Total sales (services)</p>
                                <p class="value">{{ summary.totalSales }}</p>
                            </div>

                            <div class="summary-card">
                                <span class="accent"></span>
                                <p class="kicker">Net total</p>
                                <p class="value">{{ currencySymbol }} {{ formatNumber(summary.netTotal) }}</p>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="mt-8">
                            <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700/60 dark:bg-zinc-900/60">
                                <span class="absolute inset-y-0 left-0 w-1.5 bg-orange-500"></span>

                                <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3 dark:border-zinc-700/60">
                                    <h6 class="text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">
                                        Daily sales by team member
                                    </h6>

                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ rows.length }} {{ rows.length === 1 ? 'row' : 'rows' }}
                                    </p>
                                </div>

                                <div v-if="rows.length" class="overflow-x-auto">
                                    <table class="min-w-[900px] w-full text-sm border-separate border-spacing-y-2">
                                        <thead>
                                            <tr class="bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                                                <th class="py-2 pl-4 pr-4 text-left whitespace-nowrap">Team member</th>
                                                <th class="py-2 pr-4 text-right whitespace-nowrap">Sales</th>
                                                <th class="py-2 pr-4 text-right whitespace-nowrap">Net total (LKR)</th>
                                                <th class="py-2 pr-4 text-right whitespace-nowrap">Tip (LKR)</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr
                                                v-for="r in rows"
                                                :key="r.staff_id"
                                                class="bg-white dark:bg-zinc-900 rounded-lg shadow-sm hover:bg-zinc-50/80 dark:hover:bg-zinc-800/60"
                                            >
                                                <td class="py-2 pl-4 pr-4 whitespace-nowrap font-semibold text-zinc-900 dark:text-zinc-100">
                                                    {{ r.team_member || '—' }}
                                                </td>

                                                <td class="py-2 pr-4 whitespace-nowrap text-right text-zinc-700 dark:text-zinc-100">
                                                    {{ Number(r.sales || 0).toLocaleString() }}
                                                </td>

                                                <td class="py-2 pr-4 whitespace-nowrap text-right font-semibold text-zinc-900 dark:text-zinc-100">
                                                    {{ currencySymbol }} {{ formatNumber(r.net_total) }}
                                                </td>

                                                <td class="py-2 pr-4 whitespace-nowrap text-right text-zinc-700 dark:text-zinc-100">
                                                    {{ currencySymbol }} {{ formatNumber(r.tip) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <p v-else class="px-4 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                    No sales found for this date.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    </div>
</template>

<script lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { BreadcrumbItem } from '@/types'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

export default {
    name: 'DailySaleReportPage',
    components: { AppLayout, Head, Link, VueDatePicker },

    props: {
        rows: { type: Array, default: () => [] },
        summary: {
            type: Object,
            default: () => ({
                totalAppointments: 0,
                totalSales: 0,
                netTotal: 0,
            }),
        },
        filters: {
            type: Object,
            default: () => ({
                date: '',
                branch_id: '',
            }),
        },
        branches: { type: Array, default: () => [] },
    },

    data() {
        const d = this.filters?.date ? new Date(this.filters.date) : new Date()

        return {
            breadcrumbs: [
                { title: 'Reports', href: route('reports.index') },
                { title: 'Daily sale report', href: route('reports.sales_team.daily') },
            ] as BreadcrumbItem[],

            currencySymbol: 'Rs',

            filtersLocal: {
                date: this.filters.date || this.formatForBackend(new Date()),
                branch_id: this.filters.branch_id || '',
            },

            selectedDate: d as any,

            branchDropdownOpen: false,
            optionsOpen: false,

            exportToast: {
                visible: false,
                message: '',
                isLoading: false,
            },
        }
    },

    computed: {
        dateLabel(): string {
            if (!this.selectedDate) return 'Select date'
            return this.formatDisplayDate(this.selectedDate, true)
        },

        selectedBranchLabel(): string {
            if (!this.filtersLocal.branch_id) return 'All branches'

            const current = (this as any).branches.find(
                (b: any) => String(b.id) === String(this.filtersLocal.branch_id),
            )
            return current?.name || 'All branches'
        },
    },

    watch: {
        selectedDate: {
            handler(newVal: any) {
                if (newVal) this.filtersLocal.date = this.formatForBackend(newVal as Date)
            },
            deep: true,
        },

        filters: {
            handler(newFilters: any) {
                const d = newFilters.date ? new Date(newFilters.date) : new Date()

                this.filtersLocal = {
                    date: newFilters.date || this.formatForBackend(new Date()),
                    branch_id: newFilters.branch_id || '',
                }

                this.selectedDate = d as any
            },
            deep: true,
        },
    },

    methods: {
        setToday() {
            const t = new Date()
            this.selectedDate = t as any
            this.filtersLocal.date = this.formatForBackend(t)
            this.applyFilters()
        },

        applyFilters() {
            const query: Record<string, string> = {}
            if (this.filtersLocal.date) query.date = this.filtersLocal.date
            if (this.filtersLocal.branch_id) query.branch_id = this.filtersLocal.branch_id

            ;(this as any).$inertia.get(route('reports.sales_team.daily'), query, {
                preserveState: true,
                preserveScroll: true,
            })
        },

        toggleBranchDropdown() {
            this.branchDropdownOpen = !this.branchDropdownOpen
        },

        selectBranch(branchId: string | number) {
            this.filtersLocal.branch_id = branchId ? String(branchId) : ''
            this.branchDropdownOpen = false
        },

        toggleOptions() {
            this.optionsOpen = !this.optionsOpen
        },

        formatForBackend(date: Date): string {
            const year = date.getFullYear()
            const month = String(date.getMonth() + 1).padStart(2, '0')
            const day = String(date.getDate()).padStart(2, '0')
            return `${year}-${month}-${day}`
        },

        formatDisplayDate(date: Date, includeYear = true): string {
            const options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short' }
            if (includeYear) options.year = 'numeric'
            return date.toLocaleDateString(undefined, options)
        },

        formatNumber(value: number | string) {
            const num = Number(value) || 0
            return num.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        },

        showExportToast(message = 'Export in progress') {
            this.exportToast.message = message
            this.exportToast.isLoading = true
            this.exportToast.visible = true
        },

        showExportResult(message: string, autoHideMs = 1800) {
            this.exportToast.message = message
            this.exportToast.isLoading = false
            this.exportToast.visible = true

            if (autoHideMs > 0) {
                setTimeout(() => (this.exportToast.visible = false), autoHideMs)
            }
        },

        async exportExcel() {
            this.optionsOpen = false

            const query: Record<string, string> = {}
            if (this.filtersLocal.date) query.date = this.filtersLocal.date
            if (this.filtersLocal.branch_id) query.branch_id = this.filtersLocal.branch_id

            const url = route('reports.sales_team.daily.excel', query)
            this.showExportToast('Export in progress')

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel',
                    },
                    credentials: 'same-origin',
                })

                if (!response.ok) {
                    this.showExportResult('Export failed', 2500)
                    return
                }

                const blob = await response.blob()
                const disposition = response.headers.get('Content-Disposition') || ''
                let filename = 'daily-report.xlsx'
                const match = disposition.match(/filename="?([^"]+)"?/i)
                if (match && match[1]) filename = decodeURIComponent(match[1])

                const blobUrl = window.URL.createObjectURL(blob)
                const link = document.createElement('a')
                link.href = blobUrl
                link.download = filename
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(blobUrl)

                this.showExportResult('Export complete', 1500)
            } catch (e) {
                this.showExportResult('Export failed', 2500)
            }
        },

        async exportPdf() {
            this.optionsOpen = false

            const query: Record<string, string> = {}
            if (this.filtersLocal.date) query.date = this.filtersLocal.date
            if (this.filtersLocal.branch_id) query.branch_id = this.filtersLocal.branch_id

            const url = route('reports.sales_team.daily.pdf', query)
            this.showExportToast('Export in progress')

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/pdf',
                    },
                    credentials: 'same-origin',
                })

                if (!response.ok) {
                    this.showExportResult('Export failed', 2500)
                    return
                }

                const blob = await response.blob()
                const disposition = response.headers.get('Content-Disposition') || ''
                let filename = 'daily-report.pdf'
                const match = disposition.match(/filename="?([^"]+)"?/i)
                if (match && match[1]) filename = decodeURIComponent(match[1])

                const blobUrl = window.URL.createObjectURL(blob)
                const link = document.createElement('a')
                link.href = blobUrl
                link.download = filename
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(blobUrl)

                this.showExportResult('Export complete', 1500)
            } catch (e) {
                this.showExportResult('Export failed', 2500)
            }
        },

        handleOutsideClick(e: MouseEvent) {
            const target = e.target as HTMLElement
            if (!target) return
            // close dropdowns if clicking outside
            this.branchDropdownOpen = false
            this.optionsOpen = false
        },
    },

    mounted() {
        // default is already today from server, but UI "Today" is effectively selected
        document.addEventListener('click', this.handleOutsideClick)
    },

    beforeUnmount() {
        document.removeEventListener('click', this.handleOutsideClick)
    },
}
</script>

<style scoped>
.summary-card {
    position: relative;
    overflow: hidden;
    border-radius: 0.75rem;
    border: 1px solid rgb(228 228 231);
    background: #fff;
    padding: 1rem 1rem 1rem 1.5rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
:global(.dark) .summary-card {
    border-color: rgba(63, 63, 70, 0.6);
    background: rgba(24, 24, 27, 0.6);
}
.summary-card .accent {
    position: absolute;
    inset: 0 auto 0 0;
    width: 0.375rem;
    background: #f97316;
}
.summary-card .kicker {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgb(113 113 122);
}
:global(.dark) .summary-card .kicker {
    color: rgb(161 161 170);
}
.summary-card .value {
    margin-top: 0.5rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: rgb(24 24 27);
}
:global(.dark) .summary-card .value {
    color: rgb(244 244 245);
}
</style>
