<script setup lang="ts">
/* -----------------------------------------------
   Imports
------------------------------------------------ */
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
dayjs.extend(relativeTime);

import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { Bell, Lock } from 'lucide-vue-next';
import Toast from '@/components/Toast.vue';

/* -----------------------------------------------
   Props / Types
------------------------------------------------ */
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
withDefaults(defineProps<Props>(), { breadcrumbs: () => [] });

interface AppNotification {
    id: string;
    title: string;
    message: string;
    read_at?: string | null;
    created_at?: string;
}


/* -----------------------------------------------
   State
------------------------------------------------ */
const page = usePage();
const notifications = ref<AppNotification[]>([]);
const unreadCount = ref<number>(0);
const isDropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);


/*Click Outside Handler */
const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isDropdownOpen.value = false;
        document.removeEventListener('click', handleClickOutside);
    }
};


/*Dropdown Toggle*/
const toggleDropdown = (event: MouseEvent) => {
    event.stopPropagation();

    const wasOpen = isDropdownOpen.value;
    isDropdownOpen.value = !isDropdownOpen.value;
    
    document.removeEventListener('click', handleClickOutside);
    
    // Add listener when opening
    if (isDropdownOpen.value && !wasOpen) {
        nextTick(() => {
            document.addEventListener('click', handleClickOutside);
        });
    }
};

/* -----------------------------------------------
   Logout
------------------------------------------------ */
const logout = () => {
    router.post(route('logout'), {}, {
        onFinish: () => router.visit(route('home')),
    });
};


/* -----------------------------------------------
   Fetch Notifications
------------------------------------------------ */
const fetchNotifications = async () => {
    try {
        const res = await axios.get('/notifications/list');
        notifications.value = res.data.data;
        unreadCount.value = res.data.data.filter(n => !n.read_at).length;
    } catch (e) {
        console.error('Failed to fetch notifications', e);
    }
};


/* -----------------------------------------------
   Mark as Read
------------------------------------------------ */
const markAsRead = async (id: string) => {
    await axios.post(`/notifications/${id}/read`);

    notifications.value = notifications.value.map(n =>
        n.id === id ? { ...n, read_at: new Date().toISOString() } : n
    );

    unreadCount.value = notifications.value.filter(n => !n.read_at).length;
};


/* -----------------------------------------------
   Mark All Dropdown Notifications as Read
------------------------------------------------ */
const markDropdownAllRead = async () => {
    await axios.post('/notifications/read-all');

    notifications.value = notifications.value.map(n => ({
        ...n,
        read_at: new Date().toISOString(),
    }));

    unreadCount.value = 0;
};


/* -----------------------------------------------
   Group by Date
------------------------------------------------ */
const groupedNotifications = computed(() => {
    const groups: Record<string, AppNotification[]> = {};

    notifications.value.forEach(n => {
        const date = dayjs(n.created_at).format("YYYY-MM-DD");

        if (!groups[date]) groups[date] = [];
        groups[date].push(n);
    });

    return groups;
});

const formatGroupDate = (date: string) => {
    const d = dayjs(date);
    const today = dayjs().startOf("day");
    const yesterday = dayjs().subtract(1, "day").startOf("day");

    if (d.isSame(today, "day")) return "Today";
    if (d.isSame(yesterday, "day")) return "Yesterday";

    return d.format("DD MMM YYYY");
};


/* -----------------------------------------------
   Mounted
------------------------------------------------ */
onMounted(() => {
    // Ask browser notification permission
    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }

    fetchNotifications();

    const userId = page.props.auth.user.id;

    // Real-time notification listener
    window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification: any) => {
            notifications.value.unshift({
                id: notification.id,
                title: notification.title,
                message: notification.message,
                created_at: new Date().toISOString(),
            });

            unreadCount.value++;

            window.AppToast.show(notification.title, notification.message);
        });

    // Sync from full notifications page
    window.addEventListener('notifications:updated', fetchNotifications);
});

onUnmounted(() => {
    // Clean up click listener
    document.removeEventListener('click', handleClickOutside);
});
</script>



<!-- -----------------------------------------------
     TEMPLATE
