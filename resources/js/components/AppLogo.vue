<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

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
        class="inline-flex items-center gap-2 font-serif font-medium tracking-[-0.01em]"
        :class="{
            'text-[18px]': size === 'sm',
            'text-[22px]': size === 'md',
            'text-[28px]': size === 'lg',
        }"
    >
        <AppLogoIcon
            :class="{
                'size-5': size === 'sm',
                'size-6': size === 'md',
                'size-8': size === 'lg',
            }"
        />
        <span class="text-foreground">tally<em class="not-italic-[0] font-medium" style="font-style: italic; color: var(--brand)">pal</em></span>
        <span
            v-if="showBeta"
            class="ml-1 hidden rounded-[2px] border border-rule px-1.5 py-0.75 font-mono text-[10px] tracking-[0.12em] text-muted-foreground uppercase sm:inline-block"
        >
            Beta
        </span>
    </span>
</template>
