<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Activity, LayoutGrid, LucideBookOpenText, Telescope } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { GitHubIcon } from 'vue3-simple-icons';
import AppLogo from './AppLogo.vue';

const { t } = useI18n();

const mainNavItems: NavItem[] = [
    {
        title: t('navigation.dashboard'),
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: t('navigation.pulse'),
        href: '/pulse',
        icon: Activity,
    },
    {
        title: t('navigation.telescope'),
        href: '/telescope',
        icon: Telescope,
    },
    {
        title: t('navigation.github_repo'),
        href: 'https://github.com/keteraf/tallypal',
        icon: GitHubIcon,
    },
    {
        title: t('navigation.documentation'),
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: LucideBookOpenText,
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
