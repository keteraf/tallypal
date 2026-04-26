<script setup lang="ts">
import { computed } from 'vue';
import type { WeekHistory } from '../types';
import { formatCurrency } from '../utils';

const props = defineProps<{
    weeks: WeekHistory[];
    currencySymbol: string;
    decimals: number;
}>();

const maxWeekAmount = computed(() => {
    const max = Math.max(0, ...props.weeks.map((w) => w.amount));
    return max === 0 ? 1 : max;
});
</script>

<template>
    <section v-if="weeks.length > 0" class="overflow-hidden rounded-sm border border-rule">
        <div class="flex items-baseline justify-between border-b border-rule-2 px-7 py-5">
            <span class="font-serif text-xl text-foreground italic">{{ $t('home.history.title') }}</span>
        </div>
        <div class="px-7 py-6">
            <div class="flex h-44 items-end gap-1.5 border-b border-rule px-1 pb-1">
                <div
                    v-for="(week, idx) in weeks"
                    :key="idx"
                    class="flex-1 rounded-t-sm transition-all"
                    :class="week.is_current ? 'bg-brand opacity-100' : 'bg-foreground/80'"
                    :style="{ height: `${Math.max(2, (week.amount / maxWeekAmount) * 100)}%` }"
                    :title="`${week.label}: ${currencySymbol} ${formatCurrency(week.amount, decimals)}`"
                />
            </div>
            <div class="mt-3 flex justify-between px-1 font-mono text-[10px] tracking-[0.08em] text-muted-foreground uppercase">
                <span>{{ weeks[0]?.label }}</span>
                <span v-if="weeks.length > 6">{{ weeks[Math.floor(weeks.length / 2)]?.label }}</span>
                <span>{{ weeks[weeks.length - 1]?.label }} · {{ $t('home.history.now') }}</span>
            </div>
            <div class="mt-4 flex gap-5 font-mono text-[11px] tracking-[0.06em] text-muted-foreground">
                <span class="flex items-center gap-1.5"
                    ><span class="inline-block size-2.5 rounded-sm bg-foreground/80" />{{ $t('home.history.spent') }}</span
                >
                <span class="flex items-center gap-1.5"
                    ><span class="inline-block size-2.5 rounded-sm bg-brand" />{{ $t('home.history.this_week') }}</span
                >
            </div>
        </div>
    </section>
</template>
