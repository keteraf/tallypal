<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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

const toChartData = (series: TimeSeriesPoint[]): ChartPoint[] =>
    series.map((d) => ({ date: new Date(d.date), count: d.count }));

const usersChartData = computed(() => toChartData(props.usersOverTime));
const talliesChartData = computed(() => toChartData(props.talliesOverTime));
const transactionsChartData = computed(() => toChartData(props.transactionsOverTime));

const xAccessor = (d: ChartPoint) => d.date;
const yAccessor = (d: ChartPoint) => d.count;

const formatXTick = (d: number | Date) => {
    const date = typeof d === 'number' ? new Date(d) : d;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head :title="$t('common.dashboard')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Stat tiles -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <div class="flex items-center gap-2">
                            <Users class="text-muted-foreground h-4 w-4" />
                            <CardDescription>{{ $t('dashboard.total_users') }}</CardDescription>
                        </div>
                        <CardTitle class="text-3xl font-bold">{{ stats.total_users.toLocaleString() }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground text-xs">+{{ stats.new_users_30d }} {{ $t('dashboard.this_month') }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <div class="flex items-center gap-2">
                            <BookOpen class="text-muted-foreground h-4 w-4" />
                            <CardDescription>{{ $t('dashboard.total_tallies') }}</CardDescription>
                        </div>
                        <CardTitle class="text-3xl font-bold">{{ stats.total_tallies.toLocaleString() }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground text-xs">{{ stats.shared_tallies }} {{ $t('dashboard.shared') }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <div class="flex items-center gap-2">
                            <ArrowLeftRight class="text-muted-foreground h-4 w-4" />
                            <CardDescription>{{ $t('dashboard.total_transactions') }}</CardDescription>
                        </div>
                        <CardTitle class="text-3xl font-bold">{{ stats.total_transactions.toLocaleString() }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground text-xs">{{ $t('dashboard.all_time') }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <div class="flex items-center gap-2">
                            <Mail class="text-muted-foreground h-4 w-4" />
                            <CardDescription>{{ $t('dashboard.pending_invitations') }}</CardDescription>
                        </div>
                        <CardTitle class="text-3xl font-bold">{{ stats.pending_invitations.toLocaleString() }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground text-xs">{{ $t('dashboard.awaiting_response') }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts -->
            <div class="grid gap-4 lg:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ $t('dashboard.users_over_time') }}</CardTitle>
                        <CardDescription>{{ $t('dashboard.last_30_days') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="w-full overflow-hidden">
                            <VisXYContainer
                                :data="usersChartData"
                                :height="208"
                                :margin="{ left: 40, right: 8, top: 8, bottom: 24 }"
                                :y-domain="[0, undefined]"
                            >
                                <VisGroupedBar :x="xAccessor" :y="yAccessor" color="var(--chart-1)" :rounded-corners="4" />
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
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ $t('dashboard.tallies_over_time') }}</CardTitle>
                        <CardDescription>{{ $t('dashboard.last_30_days') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="w-full overflow-hidden">
                            <VisXYContainer
                                :data="talliesChartData"
                                :height="208"
                                :margin="{ left: 40, right: 8, top: 8, bottom: 24 }"
                                :y-domain="[0, undefined]"
                            >
                                <VisGroupedBar :x="xAccessor" :y="yAccessor" color="var(--chart-2)" :rounded-corners="4" />
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
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ $t('dashboard.transactions_over_time') }}</CardTitle>
                        <CardDescription>{{ $t('dashboard.last_30_days') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="w-full overflow-hidden">
                            <VisXYContainer
                                :data="transactionsChartData"
                                :height="208"
                                :margin="{ left: 40, right: 8, top: 8, bottom: 24 }"
                                :y-domain="[0, undefined]"
                            >
                                <VisLine :x="xAccessor" :y="yAccessor" color="var(--chart-4)" :curve-type="CurveType.Natural" />
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
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
