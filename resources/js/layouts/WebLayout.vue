<script setup lang="ts">
import AppOverlays from '@/components/AppOverlays.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import BottomNav from '@/components/BottomNav.vue';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { useAddTransaction } from '@/composables/useAddTransaction';
import { useNewTally } from '@/composables/useNewTally';
import { stats } from '@/routes';
import tallies from '@/routes/tallies';
import type { BreadcrumbItem } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { useEventListener, useMediaQuery } from '@vueuse/core';
import { onMounted, ref, watch } from 'vue';

/**
 * The single responsive app shell (one WebLayout, no separate mobile layout):
 * - `< md`: full-width content + fixed bottom nav, no top header on primary tabs.
 * - `≥ md`: left sidebar shell — a rail on md–lg, expanded ≥ lg. A slim header
 *   (trigger + breadcrumbs) renders only for pages that pass `breadcrumbs`; the
 *   primary tabs (Home / Tallies / Stats) pass none, so their own top bar is the
 *   first thing in the content area.
 * The Add-tx / New-tally overlays, and the global `1`/`2`/`3`/`n` shortcuts, are
 * hosted once here.
 */
interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

// Start from the SSR cookie value so server and first client render agree (no
// hydration mismatch), then resolve to width once mounted: expanded ≥ lg, rail md–lg.
const sidebarOpen = ref<boolean>(page.props.sidebarOpen as boolean);
const isDesktop = useMediaQuery('(min-width: 1024px)');

onMounted(() => {
    sidebarOpen.value = isDesktop.value;
});

watch(isDesktop, (value) => {
    sidebarOpen.value = value;
});

const { isOpen: addTransactionOpen, open: openAddTransaction } = useAddTransaction();
const { isOpen: newTallyOpen } = useNewTally();

function isTypingTarget(target: EventTarget | null): boolean {
    if (!(target instanceof HTMLElement)) {
        return false;
    }

    return target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT' || target.isContentEditable;
}

// `1`/`2`/`3` jump to Home/Tallies/Stats, `n` opens Add transaction. Ignored while
// typing in a field or while an overlay (Add-tx / New-tally) is open.
useEventListener(window, 'keydown', (event: KeyboardEvent) => {
    if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.altKey) {
        return;
    }

    if (addTransactionOpen.value || newTallyOpen.value || isTypingTarget(event.target)) {
        return;
    }

    switch (event.key) {
        case '1':
            // `home.url()` resolves to '/' (the marketing route, routes/web.php:15), not
            // the dashboard — use the literal path, same as AppSidebar's nav item.
            router.visit('/home');
            break;
        case '2':
            router.visit(tallies.index.url());
            break;
        case '3':
            router.visit(stats.url());
            break;
        case 'n':
            event.preventDefault();
            openAddTransaction();
            break;
    }
});
</script>

<template>
    <SidebarProvider v-model:open="sidebarOpen">
        <AppSidebar />

        <SidebarInset class="min-w-0">
            <div v-if="breadcrumbs.length > 0" class="hidden md:block">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            </div>

            <div class="flex-1 pb-[calc(68px+env(safe-area-inset-bottom))] md:pb-0">
                <slot />
            </div>
        </SidebarInset>

        <BottomNav />
        <AppOverlays />
    </SidebarProvider>
</template>
