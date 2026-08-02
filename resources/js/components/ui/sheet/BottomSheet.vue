<script setup lang="ts">
import { X } from 'lucide-vue-next';
import Sheet from './Sheet.vue';
import SheetContent from './SheetContent.vue';
import SheetDescription from './SheetDescription.vue';
import SheetTitle from './SheetTitle.vue';

/**
 * Mobile bottom sheet: `ui/sheet` with `side="bottom"`, a grabber, rounded top
 * corners (`--radius-lg` = 28px) and `max-h-[88%]` — the `.sheet` pattern from the
 * mobile design source. Reka (via the underlying Dialog) provides the focus trap
 * and scroll lock. Pair with `ui/dialog` at `≥ md` (see `ResponsiveModal`).
 */
withDefaults(defineProps<{ title?: string; description?: string }>(), {
    title: '',
    description: '',
});

const open = defineModel<boolean>('open', { default: false });
</script>

<template>
    <Sheet v-model:open="open">
        <SheetContent side="bottom" class="max-h-[88%] gap-0 rounded-t-lg border-line bg-surface p-0 [&>button]:hidden">
            <div class="mx-auto mt-2.5 mb-1 h-1 w-9.5 shrink-0 rounded-full bg-surface-3" />

            <div class="flex shrink-0 items-center justify-between px-5 pt-2.5 pb-3.5">
                <SheetTitle class="text-[19px] font-semibold tracking-[-0.025em] text-text">{{ title }}</SheetTitle>
                <SheetDescription v-if="description" class="sr-only">{{ description }}</SheetDescription>
                <button
                    type="button"
                    aria-label="Close"
                    class="grid size-9 place-items-center rounded-full border border-line bg-surface text-text-2 transition-colors hover:bg-surface-2 hover:text-text"
                    @click="open = false"
                >
                    <X class="size-[15px]" />
                </button>
            </div>

            <div class="overflow-y-auto px-5 [scrollbar-color:var(--surface-3)_transparent] [scrollbar-width:thin] pb-[calc(1.375rem+env(safe-area-inset-bottom))]">
                <slot />
            </div>
        </SheetContent>
    </Sheet>
</template>
