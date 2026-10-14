<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { useLanguageSwitcher } from '@/composables/useLanguageSwitcher';
import { dashboard, home, login, logout, register } from '@/routes';
import * as profile from '@/routes/profile';
import type { NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { House, Languages, LayoutGrid, LogOut, Menu, Settings, Tally5 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage();
const auth = computed(() => page.props.auth);
const { t } = useI18n();

const sheetOpen = ref(false);
const { languages, locale, setLocale } = useLanguageSwitcher();

const isSuperAdmin = computed(() => auth.value.user?.roles?.includes('Super Admin'));
const isCurrentRoute = computed(() => (url: string) => page.url === url);

const anchors = [
    { label: 'navigation.how', href: '#how' },
    { label: 'navigation.features', href: '#features' },
    { label: 'navigation.pricing', href: '#pricing' },
    { label: 'navigation.faq', href: '#faq' },
];

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        { title: t('navigation.home'), href: '/home', icon: House },
        { title: t('tallies.title'), href: '/tallies', icon: Tally5 },
    ];

    if (isSuperAdmin.value) {
        items.unshift({ title: t('navigation.dashboard'), href: dashboard.url(), icon: LayoutGrid });
    }

    return items;
});
</script>

<template>
    <header class="sticky top-0 z-[60] border-b border-chrome-line bg-chrome-bg backdrop-blur-[22px] backdrop-saturate-[1.6]">
        <div class="mx-auto flex max-w-site items-center gap-5 px-7 py-3.5">
            <Link :href="home.url()" class="flex shrink-0 items-center">
                <AppLogo show-beta />
            </Link>

            <nav class="ml-auto hidden items-center gap-1.5 min-[880px]:flex">
                <template v-if="auth.user">
                    <Link
                        v-for="item in mainNavItems"
                        :key="item.title"
                        :href="item.href"
                        class="rounded-full px-[13px] py-[9px] text-nav-lnk font-medium transition-colors"
                        :class="isCurrentRoute(item.href) ? 'text-text' : 'text-text-2 hover:bg-surface hover:text-text'"
                    >
                        {{ item.title }}
                    </Link>
                    <span class="mx-1 h-4 w-px bg-line" />
                </template>
                <a
                    v-for="anchor in anchors"
                    :key="anchor.href"
                    :href="anchor.href"
                    class="rounded-full px-[13px] py-[9px] text-nav-lnk font-medium text-text-2 transition-colors hover:bg-surface hover:text-text"
                >
                    {{ t(anchor.label) }}
                </a>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <ThemeToggle />

                <div v-if="auth.user" class="hidden min-[880px]:block">
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <button
                                type="button"
                                class="relative inline-flex size-9 items-center justify-center rounded-full border border-line text-text-2 transition-colors hover:border-text hover:text-text"
                            >
                                <Avatar class="size-7 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                    <AvatarFallback
                                        class="rounded-full bg-[color-mix(in_oklch,var(--accent)_20%,transparent)] font-mono text-[11px] text-accent"
                                    >
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

                <template v-else>
                    <Button as-child variant="surface" class="hidden min-[880px]:inline-flex">
                        <Link :href="login.url()">{{ $t('common.login') }}</Link>
                    </Button>
                    <Button as-child class="hidden min-[880px]:inline-flex">
                        <Link :href="register.url()">{{ $t('welcome.hero.cta_primary') }}</Link>
                    </Button>
                </template>

                <!-- Below 880px: hamburger with anchors (+ app nav / user block when signed in). -->
                <div class="min-[880px]:hidden">
                    <Sheet v-model:open="sheetOpen">
                        <SheetTrigger :as-child="true">
                            <button
                                type="button"
                                class="inline-flex size-9 items-center justify-center rounded-full border border-line text-text-2 transition-colors hover:border-text hover:text-text"
                            >
                                <Menu class="size-4" />
                                <span class="sr-only">{{ $t('common.navigation_menu') }}</span>
                            </button>
                        </SheetTrigger>
                        <SheetContent side="left" class="flex w-80 flex-col overflow-y-auto border-line bg-surface p-7">
                            <SheetTitle class="sr-only">{{ $t('common.navigation_menu') }}</SheetTitle>
                            <SheetHeader class="flex justify-start p-0 text-left">
                                <AppLogo />
                            </SheetHeader>
                            <nav class="mt-8 flex flex-1 flex-col gap-1">
                                <template v-if="auth.user">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center justify-between gap-3 border-b border-line py-3 text-sm text-text-2 transition-colors hover:text-text"
                                        :class="{ 'font-medium text-text': isCurrentRoute(item.href) }"
                                        @click="sheetOpen = false"
                                    >
                                        <span class="flex items-center gap-3">
                                            <component :is="item.icon" class="size-4" />
                                            {{ item.title }}
                                        </span>
                                    </Link>
                                    <div class="my-2 border-t border-line" />
                                </template>
                                <a
                                    v-for="anchor in anchors"
                                    :key="anchor.href"
                                    :href="anchor.href"
                                    class="flex items-center gap-3 border-b border-line py-3 text-sm text-text-2 transition-colors hover:text-text"
                                    @click="sheetOpen = false"
                                >
                                    {{ t(anchor.label) }}
                                </a>
                            </nav>

                            <div v-if="auth.user" class="mt-auto border-t border-line pt-4">
                                <div class="flex items-center gap-3 border-b border-line pb-4">
                                    <Avatar class="size-9 overflow-hidden rounded-full">
                                        <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                        <AvatarFallback
                                            class="rounded-full bg-[color-mix(in_oklch,var(--accent)_20%,transparent)] font-mono text-[11px] text-accent"
                                        >
                                            {{ getInitials(auth.user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="flex min-w-0 flex-col">
                                        <span class="truncate text-sm font-medium text-text">{{ auth.user.name }}</span>
                                        <span class="truncate text-xs text-text-3">{{ auth.user.email }}</span>
                                    </div>
                                </div>

                                <Link
                                    :href="profile.edit.url()"
                                    class="flex items-center gap-3 border-b border-line py-3 text-sm text-text-2 transition-colors hover:text-text"
                                    @click="sheetOpen = false"
                                >
                                    <Settings class="size-4" />
                                    {{ $t('user_menu.settings') }}
                                </Link>

                                <div class="flex items-center gap-3 border-b border-line py-3 text-sm text-text-2">
                                    <Languages class="size-4 shrink-0" />
                                    <span class="flex-1">{{ $t('user_menu.language') }}</span>
                                    <div class="flex gap-3">
                                        <button
                                            v-for="lang in languages"
                                            :key="lang.code"
                                            type="button"
                                            class="text-xs transition-colors"
                                            :class="locale === lang.code ? 'font-medium text-text' : 'hover:text-text'"
                                            @click="setLocale(lang.code)"
                                        >
                                            {{ lang.name }}
                                        </button>
                                    </div>
                                </div>

                                <Link
                                    :href="logout.url()"
                                    method="post"
                                    as="button"
                                    class="flex w-full items-center gap-3 py-3 text-sm text-text-2 transition-colors hover:text-text"
                                >
                                    <LogOut class="size-4" />
                                    {{ $t('common.logout') }}
                                </Link>
                            </div>
                            <template v-else>
                                <div class="mt-auto flex flex-col gap-3 border-t border-line pt-4">
                                    <Button as-child variant="surface">
                                        <Link :href="login.url()" @click="sheetOpen = false">{{ $t('common.login') }}</Link>
                                    </Button>
                                    <Button as-child>
                                        <Link :href="register.url()" @click="sheetOpen = false">{{ $t('welcome.hero.cta_primary') }}</Link>
                                    </Button>
                                </div>
                            </template>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </div>
    </header>
</template>
