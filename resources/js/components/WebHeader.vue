<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { House, LayoutGrid, LogOut, Menu, Tally5 } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { t } = useI18n();

const isSuperAdmin = computed(() => auth.value.user?.roles?.includes('Super Admin'));

const isCurrentRoute = computed(() => (url: string) => page.url === url);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: t('navigation.home'),
            href: '/home',
            icon: House,
        },
        {
            title: t('tallies.title'),
            href: '/tallies',
            icon: Tally5,
        },
    ];

    if (isSuperAdmin.value) {
        items.unshift({
            title: t('navigation.dashboard'),
            href: route('dashboard'),
            icon: LayoutGrid,
        });
    }

    return items;
});
</script>

<template>
    <div>
        <header class="sticky top-0 z-50 border-b border-rule-2 bg-background/85 backdrop-blur-md">
            <div class="mx-auto flex h-[68px] w-full max-w-[1320px] items-center justify-between gap-6 px-6 md:px-8">
                <!-- Mobile menu trigger + brand -->
                <div class="flex items-center gap-3">
                    <div class="lg:hidden">
                        <Sheet>
                            <SheetTrigger :as-child="true">
                                <button
                                    type="button"
                                    class="inline-flex size-9 items-center justify-center rounded-full border border-rule text-muted-foreground transition-colors hover:border-foreground hover:text-foreground"
                                >
                                    <Menu class="size-4" />
                                    <span class="sr-only">{{ $t('common.navigation_menu') }}</span>
                                </button>
                            </SheetTrigger>
                            <SheetContent side="left" class="w-72 border-rule bg-paper p-7">
                                <SheetTitle class="sr-only">{{ $t('common.navigation_menu') }}</SheetTitle>
                                <SheetHeader class="flex justify-start p-0 text-left">
                                    <AppLogo />
                                </SheetHeader>
                                <nav class="mt-8 flex flex-col gap-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center justify-between gap-3 border-b border-rule-2 py-3 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                        :class="{ 'font-medium text-foreground': isCurrentRoute(item.href) }"
                                    >
                                        <span class="flex items-center gap-3">
                                            <component v-if="item.icon" :is="item.icon" class="size-4" />
                                            {{ item.title }}
                                        </span>
                                    </Link>
                                </nav>
                                <div v-if="auth.user" class="mt-6 border-t border-rule pt-4">
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="flex w-full items-center gap-3 py-3 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        <LogOut class="size-4" />
                                        {{ $t('common.logout') }}
                                    </Link>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>

                    <Link :href="route('home')" class="flex items-center">
                        <AppLogo show-beta />
                    </Link>
                </div>

                <!-- Desktop nav -->
                <nav class="hidden items-center gap-7 lg:flex">
                    <Link
                        v-for="item in mainNavItems"
                        :key="item.title"
                        :href="item.href"
                        class="text-sm transition-colors"
                        :class="isCurrentRoute(item.href) ? 'font-medium text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    >
                        {{ item.title }}
                    </Link>
                </nav>

                <div class="flex items-center gap-2">
                    <ThemeToggle />

                    <template v-if="auth.user">
                        <DropdownMenu>
                            <DropdownMenuTrigger :as-child="true">
                                <button
                                    type="button"
                                    class="relative inline-flex size-9 items-center justify-center rounded-full border border-rule text-muted-foreground transition-colors hover:border-foreground hover:text-foreground"
                                >
                                    <Avatar class="size-7 overflow-hidden rounded-full">
                                        <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                        <AvatarFallback class="rounded-full bg-brand-soft font-mono text-[11px] text-brand">
                                            {{ getInitials(auth.user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <UserMenuContent :user="auth.user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="hidden text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline-block"
                        >
                            {{ $t('common.login') }}
                        </Link>
                        <Button as-child size="default">
                            <Link :href="route('register')">{{ $t('common.register') }} →</Link>
                        </Button>
                    </template>
                </div>
            </div>
        </header>

        <div v-if="props.breadcrumbs.length > 1" class="border-b border-rule-2">
            <div class="mx-auto flex h-12 w-full max-w-[1320px] items-center px-6 text-muted-foreground md:px-8">
                <Breadcrumbs :breadcrumbs="props.breadcrumbs" />
            </div>
        </div>
    </div>
</template>
