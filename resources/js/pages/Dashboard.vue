<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { CurveType } from '@unovis/ts';
import { VisAxis, VisGroupedBar, VisLine, VisXYContainer } from '@unovis/vue';
import { ArrowLeftRight, BookOpen, Mail, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface TimeSeriesPoint {
    date: string;
    count: number;
}

interface DashboardStats {
    total_users: number;
    new_users_30d: number;
    total_tallies: number;
    shared_tallies: number;
    total_transactions: number;
    pending_invitations: number;
}

const props = defineProps<{
    stats: DashboardStats;
    usersOverTime: TimeSeriesPoint[];
    talliesOverTime: TimeSeriesPoint[];
    transactionsOverTime: TimeSeriesPoint[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('common.dashboard'),
        href: '/dashboard',
    },
];

type ChartPoint = { date: Date; count: number };

const toChartData = (series: TimeSeriesPoint[]): ChartPoint[] => series.map((d) => ({ date: new Date(d.date), count: d.count }));

const usersChartData = computed(() => toChartData(props.usersOverTime));
const talliesChartData = computed(() => toChartData(props.talliesOverTime));
const transactionsChartData = computed(() => toChartData(props.transactionsOverTime));

const xAccessor = (d: ChartPoint) => d.date;
const yAccessor = (d: ChartPoint) => d.count;

const formatXTick = (d: number | Date) => {
    const date = typeof d === 'number' ? new Date(d) : d;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const stats = computed(() => [
    {
        icon: Users,
        label: t('dashboard.total_users'),
        value: props.stats.total_users.toLocaleString(),
        helper: `+${props.stats.new_users_30d} ${t('dashboard.this_month')}`,
    },
    {
        icon: BookOpen,
        label: t('dashboard.total_tallies'),
        value: props.stats.total_tallies.toLocaleString(),
        helper: `${props.stats.shared_tallies} ${t('dashboard.shared')}`,
    },
    {
        icon: ArrowLeftRight,
        label: t('dashboard.total_transactions'),
        value: props.stats.total_transactions.toLocaleString(),
        helper: t('dashboard.all_time'),
    },
    {
        icon: Mail,
        label: t('dashboard.pending_invitations'),
        value: props.stats.pending_invitations.toLocaleString(),
        helper: t('dashboard.awaiting_response'),
    },
]);

const charts = computed(() => [
    { label: t('dashboard.users_over_time'), data: usersChartData.value, color: 'var(--c1)', type: 'bar' as const },
    { label: t('dashboard.tallies_over_time'), data: talliesChartData.value, color: 'var(--c2)', type: 'bar' as const },
    { label: t('dashboard.transactions_over_time'), data: transactionsChartData.value, color: 'var(--c3)', type: 'line' as const },
]);
</script>

<template>
    <Head :title="$t('common.dashboard')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-330 px-5 py-6 md:px-8 md:py-8">
            <div class="mb-6 border-b border-line pb-6">
                <div class="mb-1.5 text-[13px] text-text-3">{{ $t('dashboard.crumb') }}</div>
                <h1 class="text-[28px] font-semibold tracking-[-0.03em] text-text md:text-3xl">
                    {{ $t('dashboard.title_lead') }} {{ $t('dashboard.title_em') }}
                </h1>
            </div>

            <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="stat in stats" :key="stat.label" class="rounded-card bg-surface px-4 py-[15px]">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold tracking-[0.06em] text-text-3 uppercase">
                        <component :is="stat.icon" class="size-3.5" />
                        {{ stat.label }}
                    </div>
                    <div class="num mt-[9px] text-[22px] font-semibold text-text">{{ stat.value }}</div>
                    <div class="mt-1 text-[12px] text-text-3">{{ stat.helper }}</div>
                </div>
            </div>

            <div class="mt-9 grid gap-4 lg:grid-cols-3">
                <section v-for="chart in charts" :key="chart.label" class="rounded-card bg-surface px-4 py-4">
                    <div class="mb-1 flex items-baseline justify-between">
                        <span class="text-[14px] font-medium text-text">{{ chart.label }}</span>
                        <span class="font-mono text-[10px] tracking-[0.04em] text-text-3 uppercase">{{ $t('dashboard.last_30_days') }}</span>
                    </div>
                    <VisXYContainer :data="chart.data" :height="208" :margin="{ left: 40, right: 8, top: 8, bottom: 24 }" :y-domain="[0, undefined]">
                        <VisGroupedBar v-if="chart.type === 'bar'" :x="xAccessor" :y="yAccessor" :color="chart.color" :rounded-corners="2" />
                        <VisLine v-else :x="xAccessor" :y="yAccessor" :color="chart.color" :curve-type="CurveType.Natural" />
                        <VisAxis
                            type="x"
                            :x="xAccessor"
                            :tick-line="false"
                            :domain-line="false"
                            :grid-line="false"
                            :num-ticks="5"
                            :tick-format="formatXTick"
                        />
                        <VisAxis type="y" :num-ticks="3" :tick-line="false" :domain-line="false" />
                    </VisXYContainer>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
