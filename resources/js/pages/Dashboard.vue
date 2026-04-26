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
</script>

<template>
    <Head :title="$t('common.dashboard')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-9 p-6 md:p-8">
            <div class="border-b border-rule pb-6">
                <div class="eyebrow mb-3">{{ $t('dashboard.crumb') }}</div>
                <h1 class="m-0 font-serif text-[clamp(36px,5vw,56px)] leading-none tracking-[-0.02em]">
                    {{ $t('dashboard.title_lead') }}
                    <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ $t('dashboard.title_em') }}</em>
                </h1>
            </div>

            <div class="grid grid-cols-1 overflow-hidden rounded-sm border border-rule sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(stat, idx) in stats"
                    :key="stat.label"
                    class="border-b border-rule-2 px-7 py-6"
                    :class="{
                        'lg:border-r': (idx + 1) % 4 !== 0,
                        'sm:border-r sm:max-lg:border-r-0': (idx + 1) % 2 !== 0,
                        'lg:border-b-0': idx >= stats.length - 4,
                        'sm:max-lg:border-b-0': idx >= stats.length - 2,
                    }"
                >
                    <div class="mb-3.5 flex items-center gap-2">
                        <component :is="stat.icon" class="size-4 text-muted-foreground" />
                        <span class="eyebrow">{{ stat.label }}</span>
                    </div>
                    <div class="font-serif text-[40px] leading-none tracking-[-0.02em]">{{ stat.value }}</div>
                    <div class="mt-3 font-mono text-[12px] text-muted-foreground">{{ stat.helper }}</div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <section class="overflow-hidden rounded-sm border border-rule">
                    <div class="flex items-baseline justify-between border-b border-rule-2 px-6 py-4">
                        <span class="font-serif text-lg text-foreground italic">{{ $t('dashboard.users_over_time') }}</span>
                        <span class="font-mono text-[10px] tracking-[0.08em] text-muted-foreground uppercase">{{
                            $t('dashboard.last_30_days')
                        }}</span>
                    </div>
                    <div class="px-4 py-4">
                        <VisXYContainer
                            :data="usersChartData"
                            :height="208"
                            :margin="{ left: 40, right: 8, top: 8, bottom: 24 }"
                            :y-domain="[0, undefined]"
                        >
                            <VisGroupedBar :x="xAccessor" :y="yAccessor" color="var(--brand)" :rounded-corners="2" />
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
                    </div>
                </section>

                <section class="overflow-hidden rounded-sm border border-rule">
                    <div class="flex items-baseline justify-between border-b border-rule-2 px-6 py-4">
                        <span class="font-serif text-lg text-foreground italic">{{ $t('dashboard.tallies_over_time') }}</span>
                        <span class="font-mono text-[10px] tracking-[0.08em] text-muted-foreground uppercase">{{
                            $t('dashboard.last_30_days')
                        }}</span>
                    </div>
                    <div class="px-4 py-4">
                        <VisXYContainer
                            :data="talliesChartData"
                            :height="208"
                            :margin="{ left: 40, right: 8, top: 8, bottom: 24 }"
                            :y-domain="[0, undefined]"
                        >
                            <VisGroupedBar :x="xAccessor" :y="yAccessor" color="var(--ink-2)" :rounded-corners="2" />
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
                    </div>
                </section>

                <section class="overflow-hidden rounded-sm border border-rule">
                    <div class="flex items-baseline justify-between border-b border-rule-2 px-6 py-4">
                        <span class="font-serif text-lg text-foreground italic">{{ $t('dashboard.transactions_over_time') }}</span>
                        <span class="font-mono text-[10px] tracking-[0.08em] text-muted-foreground uppercase">{{
                            $t('dashboard.last_30_days')
                        }}</span>
                    </div>
                    <div class="px-4 py-4">
                        <VisXYContainer
                            :data="transactionsChartData"
                            :height="208"
                            :margin="{ left: 40, right: 8, top: 8, bottom: 24 }"
                            :y-domain="[0, undefined]"
                        >
                            <VisLine :x="xAccessor" :y="yAccessor" color="var(--brand)" :curve-type="CurveType.Natural" />
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
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
