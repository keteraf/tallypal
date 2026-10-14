<script setup lang="ts">
import { TrendBadge } from '@/components/ui/trend-badge';
import { deltaCaption, formatCurrency, formatCurrencyTotals } from '@/pages/home/utils';
import type { StatsOwedToMe, StatsPeriod } from '@/pages/stats/types';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    currencySymbol: string;
    decimals: number;
    dailyAverage: number;
    previousPeriod: StatsPeriod | null;
    /** Daily average vs the previous period's; null hides the pill and its caption. */
    deltaPercentage: number | null;
    sharedTotalOut: number;
    /** Null when nothing was spent in the period. */
    sharedPercentage: number | null;
    /** Filtered to a tally with a single member. */
    isPrivateTally: boolean;
    owedToMe: StatsOwedToMe;
}>();

const { t, locale } = useI18n();

const delta = computed(() =>
    props.deltaPercentage === null
        ? null
        : {
              direction: props.deltaPercentage < 0 ? ('down' as const) : ('up' as const),
              value: `${Math.abs(props.deltaPercentage)}%`,
              label: deltaCaption(props.previousPeriod, locale.value, t),
          },
);

const money = (amount: number): string => `${props.currencySymbol} ${formatCurrency(amount, props.decimals)}`;

const sharedValue = computed(() => (props.sharedPercentage === null ? '—' : money(props.sharedTotalOut)));

const sharedSub = computed(() => {
    if (props.isPrivateTally) {
        return t('stats.kpi.shared_private');
    }

    return props.sharedPercentage === null ? null : t('stats.kpi.shared_sub', { percentage: props.sharedPercentage });
});

const owed = computed(() => money(props.owedToMe.amount_minor / 10 ** props.decimals));

const otherOwed = computed(() =>
    formatCurrencyTotals(
        props.owedToMe.other_currencies.map((owed) => ({
            amount: owed.amount_minor / 10 ** owed.decimals,
            currency_symbol: owed.currency_symbol,
            currency_code: owed.currency_code,
            decimals: owed.decimals,
        })),
    ),
);

const openBalances = computed(() => t('stats.kpi.open_balances', { count: props.owedToMe.open_balances }, props.owedToMe.open_balances));
</script>

<template>
    <div class="grid grid-cols-1 gap-3 md:gap-4">
        <div class="rounded-card bg-surface p-5">
            <div class="flex items-baseline justify-between gap-3">
                <span class="text-[12px] text-text-3">{{ t('stats.kpi.daily_average') }}</span>
                <span class="num text-[20px] font-semibold text-text">{{ money(dailyAverage) }}</span>
            </div>
            <div v-if="delta" class="mt-3">
                <TrendBadge tone="neutral" :direction="delta.direction" :value="delta.value" :label="delta.label" />
            </div>
        </div>

        <div class="rounded-card bg-surface p-5">
            <div class="flex items-baseline justify-between gap-3">
                <span class="text-[12px] text-text-3">{{ t('stats.kpi.shared_portion') }}</span>
                <span class="num text-[20px] font-semibold text-text">{{ sharedValue }}</span>
            </div>
            <p v-if="sharedSub" class="mt-3 text-[12px] text-text-3">{{ sharedSub }}</p>
        </div>

        <div class="rounded-card bg-surface p-5">
            <div class="flex items-baseline justify-between gap-3">
                <span class="text-[12px] text-text-3">{{ t('stats.kpi.owed_to_you') }}</span>
                <span class="num text-[20px] font-semibold text-pos">{{ owed }}</span>
            </div>
            <p class="mt-3 text-[12px] text-text-3">{{ openBalances }}</p>
            <p v-if="owedToMe.other_currencies.length" class="num mt-1 text-[12px] text-text-3">{{ t('stats.kpi.owed_also', { amounts: otherOwed }) }}</p>
        </div>
    </div>
</template>
