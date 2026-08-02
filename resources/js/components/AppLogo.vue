<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Badge } from '@/components/ui/badge';

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
        class="inline-flex items-center gap-2 font-medium tracking-[-0.01em]"
        :class="{
            'text-lg': size === 'sm',
            'text-xl': size === 'md',
            'text-3xl': size === 'lg',
        }"
    >
        <AppLogoIcon
            :class="{
                'size-5': size === 'sm',
                'size-6': size === 'md',
                'size-8': size === 'lg',
            }"
        />
        <span class="text-text">tally<span class="text-accent">pal</span></span>
        <Badge v-if="showBeta" variant="outline" class="ml-1 hidden sm:inline-flex">Beta</Badge>
    </span>
</template>
