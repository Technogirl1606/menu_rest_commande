<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ClipboardList, UtensilsCrossed, Table2, Tags, ExternalLink } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
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
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Commandes',
        href: '/admin/kitchen',
        icon: ClipboardList,
    },
    {
        title: 'Plats',
        href: '/admin/items',
        icon: UtensilsCrossed,
    },
    {
        title: 'Catégories',
        href: '/admin/categories',
        icon: Tags,
    },
    {
        title: 'Tables',
        href: '/admin/tables',
        icon: Table2,
    },
    {
        title: 'Voir le menu',
        href: '/menu',
        icon: ExternalLink,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
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