<script>
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { Head, Link } from '@inertiajs/vue3'

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
import LineRevenue from './LineRevenue.vue'
import BarMonthlyBookings from './BarMonthlyBookings.vue'
import DonutServiceMix from './DonutServiceMix.vue'

export default {
    name: "DashboardPage",

    props: {
        today_bookings: { type: Number, default: 0 },
        locations: { type: Number, default: 0 },
        customers: { type: Number, default: 0 },
        employees: { type: Number, default: 0 },
        revenue: { type: Number, default: 0 },

        week_labels: { type: Array, default: () => [] },
        week_data: { type: Array, default: () => [] },

        status_labels: { type: Array, default: () => [] },
        status_data: { type: Array, default: () => [] },

        latest_bookings: { type: Array, default: () => [] },
    },

    components: {
        AppLayout,
        Head,
        Link,
        LineRevenue,
        BarMonthlyBookings,
        DonutServiceMix,
        CalendarDaysIcon,
        MapPinIcon,
        UsersIcon,
        UserGroupIcon,
        BanknotesIcon,
        ChartBarIcon
    },

    data() {
        return {
            breadcrumbs: [{ title: 'Dashboard', href: dashboard().url }],
            currentTime: '',
            // KPI CARDS
            kpis: [
                {
                    label: 'Today Bookings',
                    value: this.today_bookings,
                    icon: CalendarDaysIcon,
                    color: '',
                    iconBg: 'bg-blue-50 text-blue-600',
                    route: 'calendar.index'
                },
                {
                    label: 'Locations',
                    value: this.locations,
                    icon: MapPinIcon,
                    color: '',
                    iconBg: 'bg-purple-50 text-purple-600',
                    route: 'branch.index'
                },
                {
                    label: 'Customers',
                    value: this.customers,
                    icon: UsersIcon,
                    color: '',
                    iconBg: 'bg-green-50 text-green-600',
                    route: 'clients.index'
                },
                {
                    label: 'Employees',
                    value: this.employees,
                    icon: UserGroupIcon,
                    color: '',
                    iconBg: 'bg-orange-50 text-orange-600',
                    route: 'users'
                }
            ],

            // CHART DATA (SAMPLE)
            weekLabels: this.week_labels,
            weeklyData: this.week_data,

            months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            bookings12: [84, 92, 110, 128, 140, 152, 170, 165, 180, 194, 205, 220],

            // STATUS DONUT
            statusLabels: this.status_labels,
            statusData: this.status_data,

            latestBookings: this.latest_bookings,
        }
    },

    computed: {
        greeting() {
            const hour = new Date().getHours();

            if (hour < 12) return "Good morning";
            if (hour < 18) return "Good afternoon";
            if (hour < 21) return "Good evening";
            return "Good night";
        }
    },
    mounted() {
        this.updateTime();
        setInterval(this.updateTime, 1000); // Live clock
    },
    methods: {
        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        }
    }

}
</script>


<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6 space-y-8">

            <!-- MODERN GREETING HEADER -->
            <div
                class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3  from-indigo-50 to-blue-50 p-5 rounded-2xl border shadow-sm">

                <!-- Left content -->
                <div>
                    <p class="text-sm text-slate-500">Welcome back,</p>

                    <h2 class="text-xl font-semibold text-slate-800">
                        {{ greeting }}, {{ $page.props.auth.user.name }} 👋
                    </h2>

                    <p class="text-sm font-medium text-slate-500 mt-1">
                        {{ currentTime }}
                    </p>
                </div>

                <!-- Right Icon (Always-visible tooltip) -->
                <Link :href="route('calendar.index')"
                    class="relative flex flex-col items-center justify-center transition-transform hover:scale-105">

                <!-- Icon -->
                <div class="w-12 h-12 rounded-2xl bg-white shadow-inner flex items-center justify-center
               border border-slate-200 hover:bg-blue-50 hover:border-blue-200 transition">
                    <CalendarDaysIcon class="w-8 h-8 text-blue-600" />
                </div>

                <!-- Always-visible tooltip -->
                <span class="mt-2 px-2 py-1 text-xs font-medium bg-slate-800 text-white rounded-md shadow-md
               animate-bounce-slow select-none">
                    Go to Calendar
                </span>
                </Link>



            </div>


            <!-- PREMIUM KPI GRID -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link v-for="(k, i) in kpis" :key="i" :href="route(k.route)" class="block relative overflow-hidden rounded-2xl bg-white border border-slate-200 p-6
               shadow-sm transition-all hover:shadow-md hover:-translate-y-1 hover:border-blue-300">

                <!-- Gradient Accent Layer -->
                <div :class="'absolute inset-0 opacity-10 bg-gradient-to-br ' + k.color"></div>

                <!-- Card Content -->
                <div class="relative flex items-center gap-5">

                    <!-- Icon -->
                    <div :class="'w-14 h-14 rounded-2xl flex items-center justify-center shadow-md ' + k.iconBg">
                        <component :is="k.icon" class="w-7 h-7" />
                    </div>

                    <!-- Text -->
                    <div>
                        <p class="text-sm text-slate-500 font-medium">{{ k.label }}</p>
                        <p class="text-3xl font-bold text-slate-900 mt-1">{{ k.value }}</p>
                    </div>
                </div>
                </Link>
            </div>


            <!-- MAIN CHART GRID -->
            <div class="grid gap-8 md:grid-cols-3">

                <!-- REVENUE LINE CHART -->
                <div class="w-full md:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-slate-800"> Weekly Bookings Trend</h3>

                    </div>
                    <div class="h-64 w-full">
                        <LineRevenue :labels="weekLabels" :data="weeklyData" />
                    </div>
                </div>

                <!-- SERVICE MIX DONUT -->
                <div class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm overflow-hidden">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Appointment Status</h3>
                    <div class="h-64 w-full">
                        <DonutServiceMix :labels="statusLabels" :data="status_data" />
                    </div>
                </div>



            </div>


            <!-- Latest 5 Bookings -->
             
            <div class="md:col-span-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Latest Bookings</h3>

                <div class="hidden md:block">
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

                 <!-- Mobile Horizontal Scroll Table -->
                 <div class="md:hidden">
        <div class="overflow-x-auto -mx-6 px-6">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-slate-600 whitespace-nowrap">Client</th>
                        <th class="px-3 py-2 text-left font-medium text-slate-600 whitespace-nowrap">Service</th>
                        <th class="px-3 py-2 text-left font-medium text-slate-600 whitespace-nowrap">Date</th>
                        <th class="px-3 py-2 text-left font-medium text-slate-600 whitespace-nowrap">Time</th>
                        <th class="px-3 py-2 text-left font-medium text-slate-600 whitespace-nowrap">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr v-for="b in latestBookings" :key="b.id" class="hover:bg-slate-50">
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span v-if="b.client">
                                {{ b.client.first_name }} {{ b.client.last_name }}
                            </span>
                            <span v-else class="">
                                Walk-in
                            </span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span v-for="s in b.services" :key="s.id" class="block">
                                {{ s.label }}
                            </span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            {{ new Date(b.starts_at).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            }) }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            {{ new Date(b.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                            }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span :class="[
                                'px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap',
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
            </div>
        </div>
    </AppLayout>
</template>
