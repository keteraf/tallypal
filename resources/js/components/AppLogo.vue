<script setup lang="ts">
interface Props {
    showBeta?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

withDefaults(defineProps<Props>(), {
    showBeta: false,
    size: 'md',
});
</script>

<template>
    <span
        class="inline-flex items-baseline gap-2.5 font-serif font-medium tracking-[-0.01em]"
        :class="{
            'text-[18px]': size === 'sm',
            'text-[22px]': size === 'md',
            'text-[28px]': size === 'lg',
        }"
    >
        <span class="inline-block size-[7px] -translate-y-[2px] rounded-full bg-brand" aria-hidden="true" />
        <span class="text-foreground">tally<em class="not-italic-[0] font-medium" style="font-style: italic; color: var(--brand)">pal</em></span>
        <span
            v-if="showBeta"
            class="ml-1 rounded-[2px] border border-rule px-1.5 py-[3px] font-mono text-[10px] tracking-[0.12em] text-muted-foreground uppercase"
        >
            Beta
        </span>
    </span>
</template>
