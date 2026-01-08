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

import AppContent from '@/Backend/components/AppContent.vue';
import AppShell from '@/Backend/components/AppShell.vue';
import AppSidebar from '@/Backend/components/AppSidebar.vue';
import AppSidebarHeader from '@/Backend/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/Backend/types';
import { Lock } from 'lucide-vue-next';
import Toast from '@/Backend/components/Toast.vue';

/* -----------------------------------------------
   Props / Types
------------------------------------------------ */
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
withDefaults(defineProps<Props>(), { breadcrumbs: () => [] });

/* -----------------------------------------------
   State
------------------------------------------------ */
const page = usePage();


/* -----------------------------------------------
   Logout
------------------------------------------------ */
const logout = () => {
    router.post(route('logout'), {}, {
        onFinish: () => router.visit(route('home')),
    });
};
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
