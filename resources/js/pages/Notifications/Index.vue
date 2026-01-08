<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'

interface NotificationItem {
    id: string
    title: string
    message: string
    read_at: string | null
    created_at: string
}

const notifications = ref<NotificationItem[]>([])
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = 10
const loading = ref(false)

const loadPage = async (page = 1) => {
    loading.value = true
    const res = await axios.get('/notifications/list?page=' + page + '&per_page=' + perPage)
    notifications.value = res.data.data
    currentPage.value = res.data.current_page
    lastPage.value = res.data.last_page
    loading.value = false
}

const markAsRead = async (id: string) => {
    try {
        await axios.post(`/notifications/${id}/read`)

        notifications.value = notifications.value.map(n =>
            n.id === id ? { ...n, read_at: new Date().toISOString() } : n
        )

        window.dispatchEvent(new CustomEvent('notifications:updated'))
    } catch (e) {
        console.error('Failed to mark notification as read', e)
    }
}

const markAllAsRead = async () => {
    await axios.post('/notifications/read-all')

    notifications.value = notifications.value.map(n => ({
        ...n,
        read_at: new Date().toISOString(),
    }))

    window.dispatchEvent(new CustomEvent('notifications:updated'))
}

// unread count for header + button state
const unreadCount = computed(() =>
    notifications.value.filter(n => !n.read_at).length
)

// LIMIT BREADCRUMB (PAGINATION) TO 4 STAGES
const pagesToShow = computed(() => {
    const total = lastPage.value
    const cur = currentPage.value
    const maxToShow = 4

    if (total <= maxToShow) {
        return Array.from({ length: total }, (_, i) => i + 1)
    }

    let start = Math.max(1, cur - Math.floor(maxToShow / 2))
    let end = start + maxToShow - 1

    if (end > total) {
        end = total
        start = end - maxToShow + 1
    }

    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

onMounted(() => loadPage(1))
</script>

<template>
    <AppLayout>
        <div class="min-h-screen">
            <div class="max-w-6xl mx-auto py-8 px-4 md:py-12">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-[#ff2000]/10">
                            <!-- Bell icon -->
                            <svg
                                class="w-6 h-6 text-[#ff2000]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 01-6 0m6 0H9"
                                />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                Notifications
                            </h1>
                            <p class="text-sm text-gray-500">
                                <template v-if="unreadCount > 0">
                                    You have {{ unreadCount }} unread
                                    notification<span v-if="unreadCount > 1">s</span>
                                </template>
                                <template v-else>
                                    All caught up!
                                </template>
                            </p>
                        </div>
                    </div>

                    <button
                        @click="markAllAsRead"
                        :disabled="unreadCount === 0 || loading"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white text-sm font-medium
                               border border-gray-200 shadow-sm hover:bg-gray-50
                               disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <!-- double check icon -->
                        <svg
                            class="w-4 h-4 text-[#ff2000]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 12l2 2 4-4m-6 6l2 2 4-4M5 13l2 2"
                            />
                        </svg>
                        <span class="text-gray-800">Mark all as read</span>
                    </button>
                </div>

                <!-- Notifications list -->
                <div class="space-y-3">
                    <div
                        v-for="(n, index) in notifications"
                        :key="n.id"
                        class="group relative p-5 rounded-xl border bg-white transition-all duration-300
                               hover:shadow-md"
                        :class="{
                            'border-l-4 border-l-[#ff2000] shadow-md': !n.read_at,
                            'opacity-80': n.read_at,
                        }"
                        :style="{ animationDelay: index * 50 + 'ms' }"
                    >
                        <!-- Unread indicator -->
                        <div v-if="!n.read_at" class="absolute top-5 right-5">
                            <span class="relative flex h-2.5 w-2.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#ff2000] opacity-75"
                                ></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#ff2000]"></span>
                            </span>
                        </div>

                        <div class="pr-8">
                            <h3
                                class="font-semibold mb-1"
                                :class="n.read_at ? 'text-gray-600' : 'text-gray-900'"
                            >
                                {{ n.title }}
                            </h3>
                            <p
                                class="text-sm leading-relaxed mb-3"
                                :class="n.read_at ? 'text-gray-500' : 'text-gray-600'"
                            >
                                {{ n.message }}
                            </p>

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">
                                    {{ n.created_at }}
                                </span>

                                <button
                                    v-if="!n.read_at"
                                    @click="markAsRead(n.id)"
                                    class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-md text-xs
                                           text-[#ff2000] hover:bg-[#ff2000]/10
                                           opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <!-- check icon -->
                                    <svg
                                        class="w-3.5 h-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    <span>Mark as read</span>
                                </button>

                                <span
                                    v-else
                                    class="text-xs text-gray-400 flex items-center gap-1"
                                >
                                    <svg
                                        class="w-3 h-3 text-[#ff2000]"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    <span>Read</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="!loading && notifications.length === 0"
                        class="p-8 rounded-xl border border-dashed text-center text-sm text-gray-500 bg-white"
                    >
                        No notifications to show.
                    </div>
                </div>

                <!-- Pagination (breadcrumb-style) -->
                <div class="flex items-center justify-center gap-2 mt-8">
                    <button
                        :disabled="currentPage === 1 || loading"
                        @click="loadPage(currentPage - 1)"
                        class="inline-flex items-center gap-1 px-3 py-1.5 border rounded-lg text-sm
                               text-gray-700 bg-white hover:bg-gray-50
                               disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                        <span>Previous</span>
                    </button>

                    <div class="flex items-center gap-1 px-2">
                        <!-- Only up to 4 stages -->
                        <button
                            v-for="page in pagesToShow"
                            :key="page"
                            @click="loadPage(page)"
                            class="w-8 h-8 rounded-lg text-sm font-medium transition-colors"
                            :class="
                                page === currentPage
                                    ? 'bg-[#ff2000] text-white shadow-sm'
                                    : 'text-gray-500 bg-white hover:bg-gray-100 border border-transparent'
                            "
                        >
                            {{ page }}
                        </button>
                    </div>

                    <button
                        :disabled="currentPage === lastPage || loading"
                        @click="loadPage(currentPage + 1)"
                        class="inline-flex items-center gap-1 px-3 py-1.5 border rounded-lg text-sm
                               text-gray-700 bg-white hover:bg-gray-50
                               disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <span>Next</span>
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
