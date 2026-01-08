<script setup lang="ts">
import NavFooter from '@/Backend/components/NavFooter.vue';
import NavMain from '@/Backend/components/NavMain.vue';
import NavUser from '@/Backend/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/Backend/components/ui/sidebar';
import { dashboard, } from '@/routes';
import { NavItemWithSubmenu, type NavItem } from '@/Backend/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    MapPinned,
    CalendarDays,
    ClipboardList,
    BadgePercent,
    Users,
    Images,
    Settings,
    ShieldCheck,
    UserRoundCog,
    BookOpen,
    MessageSquare,
    BarChart3,
    Medal,
    BookIcon
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

import { route } from 'ziggy-js'

const mainNavItems: NavItemWithSubmenu[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutDashboard,
    },
];

const footerNavItems: NavItem[] = [

    {
        title: 'Privacy Policy',
        href: route('privacypolicy'),
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
