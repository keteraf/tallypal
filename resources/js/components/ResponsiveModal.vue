<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { BottomSheet } from '@/components/ui/sheet';
import { useMediaQuery } from '@vueuse/core';

/**
 * Viewport-aware overlay: a bottom sheet `< md`, a centered `ui/dialog` modal
 * `≥ md` (the Add-tx / New-tally shell decided in the migration plan). Both branches
 * are inert until opened — reka renders portal content only while `open` — so the
 * `v-if` swap on hydration touches no visible DOM. Focus trap + scroll lock come
 * from reka. The desktop dialog is capped at the viewport height: the header stays put
 * and the body scrolls (forms keep their action row `sticky bottom-0`). `useMediaQuery` is SSR-safe (returns `false` on the server).
 */
withDefaults(defineProps<{ title?: string; description?: string }>(), {
    title: '',
    description: '',
});

const open = defineModel<boolean>('open', { default: false });

const isDesktop = useMediaQuery('(min-width: 768px)');
</script>

<template>
    <Dialog v-if="isDesktop" v-model:open="open">
        <DialogContent class="gap-0 rounded-lg border-line bg-surface p-0 sm:max-w-[500px]">
            <div class="shrink-0 pt-[22px] pr-[72px] pb-3.5 pl-6">
                <DialogTitle class="text-[19px] font-semibold tracking-[-0.025em] text-text">{{ title }}</DialogTitle>
                <DialogDescription v-if="description" class="sr-only">{{ description }}</DialogDescription>
            </div>
            <div class="min-h-0 flex-1 [scrollbar-width:thin] [scrollbar-color:var(--surface-3)_transparent] overflow-y-auto px-6 pb-6">
                <slot />
            </div>
        </DialogContent>
    </Dialog>

    <BottomSheet v-else v-model:open="open" :title="title" :description="description">
        <slot />
    </BottomSheet>
</template>
