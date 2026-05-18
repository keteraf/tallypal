<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useLanguageSwitcher } from '@/composables/useLanguageSwitcher';
import { getInitials } from '@/composables/useInitials';
import { dashboard, home, login, logout } from '@/routes';
import * as profile from '@/routes/profile';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { House, Languages, LayoutGrid, LogOut, Menu, Settings, Tally5 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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

const sheetOpen = ref(false);
const { languages, locale, setLocale } = useLanguageSwitcher();

const isSuperAdmin = computed(() => auth.value.user?.roles?.includes('Super Admin'));

const isCurrentRoute = computed(() => (url: string) => page.url === url);

const isWelcomePage = computed(() => page.url === '/');

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
            href: dashboard.url(),
            icon: LayoutGrid,
        });
    }

    return items;
});
</script>

<template>
    <div class="sticky top-0 z-50">
        <header class="border-b border-rule-2 bg-background/85 backdrop-blur-md">
            <div class="mx-auto flex h-17 w-full max-w-330 items-center justify-between gap-3 px-4 sm:gap-6 sm:px-6 md:px-8">
                <!-- Brand (left) -->
                <Link :href="home.url()" class="flex items-center">
                    <AppLogo show-beta />
                </Link>

                <!-- Desktop nav (unchanged) -->
                <nav class="hidden items-center gap-7 lg:flex">
                    <template v-if="auth.user">
                        <Link
                            v-for="item in mainNavItems"
                            :key="item.title"
                            :href="item.href"
                            class="text-sm transition-colors"
                            :class="isCurrentRoute(item.href) ? 'font-medium text-foreground' : 'text-muted-foreground hover:text-foreground'"
                        >
                            {{ item.title }}
                        </Link>
                    </template>
                    <span v-if="auth.user && isWelcomePage" class="h-4 w-px bg-rule"></span>
                    <template v-if="isWelcomePage || !auth.user">
                        <a href="#features" class="text-sm text-muted-foreground transition-colors hover:text-foreground">{{ $t('navigation.features') }}</a>
                        <a href="#pricing" class="text-sm text-muted-foreground transition-colors hover:text-foreground">{{ $t('navigation.pricing') }}</a>
                        <a href="#faq" class="text-sm text-muted-foreground transition-colors hover:text-foreground">{{ $t('navigation.faq') }}</a>
                    </template>
                </nav>

                <!-- Right side -->
                <div class="flex items-center gap-2">
                    <!-- Desktop only: theme toggle -->
                    <ThemeToggle class="hidden lg:inline-flex" />

                    <!-- Desktop only: user dropdown -->
                    <div v-if="auth.user" class="hidden lg:block">
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
                    </div>

                    <!-- Guest: login link (all viewports) -->
                    <Link
                        v-if="!auth.user"
                        :href="login.url()"
                        class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        {{ $t('common.login') }}
                    </Link>

                    <!-- Mobile only: hamburger sheet (authenticated only) -->
                    <div v-if="auth.user" class="lg:hidden">
                        <Sheet v-model:open="sheetOpen">
                            <SheetTrigger :as-child="true">
                                <button
                                    type="button"
                                    class="inline-flex size-9 items-center justify-center rounded-full border border-rule text-muted-foreground transition-colors hover:border-foreground hover:text-foreground"
                                >
                                    <Menu class="size-4" />
                                    <span class="sr-only">{{ $t('common.navigation_menu') }}</span>
                                </button>
                            </SheetTrigger>
                            <SheetContent side="left" class="flex flex-col overflow-y-auto w-80 border-rule bg-paper p-7">
                                <SheetTitle class="sr-only">{{ $t('common.navigation_menu') }}</SheetTitle>
                                <SheetHeader class="flex justify-start p-0 text-left">
                                    <AppLogo />
                                </SheetHeader>
                                <nav class="mt-8 flex flex-1 flex-col gap-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center justify-between gap-3 border-b border-rule-2 py-3 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                        :class="{ 'font-medium text-foreground': isCurrentRoute(item.href) }"
                                        @click="sheetOpen = false"
                                    >
                                        <span class="flex items-center gap-3">
                                            <component v-if="item.icon" :is="item.icon" class="size-4" />
                                            {{ item.title }}
                                        </span>
                                    </Link>
                                    <template v-if="isWelcomePage">
                                        <div class="my-2 border-t border-rule"></div>
                                        <a href="#features" class="flex items-center gap-3 border-b border-rule-2 py-3 text-sm text-muted-foreground transition-colors hover:text-foreground" @click="sheetOpen = false">{{ $t('navigation.features') }}</a>
                                        <a href="#pricing" class="flex items-center gap-3 border-b border-rule-2 py-3 text-sm text-muted-foreground transition-colors hover:text-foreground" @click="sheetOpen = false">{{ $t('navigation.pricing') }}</a>
                                        <a href="#faq" class="flex items-center gap-3 border-b border-rule-2 py-3 text-sm text-muted-foreground transition-colors hover:text-foreground" @click="sheetOpen = false">{{ $t('navigation.faq') }}</a>
                                    </template>
                                </nav>

                                <!-- User section -->
                                <div class="mt-auto border-t border-rule pt-4">
                                    <!-- Avatar + name + email -->
                                    <div class="flex items-center gap-3 border-b border-rule-2 pb-4">
                                        <Avatar class="size-9 overflow-hidden rounded-full">
                                            <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                            <AvatarFallback class="rounded-full bg-brand-soft font-mono text-[11px] text-brand">
                                                {{ getInitials(auth.user?.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="flex min-w-0 flex-col">
                                            <span class="truncate text-sm font-medium text-foreground">{{ auth.user.name }}</span>
                                            <span class="truncate text-xs text-muted-foreground">{{ auth.user.email }}</span>
                                        </div>
                                    </div>

                                    <!-- Settings -->
                                    <Link
                                        :href="profile.edit.url()"
                                        class="flex items-center gap-3 border-b border-rule-2 py-3 text-sm text-muted-foreground transition-colors hover:text-foreground"
                                        @click="sheetOpen = false"
                                    >
                                        <Settings class="size-4" />
                                        {{ $t('user_menu.settings') }}
                                    </Link>

                                    <!-- Language switcher -->
                                    <div class="flex items-center gap-3 border-b border-rule-2 py-3 text-sm text-muted-foreground">
                                        <Languages class="size-4 shrink-0" />
                                        <span class="flex-1">{{ $t('user_menu.language') }}</span>
                                        <div class="flex gap-3">
                                            <button
                                                v-for="lang in languages"
                                                :key="lang.code"
                                                type="button"
                                                class="text-xs transition-colors"
                                                :class="locale === lang.code ? 'font-medium text-foreground' : 'hover:text-foreground'"
                                                @click="setLocale(lang.code)"
                                            >
                                                {{ lang.name }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Logout -->
                                    <Link
                                        :href="logout.url()"
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
                </div>
            </div>
        </header>

        <div v-if="props.breadcrumbs.length > 1" class="border-b border-rule-2">
            <div class="mx-auto flex h-12 w-full max-w-330 items-center px-6 text-muted-foreground md:px-8">
                <Breadcrumbs :breadcrumbs="props.breadcrumbs" />
            </div>
        </div>
    </div>
</template>
