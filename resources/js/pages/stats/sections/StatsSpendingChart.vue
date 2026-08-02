<script setup lang="ts">
import { formatCurrency, formatCurrencyTotals } from '@/pages/home/utils';
import type { StatsCurrencyTotal, StatsSeriesPoint } from '@/pages/stats/types';
import { VisAxis, VisGroupedBar, VisGroupedBarSelectors, VisXYContainer } from '@unovis/vue';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    label: string;
    amount: number;
    currencySymbol: string;
    decimals: number;
    series: StatsSeriesPoint[];
    /** The range's expenses in other currencies, listed apart and never added in (O-04). */
    otherCurrencies: StatsCurrencyTotal[];
}>();

const { t } = useI18n();

type SeriesEntry = StatsSeriesPoint & { index: number };

const chartData = computed<SeriesEntry[]>(() => props.series.map((point, index) => ({ ...point, index })));

const xAccessor = (d: SeriesEntry) => d.index;
const yAccessor = (d: SeriesEntry) => d.amount;

/** `VisGroupedBar` is generic, so `InstanceType` can't describe it; only `render` is used. */
const chart = ref<{ component?: { render: (duration?: number) => void } }>();
const hoveredIndex = ref<number | null>(null);
const colorAccessor = (d: SeriesEntry) =>
    d.is_current ? 'var(--accent)' : hoveredIndex.value === d.index ? 'var(--accent-dim)' : 'var(--surface-3)';
const barEvents = {
    [VisGroupedBarSelectors.bar]: {
        mouseenter: (d: SeriesEntry) => {
            hoveredIndex.value = d.index;
            chart.value?.component?.render(0);
        },
        mouseleave: () => {
            hoveredIndex.value = null;
            chart.value?.component?.render(0);
        },
    },
};

const isPhone = useMediaQuery('(max-width: 640px)');
const chartHeight = computed(() => (isPhone.value ? 160 : 220));

/** Design bar gaps: 5px on phones, 8px otherwise. */
const barPadding = computed(() => (isPhone.value ? 0.2 : 0.12));

const formatXTick = (value: number) => props.series[value]?.label ?? '';

const AXIS_MAX_LABELS = 6;

/** Every bar is labelled up to 7 buckets; beyond that, 6 evenly spaced labels including the first and last. */
const xTickValues = computed<number[]>(() => {
    const count = props.series.length;

    if (count <= AXIS_MAX_LABELS + 1) {
        return props.series.map((_, index) => index);
    }

    return Array.from({ length: AXIS_MAX_LABELS }, (_, i) => Math.round((i * (count - 1)) / (AXIS_MAX_LABELS - 1)));
});

/** First radius value of the `--radius-bar` token (the rounded end of a bar). */
const barRadius = computed(() => {
    if (typeof window === 'undefined') {
        return 6;
    }

    return Number.parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--radius-bar')) || 6;
});
</script>

<template>
    <div class="rounded-card bg-surface p-5">
        <div class="mb-1 flex items-baseline justify-between">
            <span class="text-[12px] text-text-3">{{ label }}</span>
            <span class="num text-[20px] font-semibold text-text">{{ currencySymbol }} {{ formatCurrency(amount, decimals) }}</span>
        </div>
        <p v-if="otherCurrencies.length > 0" class="num mb-1 text-right text-[12px] text-text-3">
            {{ t('common.other_currencies', { amounts: formatCurrencyTotals(otherCurrencies) }) }}
        </p>

        <VisXYContainer
            class="axis-mono"
            :data="chartData"
            :height="chartHeight"
            :margin="{ left: 0, right: 0, top: 8, bottom: 18 }"
            :y-domain="[0, undefined]"
        >
            <VisGroupedBar
                ref="chart"
                :x="xAccessor"
                :y="yAccessor"
                :color="colorAccessor"
                :rounded-corners="barRadius"
                :bar-padding="barPadding"
                :events="barEvents"
            />
            <VisAxis
                type="x"
                :x="xAccessor"
                :tick-format="formatXTick"
                :tick-line="false"
                :domain-line="false"
                :grid-line="false"
                :tick-values="xTickValues"
            />
        </VisXYContainer>
    </div>
</template>

<style scoped>
.axis-mono {
    --vis-axis-tick-label-font-size: var(--text-axis);
    --vis-axis-tick-label-text-color: var(--text-3);
}

.axis-mono :deep(.tick text) {
    font-family: var(--font-mono);
    letter-spacing: var(--text-axis--letter-spacing);
}
</style>
