<script setup lang="ts">
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const sidebarNavItems = computed<NavItem[]>(() => [
    {
        title: t('settings.nav.profile'),
        href: '/settings/profile',
    },
    {
        title: t('settings.nav.password'),
        href: '/settings/password',
    },
    {
        title: t('settings.nav.appearance'),
        href: '/settings/appearance',
    },
]);

const page = usePage();

const currentPath = computed(() => page.url.split('?')[0]);
</script>

<template>
    <div class="mx-auto w-full max-w-330 px-5 py-6 pb-28 md:px-8 md:py-8">
        <div class="mb-[18px] md:mb-[26px]">
            <h1 class="text-[24px] font-semibold tracking-[-0.03em] text-text sm:text-[26px]">{{ $t('settings.title') }}</h1>
            <p class="mt-[3px] text-[13px] text-text-3">{{ $t('settings.subtitle') }}</p>
        </div>

        <div class="grid gap-10 lg:grid-cols-[200px_1fr] lg:gap-14">
            <aside>
                <h6 class="eyebrow mb-3.5">{{ $t('settings.nav.sections') }}</h6>
                <nav class="flex flex-col">
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center justify-between border-b border-line py-2.5 text-sm transition-colors"
                        :class="currentPath === item.href ? 'font-medium text-text' : 'text-text-3 hover:text-text'"
                    >
                        <span>{{ item.title }}</span>
                        <span v-if="currentPath === item.href" class="font-mono text-xs text-accent">●</span>
                    </Link>
                </nav>
            </aside>

            <div class="min-w-0">
                <section class="max-w-2xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
