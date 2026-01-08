<template>
    <div>
        <Head title="Reports" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div
                    class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm
                           dark:border-zinc-700/60 dark:bg-zinc-900"
                >
                    <!-- HEADER -->
                    <div
                        class="flex items-center justify-between gap-3 flex-wrap
                               border-b border-zinc-200 px-4 py-3
                               dark:border-zinc-700/60"
                    >
                        <div>
                            <h5 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Reports
                            </h5>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                Manage reports and their visibility.
                            </p>
                        </div>
                    </div>

                    <!-- TOP NAV TABS (improved design) -->
                    <div class="border-b border-zinc-200 dark:border-zinc-700/60 ">
                        <div class="px-4 py-3">
                            <div class="tabs-shell relative">
                                <!-- subtle background bar -->
                                <div
                                    class="pointer-events-none absolute inset-0 rounded-xl
                                           bg-zinc-50/70 dark:bg-zinc-800/40 "
                                ></div>

                                <div class="tabs-scroll relative flex items-center gap-1 overflow-x-auto p-1 ">
                                    <button
                                        v-for="t in tabs"
                                        :key="t.key"
                                        type="button"
                                        @click="setTab(t.key)"
                                        class="tab-btn shrink-0"
                                        :class="activeTab === t.key ? 'is-active' : 'is-inactive'"
                                    >
                                        <span class="relative z-10">{{ t.label }}</span>
                                    </button>
                                </div>

                                <!-- mobile edge fade -->
                                <div class="pointer-events-none absolute right-0 top-0 h-full w-10 bg-gradient-to-l from-white dark:from-zinc-900"></div>
                            </div>
                        </div>
                    </div>

                    <!-- BODY -->
                    <div class="p-5">
                        <Transition name="tab-swap" mode="out-in">
                            <div :key="activeTab" class="space-y-6">
                                <!-- CARDS GRID (per tab) -->
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="card in cardsForActiveTab"
                                        :key="card.key"
                                        @click="card.onClick()"
                                        class="group relative flex flex-col justify-between rounded-2xl
                                               border border-zinc-200 dark:border-zinc-700/60
                                               bg-white dark:bg-zinc-900
                                               hover:bg-zinc-50 dark:hover:bg-zinc-800
                                               transition-all duration-200 p-4 cursor-pointer"
                                    >
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <h4 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                                                    {{ card.title }}
                                                </h4>
                                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2">
                                                    {{ card.description }}
                                                </p>
                                            </div>

                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full"
                                                :class="card.iconWrapClass"
                                            >
                                                <i :class="card.iconClass + ' text-xl'"></i>
                                            </div>
                                        </div>

                                        <div
                                            class="mt-3 inline-flex items-center text-sm font-medium
                                                   text-indigo-600 dark:text-indigo-400"
                                        >
                                            <span>Open report</span>
                                            <i class="bx bx-right-arrow-alt ml-1 text-lg transition-transform duration-200 group-hover:translate-x-0.5"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-zinc-200 dark:border-zinc-700/60"></div>

                                <!-- Report list (keeps your existing management UI) -->
                                <div class="space-y-4">
                                    <div
                                        v-for="item in displayedReports"
                                        :key="item.id"
                                        @click="goToEdit(item.id)"
                                        class="group relative flex items-center gap-4 rounded-2xl
                                               border border-zinc-200 dark:border-zinc-700/60
                                               bg-white dark:bg-zinc-900
                                               hover:bg-zinc-50 dark:hover:bg-zinc-800
                                               transition-colors p-4 cursor-pointer"
                                    >
                                        <!-- IMAGE / PREVIEW -->
                                        <div
                                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden
                                                   bg-zinc-200 dark:bg-zinc-800
                                                   flex-shrink-0 flex items-center justify-center"
                                        >
                                            <img
                                                v-if="item.media && item.media.length && item.media[0].original_url"
                                                :src="item.media[0].original_url"
                                                class="object-cover w-full h-full"
                                            />
                                            <div v-else class="flex flex-col items-center text-zinc-400">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-8 w-8 text-zinc-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm3 0l3 3 2-2 4 4 3-3"
                                                    />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- LEFT DETAILS -->
                                        <div class="flex-1 min-w-0">
                                            <h4
                                                class="text-base sm:text-lg font-semibold text-zinc-900
                                                       dark:text-zinc-100 capitalize truncate"
                                            >
                                                {{ item.report_name }}
                                            </h4>

                                            <p
                                                class="text-sm text-zinc-600 dark:text-zinc-400
                                                       mt-1 line-clamp-2"
                                            >
                                                {{ item.report_description }}
                                            </p>
                                        </div>

                                        <!-- RIGHT SIDE: Status + Dots -->
                                        <div class="flex items-center gap-2 sm:gap-3 relative" @click.stop>
                                            <!-- STATUS BADGE -->
                                            <span
                                                :class="item.status === ACTIVE_VALUE
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                                                    : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'"
                                                class="text-xs px-3 py-1 font-semibold rounded-full whitespace-nowrap"
                                            >
                                                {{ item.status === ACTIVE_VALUE ? 'Active' : 'Inactive' }}
                                            </span>

                                            <!-- THREE DOTS BUTTON -->
                                            <button
                                                class="three-dots-btn p-2 rounded-lg
                                                       hover:bg-zinc-200 dark:hover:bg-zinc-700
                                                       transition"
                                                @click="toggleDropdown(item.id)"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-6 w-6 text-zinc-600 dark:text-zinc-300"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <circle cx="12" cy="5" r="1.5" />
                                                    <circle cx="12" cy="12" r="1.5" />
                                                    <circle cx="12" cy="19" r="1.5" />
                                                </svg>
                                            </button>

                                            <!-- DROPDOWN -->
                                            <div
                                                v-if="dropdownOpen === item.id"
                                                class="dropdown-menu absolute right-0 top-10 z-50 w-40 rounded-xl shadow-lg
                                                       bg-white dark:bg-zinc-800
                                                       border border-zinc-200 dark:border-zinc-700"
                                            >
                                                <button
                                                    class="w-full text-left px-4 py-2 text-sm
                                                           hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                    @click="goToEdit(item.id)"
                                                >
                                                    Edit
                                                </button>

                                                <button
                                                    class="w-full text-left px-4 py-2 text-sm text-rose-600
                                                           hover:bg-rose-50 dark:text-rose-400
                                                           dark:hover:bg-rose-900/40"
                                                    @click="openDeleteModal(item)"
                                                >
                                                    Delete
                                                </button>

                                                <button
                                                    class="w-full text-left px-4 py-2 text-sm
                                                           hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                    @click="onToggleChange(item, item.status !== ACTIVE_VALUE)"
                                                >
                                                    {{ item.status === ACTIVE_VALUE ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- empty state -->
                                    <!-- <div
                                        v-if="!displayedReports.length"
                                        class="rounded-2xl border border-dashed border-zinc-300 dark:border-zinc-700
                                               bg-zinc-50 dark:bg-zinc-900/40 p-8 text-center"
                                    >
                                        <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                            No reports found in this tab.
                                        </p>
                                    </div> -->
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </AppLayout>

        <!-- DELETE MODAL -->
        <Transition name="overlay-fade">
            <div
                v-if="reportDeleteOpen"
                class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50"
                @click.self="closeDeleteModal"
            >
                <Transition name="modal-pop" appear>
                    <div
                        v-show="reportDeleteOpen"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl
                               dark:bg-zinc-900"
                    >
                        <div class="mb-4 flex items-start justify-between">
                            <h4 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                                Permanently delete report
                            </h4>
                            <button
                                @click="closeDeleteModal"
                                class="p-1 text-zinc-500 hover:bg-zinc-100
                                       dark:hover:bg-zinc-800 rounded-full"
                            >
                                <i class="bx bx-x text-2xl"></i>
                            </button>
                        </div>

                        <p class="text-base text-zinc-700 dark:text-zinc-200 font-medium">
                            {{ reportDeleteMeta.report_name }}
                        </p>
                        <p class="mt-2 text-base text-zinc-600 dark:text-zinc-300">
                            This report and its related data will be permanently deleted.
                        </p>

                        <div class="flex justify-end gap-3 mt-6">
                            <button class="btn-secondary" @click="closeDeleteModal">
                                Cancel
                            </button>
                            <button
                                class="btn-primary bg-rose-600 hover:bg-rose-700 text-white"
                                :disabled="deletingReport"
                                @click="confirmDeleteReport"
                            >
                                {{ deletingReport ? 'Deleting…' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<script lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import type { BreadcrumbItem } from '@/types'

type TabKey = 'all' | 'appointment' | 'finance' | 'clients' | 'sales_team'

type CardDef = {
    key: string
    title: string
    description: string
    iconClass: string
    iconWrapClass: string
    onClick: () => void
}

export default {
    name: 'ReportsPage',
    components: { AppLayout, Head },
    props: {
        reports: Array,
    },
    data() {
        return {
            breadcrumbs: [{ title: 'Reports', href: route('reports.index') }] as BreadcrumbItem[],

            dropdownOpen: null as number | null,
            localReports: [] as any[],

            ACTIVE_VALUE: 1,
            INACTIVE_VALUE: 0,

            reportDeleteOpen: false,
            reportDeleteMeta: { id: null as number | null, report_name: '' },
            deletingReport: false,

            // ✅ Default tab is now "all"
            activeTab: 'all' as TabKey,

            // ✅ "All" tab added (first)
            tabs: [
                { key: 'all', label: 'All' },
                { key: 'appointment', label: 'Appointment' },
                { key: 'finance', label: 'Finance' },
                { key: 'clients', label: 'Clients' },
                { key: 'sales_team', label: 'Sales with Team' },
            ] as Array<{ key: TabKey; label: string }>,
        }
    },
    created() {
        this.localReports = (this.reports || []).map((r: any) => ({ ...r }))
    },
    computed: {
        tabReportNames(): Record<TabKey, string[]> {
            return {
                all: [],
                appointment: ['Appointment Summary report', 'Appointment list report'],
                finance: ['Revenue report', 'Daily sale report'],
                clients: ['Client list report'],
                sales_team: ['Daily report', 'Monthly report'],
            }
        },

        cardsForActiveTab(): CardDef[] {
            const makeOpenByName = (name: string) => () => this.openReportByName(name)

            const cardsByTab: Record<Exclude<TabKey, 'all'>, CardDef[]> = {
                appointment: [
                    {
                        key: 'appt-summary',
                        title: 'Appointment Summary report',
                        description: 'Summary of appointments by period, staff, and status.',
                        iconClass: 'bx bx-calendar-check',
                        iconWrapClass:
                            'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300',
                        onClick: () => (this as any).$inertia.visit(route('reports.appointments.summary')),
                    },
                    {
                        key: 'appt-list',
                        title: 'Appointment list report',
                        description: 'Detailed appointment list with filters and export-ready layout.',
                        iconClass: 'bx bx-list-ul',
                        iconWrapClass:
                            'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-300',
                        onClick: () => (this as any).$inertia.visit(route('reports.appointments.list')),
                    },
                ],
                finance: [
                    {
                        key: 'revenue',
                        title: 'Revenue report',
                        description: 'View revenue by date range, location, and service.',
                        iconClass: 'bx bx-line-chart',
                        iconWrapClass:
                            'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300',
                        onClick: () => this.goToRevenue(),
                    },
                    {
                        key: 'daily-sale',
                        title: 'Daily sale report',
                        description: 'Daily sales breakdown with totals and payment insights.',
                        iconClass: 'bx bx-receipt',
                        iconWrapClass:
                            'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                       onClick: () => (this as any).$inertia.visit(route('reports.daily_sales')),

                    },
                ],
                clients: [
                    {
                        key: 'client-list',
                        title: 'Client list report',
                        description: 'Client directory with key details and engagement overview.',
                        iconClass: 'bx bx-user',
                        iconWrapClass:
                            'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300',
                        onClick: () => (this as any).$inertia.visit(route('reports.clients.list')),
                    },
                ],
                sales_team: [
                    {
                        key: 'team-daily',
                        title: 'Daily report',
                        description: 'Daily team performance report with key activity metrics.',
                        iconClass: 'bx bx-bar-chart-alt-2',
                        iconWrapClass:
                            'bg-fuchsia-50 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-300',
                        onClick: () => (this as any).$inertia.visit(route('reports.sales_team.daily')),
                    },
                    {
                        key: 'team-monthly',
                        title: 'Monthly report',
                        description: 'Monthly team report showing trends, totals, and summaries.',
                        iconClass: 'bx bx-calendar',
                        iconWrapClass:
                            'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                        onClick: () => (this as any).$inertia.visit(route('reports.sales_team.monthly')),
                    },
                ],
            }

            // ✅ "All" shows every card (same routes)
            if (this.activeTab === 'all') {
                const allCards = [
                    ...cardsByTab.appointment,
                    ...cardsByTab.finance,
                    ...cardsByTab.clients,
                    ...cardsByTab.sales_team,
                ]

                // safety: ensure unique keys (in case future duplicates happen)
                const seen = new Set<string>()
                return allCards.filter((c) => (seen.has(c.key) ? false : (seen.add(c.key), true)))
            }

            return cardsByTab[this.activeTab]
        },

        displayedReports() {
            // ✅ All tab shows all reports
            if (this.activeTab === 'all') return this.localReports

            // Finance: keep showing all reports (same as your previous behavior)
            if (this.activeTab === 'finance') return this.localReports

            // Other tabs: show only reports that match those tab names (if they exist)
            const names = new Set(
                (this.tabReportNames[this.activeTab] || []).map((n) => n.toLowerCase().trim()),
            )
            return this.localReports.filter((r: any) =>
                names.has(String(r.report_name || '').toLowerCase().trim()),
            )
        },
    },
    methods: {
        setTab(key: TabKey) {
            this.activeTab = key
            this.dropdownOpen = null
        },

        normalizeName(s: string) {
            return (s || '').toLowerCase().trim().replace(/\s+/g, ' ')
        },

        openReportByName(name: string) {
            const target = this.normalizeName(name)
            const found = this.localReports.find(
                (r: any) => this.normalizeName(r.report_name || '') === target,
            )
            if (found?.id) {
                this.goToEdit(found.id)
                return
            }
            // Safe fallback: do nothing if not found (prevents route crashes)
            // Ensure DB report_name matches exactly to make it clickable.
            console.warn(`[Reports] Not found: ${name}`)
        },

        toggleDropdown(id: number) {
            this.dropdownOpen = this.dropdownOpen === id ? null : id
        },
        handleOutsideClick(event: MouseEvent) {
            const dropdowns = document.querySelectorAll('.dropdown-menu')
            const clickedInside = Array.from(dropdowns).some((el) => el.contains(event.target as Node))
            if (!clickedInside) this.dropdownOpen = null
        },

        goToEdit(id: number) {
            ;(this as any).$inertia.visit(route('reports.edit', id))
        },
        goToRevenue() {
            ;(this as any).$inertia.visit(route('reports.revenue'))
        },

        openDeleteModal(report: any) {
            this.reportDeleteMeta = { id: report.id, report_name: report.report_name }
            this.reportDeleteOpen = true
            this.dropdownOpen = null
        },
        closeDeleteModal() {
            this.reportDeleteOpen = false
        },
        confirmDeleteReport() {
            this.deletingReport = true
            const reportId = this.reportDeleteMeta.id
            const index = this.localReports.findIndex((r: any) => r.id === reportId)
            let backup: any = null

            if (index !== -1) {
                backup = this.localReports[index]
                this.localReports.splice(index, 1)
            }

            ;(this as any).$inertia.delete(route('reports.destroy', reportId), {
                preserveScroll: true,
                onSuccess: () => {
                    this.deletingReport = false
                    this.reportDeleteOpen = false
                },
                onError: () => {
                    if (backup) this.localReports.splice(index, 0, backup)
                    this.deletingReport = false
                },
            })
        },
        onToggleChange(report: any, checked: boolean) {
            const newStatus = checked ? this.ACTIVE_VALUE : this.INACTIVE_VALUE
            const idx = this.localReports.findIndex((r: any) => r.id === report.id)
            const prevStatus = report.status

            if (idx !== -1) this.localReports[idx].status = newStatus

            ;(this as any).$inertia.post(
                route('reports.toggleStatus', report.id),
                { status: newStatus },
                {
                    preserveScroll: true,
                    onError: () => {
                        this.localReports[idx].status = prevStatus
                    },
                },
            )
        },
    },
    mounted() {
        document.addEventListener('click', this.handleOutsideClick)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleOutsideClick)
    },
}
</script>

<style scoped>
/* line clamp helpers */
.line-clamp-2,
.line-clamp-3 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-2 {
    -webkit-line-clamp: 2;
}
.line-clamp-3 {
    -webkit-line-clamp: 3;
}

/* dropdown animation */
.dropdown-menu {
    animation: fadeIn 0.15s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* modal transitions */
.overlay-fade-enter-active,
.overlay-fade-leave-active {
    transition: opacity 0.2s ease;
}
.overlay-fade-enter-from,
.overlay-fade-leave-to {
    opacity: 0;
}
.modal-pop-enter-active {
    transition: all 0.25s ease;
}
.modal-pop-enter-from {
    opacity: 0;
    transform: scale(0.95);
}

/* Tab content transition */
.tab-swap-enter-active,
.tab-swap-leave-active {
    transition: opacity 220ms ease, transform 220ms ease;
}
.tab-swap-enter-from {
    opacity: 0;
    transform: translateY(6px);
}
.tab-swap-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* hide horizontal scrollbar for tabs on mobile */
.tabs-scroll {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.tabs-scroll::-webkit-scrollbar {
    display: none;
}

/* Tabs styling: no block for inactive; pill only for active */
.tab-btn {
    position: relative;
    border-radius: 0.75rem;
    padding: 0.55rem 0.9rem;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 180ms ease;
    outline: none;
    white-space: nowrap;
    cursor: pointer;
}

/* inactive: text-only feel */
.tab-btn.is-inactive {
    background: transparent;
    color: rgba(82, 82, 91, 1); /* zinc-600 */
}
:global(.dark) .tab-btn.is-inactive {
    color: rgba(212, 212, 216, 1); /* zinc-300 */
}
.tab-btn.is-inactive:hover {
    background: rgba(244, 244, 245, 0.7); /* zinc-100-ish */
}
:global(.dark) .tab-btn.is-inactive:hover {
    background: rgba(63, 63, 70, 0.55); /* zinc-700-ish */
}

/* active: pill block */
.tab-btn.is-active {
    background: #111827; /* near black */
    color: white;
}
</style>
