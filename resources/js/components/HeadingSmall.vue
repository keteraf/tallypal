<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <header class="border-b border-rule-2 pb-4">
        <h3 class="font-serif text-2xl leading-tight tracking-[-0.01em] text-foreground">{{ title }}</h3>
        <p v-if="description" class="mt-1.5 text-sm text-muted-foreground">{{ description }}</p>
    </header>
</template>
