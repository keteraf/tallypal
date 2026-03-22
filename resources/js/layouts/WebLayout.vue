<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import WebHeader from '@/components/WebHeader.vue';
import type { BreadcrumbItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <WebHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>