------------------------------------------------ -->
<template>
    <AppShell variant="sidebar">
        <AppSidebar />

        <AppContent variant="sidebar" class="relative h-screen overflow-x-hidden">
            <div class="relative sticky top-0 z-40">

                <div class="relative z-10 bg-white text-black shadow-[0_8px_12px_-10px_rgba(0,0,0,0.25)]">
                    <header class="flex h-14 items-center justify-between gap-3 px-3 sm:px-4">
                        <AppSidebarHeader :breadcrumbs="breadcrumbs"
                            class="min-w-0 !text-black [&_*]:!text-black [&_a]:!text-black [&_svg]:!text-black" />

                        <div class="flex items-center gap-2">

                            <!-- Notification Bell -->
                            <div class="relative">
                                <button type="button" @click="toggleDropdown"
                                    class="group inline-flex h-10 w-10 items-center justify-center rounded-md border border-black/15 bg-black/5 transition-all hover:bg-white/10 focus:ring-2 focus:ring-[#FF2000]/60 cursor-pointer">
                                    <Bell class="h-5 w-5 group-hover:text-[#FF2000]" />


                                    <span v-if="unreadCount > 0"
                                        class="absolute -right-1 -top-1 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-[#FF2000] px-1 text-[10px] font-semibold text-white">
                                        {{ unreadCount > 9 ? "9+" : unreadCount }}
                                    </span>
                                </button>

                                <!-- Dropdown -->
                                <div v-if="isDropdownOpen" ref="dropdownRef"
                                    class="absolute right-0 mt-2 w-80 overflow-hidden rounded-lg border border-black/10 bg-white shadow-lg">
                                    <div class="flex items-center justify-between border-b px-3 py-2">
                                        <span class="text-sm font-semibold">Notifications</span>
                                        <button @click="markDropdownAllRead"
                                            class="text-xs text-blue-600 cursor-pointer">
                                            Mark all as read
                                        </button>
                                    </div>

                                    <div class="max-h-80 overflow-y-auto">

                                        <div v-if="notifications.length === 0"
                                            class="px-3 py-4 text-center text-xs text-neutral-500">
                                            No notifications yet.
                                        </div>

                                        <!-- GROUPED NOTIFICATIONS -->
                                        <template v-else>
                                            <div v-for="(items, date) in groupedNotifications" :key="date">
                                                <!-- Group Title -->
                                                <div
                                                    class="px-3 py-1 text-[11px] font-semibold text-neutral-500 bg-neutral-100">
                                                    {{ formatGroupDate(date) }}
                                                </div>

                                                <!-- Items -->
                                                <div v-for="n in items" :key="n.id"
                                                    class="flex flex-col gap-1 border-b px-3 py-2 hover:bg-neutral-50">
                                                    <span class="text-xs font-semibold text-neutral-900">
                                                        {{ n.title }}
                                                    </span>

                                                    <span class="text-xs text-neutral-600">
                                                        {{ n.message }}
                                                    </span>

                                                    <!-- TIME AGO -->
                                                    <span class="text-[10px] text-neutral-400">
                                                        {{ dayjs(n.created_at).fromNow() }}
                                                    </span>

                                                    <button v-if="!n.read_at" @click="markAsRead(n.id)"
                                                        class="text-xs text-blue-600 cursor-pointer">
                                                        Mark read
                                                    </button>

                                                    <span v-else class="text-[10px] text-green-600">
                                                        Read
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- View All -->
                                    <div class="border-t bg-neutral-50">
                                        <a :href="route('notifications.page')"
                                            class="block w-full px-3 py-2 text-center text-xs font-semibold text-blue-600 hover:bg-neutral-100">
                                            View all notifications
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Logout -->
                            <div class="relative">
                                <button type="button" @click="logout"
                                    class="group inline-flex h-10 w-10 items-center justify-center rounded-md border border-black/15 bg-black/5 hover:bg-white/10 focus:ring-2 focus:ring-[#FF2000]/60 cursor-pointer">
                                    <Lock class="h-5 w-5 group-hover:text-[#FF2000]" />
                                </button>
                            </div>

                        </div>
                    </header>
                </div>
            </div>

            <slot />

            <Toast />
        </AppContent>
    </AppShell>
</template>
