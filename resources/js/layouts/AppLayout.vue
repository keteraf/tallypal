<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <WebLayout :breadcrumbs="breadcrumbs">
        <slot />
    </WebLayout>
</template>
