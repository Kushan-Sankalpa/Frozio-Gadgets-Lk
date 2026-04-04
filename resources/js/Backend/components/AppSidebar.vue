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
      {
        title: 'Product Reviews',
        href: route('product-reviews.index'),
      },
    ],
  },
  {
    title: 'Shoe Products',
    href: '#',
    icon: Package,
    submenu: [
      {
        title: 'Shoe Brands',
        href: route('admin.shoes.brands.index'),
      },
      // {
      //   title: 'Shoe Types',
      //   href: route('admin.shoes.types.index'),
      // },
      {
        title: 'Shoe Categories',
        href: route('admin.shoes.categories.index'),
      },
      {
        title: 'Shoe Subcategories',
        href: route('admin.shoes.subcategories.index'),
      },
      {
        title: 'Shoe Size Types',
        href: route('admin.shoes.size-types.index'),
      },
      {
        title: 'Shoe Colors',
        href: route('admin.shoes.colors.index'),
      },
      {
        title: 'Shoe Materials',
        href: route('admin.shoes.materials.index'),
      },
      {
        title: 'Shoe Products',
        href: route('admin.shoes.products.index'),
      },
      {
        title: 'Product Reviews',
        href: route('admin.shoes.product-reviews.index'),
      },
    ],
  },
  {
    title: 'Cosmetics',
    href: '#',
    icon: Package,
    submenu: [
      {
        title: 'Cosmetic Brands',
        href: route('admin.cosmetics.brands.index'),
      },
      {
        title: 'Cosmetic Categories',
        href: route('admin.cosmetics.categories.index'),
      },
      {
        title: 'Product Types',
        href: route('admin.cosmetics.product-types.index'),
      },
      {
        title: 'Sizes / Volume',
        href: route('admin.cosmetics.sizes-volume.index'),
      },
      {
        title: 'Country of Origin',
        href: route('admin.cosmetics.countries-origin.index'),
      },
      {
        title: 'Cosmetic Products',
        href: route('admin.cosmetics.products.index'),
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

    {
    title: 'Invoice',
    href: route('invoices.index'),
    icon: FolderKanban,
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
