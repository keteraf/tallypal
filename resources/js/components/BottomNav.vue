<script setup lang="ts">
import BottomNavItem from '@/components/BottomNavItem.vue';
import { useAddTransaction } from '@/composables/useAddTransaction';
import { stats, you } from '@/routes';
import talliesRoutes from '@/routes/tallies';
import { usePage } from '@inertiajs/vue3';
import { ChartColumn, House, Plus, Tally5, User } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * Mobile primary navigation (`< md`): Home · Tallies · center FAB (Add-tx) · Stats · You.
 * Active tab derives from `usePage().url`; the fixed bar reserves
 * `env(safe-area-inset-bottom)` and the shell pads content to clear its ~68px height.
 * Hidden at `≥ md`, where the sidebar shell takes over.
 */
const { t } = useI18n();
const page = usePage();
const { open: openAddTransaction } = useAddTransaction();

const currentPath = computed(() => page.url.split('?')[0]);

const isActive = (href: string): boolean => currentPath.value === href || currentPath.value.startsWith(`${href}/`);
</script>

<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-line px-2 pt-2 md:hidden"
        :style="{
            paddingBottom: 'calc(8px + env(safe-area-inset-bottom))',
            background: 'color-mix(in oklch, var(--surface) 88%, transparent)',
            backdropFilter: 'blur(18px)',
            WebkitBackdropFilter: 'blur(18px)',
        }"
    >
        <BottomNavItem :label="t('navigation.home')" :icon="House" href="/home" :active="isActive('/home')" />
        <BottomNavItem :label="t('navigation.tallies')" :icon="Tally5" :href="talliesRoutes.index.url()" :active="isActive('/tallies')" />

        <button
            type="button"
            :aria-label="t('transactions.add_button')"
            class="mx-1 grid size-[52px] shrink-0 place-items-center rounded-full bg-accent text-accent-ink shadow-fab transition hover:brightness-110"
            @click="openAddTransaction()"
        >
            <Plus class="size-[22px]" :stroke-width="2.5" />
        </button>

        <BottomNavItem :label="t('navigation.stats')" :icon="ChartColumn" :href="stats.url()" :active="isActive('/stats')" />
        <BottomNavItem :label="t('navigation.you')" :icon="User" :href="you.url()" :active="isActive('/settings')" />
    </nav>
</template>
