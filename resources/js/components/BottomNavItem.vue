<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';

/**
 * A single slot in the mobile bottom tab bar: icon + label. When active, the label
 * turns `--text` and only the icon tints `--accent`, matching the design. A
 * `disabled` item renders as an inert button rather than a broken link, for tabs
 * whose route does not exist yet.
 */
withDefaults(
    defineProps<{
        label: string;
        icon: Component;
        href?: string;
        active?: boolean;
        disabled?: boolean;
    }>(),
    {
        href: undefined,
        active: false,
        disabled: false,
    },
);
</script>

<template>
    <component
        :is="disabled || !href ? 'button' : Link"
        :href="disabled ? undefined : href"
        :type="disabled || !href ? 'button' : undefined"
        :disabled="disabled || undefined"
        :aria-current="active ? 'page' : undefined"
        :aria-disabled="disabled || undefined"
        class="flex min-h-12 flex-1 flex-col items-center justify-center gap-1 py-1.5 transition-colors"
        :class="[active ? 'text-text' : 'text-text-3', disabled ? 'opacity-40' : 'hover:text-text']"
    >
        <component :is="icon" class="size-[21px]" :class="active ? 'text-accent' : undefined" :stroke-width="1.9" />
        <span class="text-[10.5px] font-medium tracking-[-0.005em]">{{ label }}</span>
    </component>
</template>
