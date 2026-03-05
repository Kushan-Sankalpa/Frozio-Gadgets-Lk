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
  Package,
  FolderKanban,
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
    title: 'Tech Products',
    href: '#',
    icon: Package,
    submenu: [
      {
        title: 'Categories',
        href: route('categories.index'),
      },
      {
        title: 'Brands',
        href: route('brands.index'),
      },
      {
        title: 'Ram Options',
        href: route('ram.index'),
      },
      {
        title: 'Storage Options',
        href: route('storage.index'),
      },
      {
        title: 'Color Options',
        href: route('colors.index'),
      },
      {
        title: 'Warranty Options',
        href: route('warranty.index'),
      },
      {
        title: 'Products',
        href: route('products.index'),
      },
    ],
  },
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