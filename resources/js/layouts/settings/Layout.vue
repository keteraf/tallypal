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
    <div class="mx-auto w-full max-w-[1320px] px-6 py-10 md:px-8">
        <div class="mb-9 border-b border-rule pb-6">
            <div class="eyebrow mb-3">{{ $t('settings.crumb') }}</div>
            <h1 class="m-0 font-serif text-[clamp(36px,5vw,56px)] leading-none tracking-[-0.02em]">
                {{ $t('settings.title_lead') }}
                <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ $t('settings.title_em') }}</em>
            </h1>
        </div>

        <div class="grid gap-10 lg:grid-cols-[200px_1fr] lg:gap-14">
            <aside>
                <h6 class="eyebrow mb-3.5">{{ $t('settings.nav.sections') }}</h6>
                <nav class="flex flex-col">
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center justify-between border-b border-rule-2 py-2.5 text-sm transition-colors"
                        :class="currentPath === item.href ? 'font-medium text-foreground' : 'text-muted-foreground hover:text-foreground'"
                    >
                        <span>{{ item.title }}</span>
                        <span v-if="currentPath === item.href" class="font-mono text-[10px] tracking-[0.06em] text-brand">●</span>
                    </Link>
                </nav>
            </aside>

            <div class="min-w-0">
                <section class="max-w-[680px] space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
