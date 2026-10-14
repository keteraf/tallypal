<script setup lang="ts">
import AppHead from '@/components/seo/AppHead.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuRadioGroup, DropdownMenuRadioItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SegmentedControl } from '@/components/ui/segmented-control';
import WebLayout from '@/layouts/WebLayout.vue';
import type { CategoryBreakdown } from '@/pages/home/types';
import StatsActivity from '@/pages/stats/sections/StatsActivity.vue';
import StatsCategoryBreakdown from '@/pages/stats/sections/StatsCategoryBreakdown.vue';
import StatsEmptyState from '@/pages/stats/sections/StatsEmptyState.vue';
import StatsKpiStack from '@/pages/stats/sections/StatsKpiStack.vue';
import StatsSpendingChart from '@/pages/stats/sections/StatsSpendingChart.vue';
import type {
    StatsActivityItem,
    StatsCurrencyTotal,
    StatsOwedToMe,
    StatsPeriod,
    StatsRangeValue,
    StatsSeriesPoint,
    StatsTallyOption,
} from '@/pages/stats/types';
import { stats } from '@/routes';
import { router } from '@inertiajs/vue3';
import { ListFilter, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    range: StatsRangeValue;
    range_label: string;
    total_out: number;
    currency_symbol: string;
    currency_code: string;
    decimals: number;
    series: StatsSeriesPoint[];
    categories: CategoryBreakdown[];
    other_currencies: StatsCurrencyTotal[];
    /** B-09 KPIs, in `currency_code` (the primary currency, or the filtered tally's own)(F-075). */
    daily_average: number;
    previous_total_out: number;
    previous_daily_average: number;
    previous_period: StatsPeriod | null;
    /** Daily average vs the previous period's daily average, one decimal; null when the previous one is 0. */
    delta_percentage: number | null;
    shared_total_out: number;
    /** Null when nothing was spent in the period. */
    shared_percentage: number | null;
    owed_to_me: StatsOwedToMe;
    /** Latest 20 transactions in range, each in its own currency (F-077). */
    activity: StatsActivityItem[];
    /** The tally filter and its options (F-071). */
    filter: { tally_uuid: string | null };
    tallies: StatsTallyOption[];
}>();

const { t } = useI18n();

const rangeOptions = computed(() => [
    { value: 'week' as const, label: t('stats.range.week') },
    { value: 'month' as const, label: t('stats.range.month') },
    { value: 'year' as const, label: t('stats.range.year') },
]);

const selectedRange = ref<StatsRangeValue>(props.range);

watch(
    () => props.range,
    (value) => {
        selectedRange.value = value;
    },
);

/** Every visit keeps the other param: the range survives a filter change and the tally survives a range switch. */
const visit = (range: StatsRangeValue, tally: string | null): void => {
    router.get(stats.url(), tally === null ? { range } : { range, tally }, { preserveScroll: true, preserveState: true, replace: true });
};

watch(selectedRange, (value) => {
    if (value === props.range) return;

    visit(value, props.filter.tally_uuid);
});

const filteredTally = computed(() => props.tallies.find((tally) => tally.uuid === props.filter.tally_uuid) ?? null);

const filterValue = computed(() => props.filter.tally_uuid ?? '');

const selectTally = (value: unknown): void => {
    const uuid = typeof value === 'string' && value !== '' ? value : null;

    if (uuid !== props.filter.tally_uuid) {
        visit(props.range, uuid);
    }
};

const subLabel = computed(() =>
    filteredTally.value === null
        ? t('stats.filter.sub_all', { period: props.range_label })
        : t('stats.filter.sub_tally', { period: props.range_label, tally: filteredTally.value.name }),
);

const hasData = computed(() => props.series.length > 0 || props.categories.length > 0);

const chartLabel = computed(() => {
    if (props.range === 'week') return t('stats.chart.out_week');
    if (props.range === 'year') return t('stats.chart.out_year');
    return t('stats.chart.out_month');
});
</script>

<template>
    <AppHead :title="$t('stats.title')" :noindex="true" />

    <WebLayout>
        <div class="mx-auto w-full max-w-330 px-5 py-6 pb-28 md:px-8 md:py-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <h1 class="text-[24px] font-semibold tracking-[-0.03em] text-text sm:text-[26px]">{{ $t('stats.title') }}</h1>
                    <p class="mt-[3px] text-[13px] text-text-3">{{ subLabel }}</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <SegmentedControl
                        v-if="hasData || filteredTally"
                        v-model="selectedRange"
                        variant="mobile"
                        :options="rangeOptions"
                        class="w-auto max-w-xs"
                    />
                    <DropdownMenu v-if="tallies.length > 0">
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="max-w-64 max-[640px]:hidden">
                                <ListFilter />
                                <span class="truncate">{{
                                    filteredTally ? $t('stats.filter.button_active', { tally: filteredTally.name }) : $t('stats.filter.button')
                                }}</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuRadioGroup :model-value="filterValue" @update:model-value="selectTally">
                                <DropdownMenuRadioItem value="">{{ $t('stats.filter.all_tallies') }}</DropdownMenuRadioItem>
                                <DropdownMenuRadioItem v-for="tally in tallies" :key="tally.uuid" :value="tally.uuid">{{
                                    tally.name
                                }}</DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <button
                v-if="filteredTally"
                type="button"
                class="mb-4 inline-flex max-w-full items-center gap-1.5 rounded-full bg-surface-2 py-1.5 pr-2.5 pl-3 text-[13px] text-text min-[641px]:hidden"
                :aria-label="$t('stats.filter.clear', { tally: filteredTally.name })"
                @click="selectTally(null)"
            >
                <span class="truncate">{{ filteredTally.name }}</span>
                <X class="size-3.5 shrink-0 text-text-3" />
            </button>

            <StatsEmptyState v-if="!hasData && !filteredTally" />

            <div v-else class="grid grid-cols-1 gap-3 min-[1101px]:grid-cols-12 md:gap-4">
                <StatsSpendingChart
                    class="min-[1101px]:col-span-8"
                    :label="chartLabel"
                    :amount="total_out"
                    :currency-symbol="currency_symbol"
                    :decimals="decimals"
                    :series="series"
                    :other-currencies="other_currencies"
                />

                <StatsKpiStack
                    class="min-[1101px]:col-span-4"
                    :currency-symbol="currency_symbol"
                    :decimals="decimals"
                    :daily-average="daily_average"
                    :previous-period="previous_period"
                    :delta-percentage="delta_percentage"
                    :shared-total-out="shared_total_out"
                    :shared-percentage="shared_percentage"
                    :is-private-tally="filteredTally !== null && !filteredTally.is_shared"
                    :owed-to-me="owed_to_me"
                />

                <StatsCategoryBreakdown
                    class="min-[1101px]:col-span-5"
                    :categories="categories"
                    :currency-symbol="currency_symbol"
                    :decimals="decimals"
                    :other-currencies="other_currencies"
                />

                <StatsActivity class="min-[1101px]:col-span-7" :activity="activity" />
            </div>
        </div>
    </WebLayout>
</template>
