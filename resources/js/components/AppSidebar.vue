<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, } from '@/routes';
import { NavItemWithSubmenu, type NavItem } from '@/types';
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
        permission: 'dashboard.view',
    },
    {
        title: 'Calendar',
        href: route('calendar.index'),
        icon: CalendarDays,
        permission: 'calendar.view',
    },
    {
        title: 'Bookings',
        href: route('bookings.index'),
        icon: BookIcon,
        permission: 'bookings.view',
    },

    {
        title: 'Location',
        href: route('branch.index'),
        icon: MapPinned,
        permission: 'branch.view',
    },
    {
        title: 'Service Menu',
        href: route('service.index'),
        icon: ClipboardList,
        permission: 'service.view',
    },

     {
        title: 'Marketing',
        icon: Settings,
        submenu: [
           {
        title: 'Discounts',
        href: route('discounts'),
        icon: BadgePercent,
        permission: 'discounts.view',
    },
        ]

    },


    {
        title: 'Clients',
        href: route('clients.index'),
        icon: Users,
        permission: 'clients.view',
    },
    {
        title: 'Loyalty Program',
        href: route('loyalty-program.index'),
        icon: Medal,
        permission: 'loyalty-program.view',
    },
    {
        title: 'Banners',
        href: route('banner.index'),
        icon: Images,
        permission: 'banner.view',
    },
    {
        title: 'Reports',
        href: route('reports.index'),
        icon: BarChart3,
        permission: 'reports.view',
    },


    {
        title: 'Settings',
        icon: Settings,
        submenu: [
            {
                title: 'Roles & Permissions',
                href: route('roles'),
                icon: ShieldCheck,
                permission: 'roles-permissions.view',
            },
            {
                title: 'Staff Members',
                href: route('users'),
                icon: UserRoundCog,
                permission: 'user.view',
            },
            // {
            //     title: 'SMS Gateway',
            //     href: route('sms.gateway'),
            //     icon: MessageSquare ,
            //     permission: 'sms-gateway.view',
            // }

        ]

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
