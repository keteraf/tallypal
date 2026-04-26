<script setup lang="ts">
import type { CategoryBreakdown } from '../types';
import { dotClass, formatCurrency } from '../utils';

defineProps<{
    categories: CategoryBreakdown[];
    currentMonthLabel: string;
    currencySymbol: string;
    decimals: number;
}>();
</script>

<template>
    <section v-if="categories.length > 0" class="overflow-hidden rounded-sm border border-rule">
        <div class="flex items-baseline justify-between border-b border-rule-2 px-7 py-5">
            <span class="font-serif text-xl text-foreground italic">{{ $t('home.categories.title') }}</span>
            <span class="font-mono text-[11px] tracking-[0.06em] text-muted-foreground uppercase">{{ currentMonthLabel }}</span>
        </div>
        <div class="py-2">
            <div
                v-for="(cat, idx) in categories"
                :key="cat.name"
                class="grid grid-cols-[18px_1fr_auto_auto] items-center gap-3.5 border-b border-rule-2 px-7 py-3 last:border-b-0"
            >
                <span class="size-2 rounded-full" :class="dotClass(idx)" />
                <span class="truncate text-[14.5px] text-foreground">{{ cat.name }}</span>
                <span class="font-mono text-[11px] tracking-[0.06em] text-muted-foreground">{{ cat.percentage }}%</span>
                <span class="tabular font-mono text-[14px] text-foreground">{{ currencySymbol }} {{ formatCurrency(cat.amount, decimals) }}</span>
            </div>
        </div>
    </section>
</template>
