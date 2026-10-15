<script setup lang="ts">
/**
 * Bottom-of-page card holding a destructive action (Delete tally, Delete transaction): copy on the
 * left, the trigger from the default slot on the right; stacked below `sm` so the copy never wraps.
 */
defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <section class="mt-6 flex flex-col gap-4 rounded-card border border-line bg-surface p-6 sm:flex-row sm:items-center sm:justify-between md:p-7">
        <div class="min-w-0">
            <h2 class="text-[13.5px] font-medium tracking-[-0.01em] text-neg">{{ title }}</h2>
            <p class="mt-0.5 text-[11.5px] text-text-3">{{ description }}</p>
        </div>
        <div class="shrink-0 *:w-full sm:*:w-auto">
            <slot />
        </div>
    </section>
</template>
