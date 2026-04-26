<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Activity, House, LayoutGrid, LucideBookOpenText, Tally5Icon, Telescope } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { GitHubIcon } from 'vue3-simple-icons';

const { t } = useI18n();

const page = usePage();
const isSuperAdmin = computed(() => page.props.auth?.user?.roles?.includes('Super Admin'));

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: t('navigation.home'),
            href: '/home',
            icon: House,
        },
        {
            title: t('welcome.my_tallies'),
            href: route('tallies.index'),
            icon: Tally5Icon,
        },
    ];

    if (isSuperAdmin.value) {
        items.push({
            title: t('navigation.dashboard'),
            href: route('dashboard'),
            icon: LayoutGrid,
        });
    }

    return items;
});

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
    <Sidebar collapsible="icon" variant="inset" class="border-r border-rule-2">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem class="flex items-center justify-between gap-2">
                    <Link :href="route('home')" class="flex items-center px-1 py-2">
                        <AppLogo size="sm" />
                    </Link>
                    <ThemeToggle class="group-data-[collapsible=icon]:hidden" />
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
