<script setup lang="ts">
import { CardHeader } from '@/components/ui/card-header';
import { Dot } from '@/components/ui/dot';
import { EmptyState } from '@/components/ui/empty-state';
import { MiniBar } from '@/components/ui/mini-bar';
import { breakdownRowColors } from '@/lib/category';
import type { CategoryBreakdown } from '@/pages/home/types';
import { formatCurrency, formatCurrencyTotals } from '@/pages/home/utils';
import type { StatsCurrencyTotal } from '@/pages/stats/types';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    categories: CategoryBreakdown[];
    currencySymbol: string;
    decimals: number;
    /** The range's expenses in other currencies, listed apart and never added in (O-04). */
    otherCurrencies: StatsCurrencyTotal[];
}>();

const { t } = useI18n();

const rows = computed(() => {
    const colors = breakdownRowColors(props.categories);

    return props.categories.map((cat, index) => ({
        ...cat,
        name: cat.is_other ? t('stats.categories.other') : cat.is_uncategorised ? t('categories.uncategorised') : cat.name,
        colors: colors[index],
    }));
});
</script>

<template>
    <EmptyState v-if="categories.length === 0" :title="t('stats.categories.empty_title')" :description="t('stats.categories.empty_description')" />

    <section v-else class="rounded-card bg-surface p-4 sm:p-[22px]">
        <CardHeader :title="t('stats.categories.title')" />

        <div>
            <div v-for="cat in rows" :key="cat.name" class="flex items-center gap-3 border-t border-line py-3 first:border-t-0">
                <span v-if="cat.is_uncategorised" class="w-2 shrink-0 text-center text-[13px] leading-none text-text-3" aria-hidden="true">◌</span>
                <Dot v-else :color="cat.colors.dot" />
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-[14px] text-text">{{ cat.name }}</span>
                    <MiniBar class="mt-[7px]" :value="cat.bar_fill" :color="cat.colors.bar" />
                </span>
                <span class="shrink-0 text-right">
                    <span class="num block text-[14px] font-medium text-text">{{ currencySymbol }} {{ formatCurrency(cat.amount, decimals) }}</span>
                    <span class="num mt-1 block text-[11px] text-text-3">{{ cat.percentage }}%</span>
                </span>
            </div>
        </div>

        <p v-if="otherCurrencies.length > 0" class="num border-t border-line pt-3 text-[12px] text-text-3">
            {{ t('common.other_currencies', { amounts: formatCurrencyTotals(otherCurrencies) }) }}
        </p>
    </section>
</template>
