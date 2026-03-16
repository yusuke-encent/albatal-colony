<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    FolderCog,
    LayoutGrid,
    LibraryBig,
    ShieldCheck,
    ShoppingBag,
    Store,
    UsersRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
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
import type { NavItem } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Storefront',
            href: '/',
            icon: Store,
        },
        {
            title: 'Library',
            href: '/library',
            icon: LibraryBig,
        },
    ];

    if (user.value?.role === 'admin') {
        items.push(
            {
                title: 'Manage Contents',
                href: '/admin/contents',
                icon: FolderCog,
            },
            {
                title: 'Sales Report',
                href: '/admin/sales',
                icon: ShoppingBag,
            },
            {
                title: 'Users',
                href: '/admin/users',
                icon: UsersRound,
            },
        );
    }

    if (user.value?.role === 'provider' || user.value?.role === 'admin') {
        items.push({
            title: 'Provider Sales',
            href: '/provider/sales',
            icon: ShieldCheck,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/">
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
