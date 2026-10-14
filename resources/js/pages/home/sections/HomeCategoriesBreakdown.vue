<script setup lang="ts">
import { CardHeader } from '@/components/ui/card-header';
import { Dot } from '@/components/ui/dot';
import { MiniBar } from '@/components/ui/mini-bar';
import { breakdownRowColors } from '@/lib/category';
import { stats } from '@/routes';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { CategoryBreakdown, CurrencyTotal } from '../types';
import { formatCurrency, formatCurrencyTotals } from '../utils';

const props = defineProps<{
    categories: CategoryBreakdown[];
    currentMonthLabel: string;
    currencySymbol: string;
    decimals: number;
    /** This month's expenses in other currencies, listed apart and never added in (O-04). */
    otherCurrencies: CurrencyTotal[];
}>();

const { t } = useI18n();

const rows = computed(() => {
    const colors = breakdownRowColors(props.categories);

    return props.categories.map((cat, index) => ({
        ...cat,
        name: cat.is_other ? t('home.categories.other') : cat.name,
        colors: colors[index],
    }));
});
</script>

<template>
    <section v-if="categories.length > 0" class="rounded-card bg-surface p-4 sm:p-[22px]">
        <CardHeader :title="t('home.categories.title')" :action-label="t('home.categories.details')" :action-href="stats.url()">
            <template #meta>
                <span class="text-[12px] font-medium text-text-3">{{ currentMonthLabel }}</span>
            </template>
        </CardHeader>

        <div>
            <div v-for="cat in rows" :key="cat.name" class="flex items-center gap-3 border-t border-line py-3 first:border-t-0">
                <Dot :color="cat.colors.dot" />
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
