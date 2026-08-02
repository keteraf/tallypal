<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <header class="border-b border-line pb-4">
        <h3 class="text-2xl leading-tight font-semibold tracking-[-0.01em] text-text">{{ title }}</h3>
        <p v-if="description" class="mt-1.5 text-sm text-text-3">{{ description }}</p>
    </header>
</template>
