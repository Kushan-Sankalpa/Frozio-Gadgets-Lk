<script>
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { Head } from '@inertiajs/vue3'

// Icons
import {
    CalendarDaysIcon,
    MapPinIcon,
    UsersIcon,
    UserGroupIcon,
    BanknotesIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline'

// Charts
import LineWeeklyTrend from './LineWeeklyTrend.vue'
import DonutStatusChart from './DonutStatusChart.vue'

export default {
    name: "DashboardStaff",

    props: {
        today_bookings: Number,
        locations: Number,
        customers: Number,
        employees: Number,

        week_labels: Array,
        week_data: Array,

        status_labels: Array,
        status_data: Array,

        latest_bookings: Array,

    },

    components: {
        AppLayout,
        Head,

        CalendarDaysIcon,
        MapPinIcon,
        UsersIcon,
        UserGroupIcon,
        BanknotesIcon,
        ChartBarIcon,

        LineWeeklyTrend,
        DonutStatusChart
    },

    data() {
        return {
            breadcrumbs: [{ title: 'Dashboard', href: dashboard().url }],

            // STAFF KPI CARDS
            kpis: [
                {
                    label: 'Today Bookings',
                    value: this.today_bookings,
                    icon: CalendarDaysIcon,
                    color: '',
                    iconBg: 'bg-blue-50 text-blue-600'
                },
                {
                    label: 'Locations',
                    value: this.locations,
                    icon: MapPinIcon,
                    color: '',
                    iconBg: 'bg-purple-50 text-purple-600'
                },
                {
                    label: 'Customers',
                    value: this.customers,
                    icon: UsersIcon,
                    color: '',
                    iconBg: 'bg-green-50 text-green-600'
                },
                {
                    label: 'Employees',
                    value: this.employees,
                    icon: UserGroupIcon,
                    color: '',
                    iconBg: 'bg-orange-50 text-orange-600'
                }

            ],

            // WEEK TREND CHART
            weekLabels: this.week_labels,
            weeklyData: this.week_data,

            // STATUS DONUT
            statusLabels: this.status_labels,
            statusData: this.status_data,

            latestBookings: this.latest_bookings,

        }
    }
}
</script>


<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6 space-y-10">

            <!-- KPI GRID (5 cards) -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(k, i) in kpis" :key="i"
                    class="relative overflow-hidden rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">

                    <!-- Gradient -->
                    <div :class="'absolute inset-0 opacity-10 bg-gradient-to-br ' + k.color"></div>

                    <!-- Content -->
                    <div class="relative flex items-center gap-4">

                        <div :class="'w-14 h-14 rounded-2xl flex items-center justify-center shadow-md ' + k.iconBg">
                            <component :is="k.icon" class="w-7 h-7" />
                        </div>

                        <div>
                            <p class="text-sm text-slate-500 font-medium">{{ k.label }}</p>
                            <p class="text-3xl font-bold text-slate-900 mt-1">{{ k.value }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Charts -->
            <div class="grid gap-8 md:grid-cols-3">

                <!-- Weekly Bookings Trend -->
                <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        Weekly Bookings Trend
                    </h3>
                    <div class="h-64">
                        <LineWeeklyTrend :labels="weekLabels" :data="weeklyData" />
                    </div>
                </div>

                <!-- Appointment Status Donut -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Appointment Status</h3>
                    <div class="h-64">
                        <DonutStatusChart :labels="statusLabels" :data="statusData" />
                    </div>
                </div>

            </div>

            <!-- Latest 5 Bookings -->
            <div class="md:col-span-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Latest Bookings</h3>

                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-slate-600">Client</th>
                            <th class="px-4 py-2 text-left font-medium text-slate-600">Service</th>
                            <th class="px-4 py-2 text-left font-medium text-slate-600">Date</th>
                            <th class="px-4 py-2 text-left font-medium text-slate-600">Time</th>
                            <th class="px-4 py-2 text-left font-medium text-slate-600">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr v-for="b in latestBookings" :key="b.id" class="hover:bg-slate-50">

                            <td class="px-4 py-3">
                                <span v-if="b.client">
                                    {{ b.client.first_name }} {{ b.client.last_name }}
                                </span>
                                <span v-else class="">
                                    Walk-in
                                </span>
                            </td>



                            <!-- Service -->
                            <td class="px-4 py-3">
                                <span v-for="s in b.services" :key="s.id" class="block">
                                    {{ s.label }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-3">
                                {{ new Date(b.starts_at).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                                }) }}
                            </td>

                            <!-- Time -->
                            <td class="px-4 py-3">
                                {{ new Date(b.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                                }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <span :class="[
                                    'px-3 py-1 rounded-full text-xs font-medium',
                                    b.status === 'completed' ? 'bg-green-100 text-green-700' :
                                        b.status === 'cancelled' ? 'bg-yellow-100 text-yellow-700' :
                                            b.status === 'no_show' ? 'bg-red-100 text-red-700' :
                                                'bg-blue-100 text-blue-700'
                                ]">
                                    {{ b.status.replace('_', ' ').toUpperCase() }}
                                </span>
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>


        </div>
    </AppLayout>
</template>
