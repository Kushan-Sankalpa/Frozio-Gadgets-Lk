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
import { NavItemWithSubmenu, type NavItem } from '@/Backend/types';
import { Link } from '@inertiajs/vue3';
import {
  LayoutDashboard,
  BookOpen,
  Layers,
  Tags,
  HardDrive,
  MemoryStick,
  ShieldCheck,
  Palette,
  Package,
  FolderKanban, // ✅ add
} from 'lucide-vue-next';

import AppLogo from './AppLogo.vue';
import { route } from 'ziggy-js';

const mainNavItems: NavItemWithSubmenu[] = [
  {
    title: 'Dashboard',
    href: route('dashboard'),
    icon: LayoutDashboard,
  },
  {
    title: 'Categories',
    href: route('categories.index'),
    icon: Layers,
  },
  {
    title: 'Brands',
    href: route('brands.index'),
    icon: Tags,
  },
  {
    title: 'RAM Options',
    href: route('ram.index'),
    icon: MemoryStick,
  },
  {
    title: 'Storage Options',
    href: route('storage.index'),
    icon: HardDrive,
  },
  {
    title: 'Color Options',
    href: route('colors.index'),
    icon: Palette,
  },
  {
    title: 'Warranty Options',
    href: route('warranty.index'),
    icon: ShieldCheck,
  },
  {
    title: 'Products',
    href: route('products.index'),
    icon: Package,
  },

  // ✅ NEW: Other CMS tab + mini tab
  {
    title: 'Other CMS',
    href: route('homebanners.index'),
    icon: FolderKanban,
    submenu: [
      {
        title: 'Home Banners',
        href: route('homebanners.index'),
      },
    ],
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
