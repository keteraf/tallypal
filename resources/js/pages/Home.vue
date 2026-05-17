<script setup lang="ts">
import AppHead from '@/components/seo/AppHead.vue';
import HomeBalanceGrid from '@/pages/home/sections/HomeBalanceGrid.vue';
import HomeCategoriesBreakdown from '@/pages/home/sections/HomeCategoriesBreakdown.vue';
import HomeEmptyState from '@/pages/home/sections/HomeEmptyState.vue';
import HomeHeader from '@/pages/home/sections/HomeHeader.vue';
import HomeMobileFab from '@/pages/home/sections/HomeMobileFab.vue';
import HomeRecentTransactions from '@/pages/home/sections/HomeRecentTransactions.vue';
import HomeSharedTally from '@/pages/home/sections/HomeSharedTally.vue';
import HomeWeeklyHistory from '@/pages/home/sections/HomeWeeklyHistory.vue';
import type {
    BalanceShape,
    CategoryBreakdown,
    PrimaryTally,
    RecentTransaction,
    SharedTally,
    TallySummary,
    WeekHistory,
} from '@/pages/home/types';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    user_name: string;
    current_month_label: string;
    primary_tally: PrimaryTally | null;
    tallies: TallySummary[];
    balance: BalanceShape;
    recent_transactions: RecentTransaction[];
    weekly_history: WeekHistory[];
    categories_breakdown: CategoryBreakdown[];
    shared_tally: SharedTally | null;
}>();

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('navigation.home'),
        href: '/home',
    },
];

const addTransactionUrl = computed(() => {
    if (!props.primary_tally) return '/tallies';
    return route('tallies.transactions.create', props.primary_tally.uuid);
});
</script>

<template>
    <AppHead :title="$t('home.title')" :noindex="true" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-330 px-6 py-10 max-[768px]:pb-28 md:px-8">
            <HomeHeader
                :user-name="user_name"
                :current-month-label="current_month_label"
                :primary-tally="primary_tally"
                :add-transaction-url="addTransactionUrl"
            />

            <HomeEmptyState v-if="tallies.length === 0" />

            <template v-else>
                <HomeBalanceGrid :balance="balance" :current-month-label="current_month_label" />

                <div class="grid grid-cols-1 gap-9 lg:grid-cols-[1.4fr_1fr]">
                    <div class="flex flex-col gap-9">
                        <HomeRecentTransactions :transactions="recent_transactions" :primary-tally="primary_tally" :decimals="balance.decimals" />
                        <HomeWeeklyHistory :weeks="weekly_history" :currency-symbol="balance.currency_symbol" :decimals="balance.decimals" />
                    </div>

                    <div class="flex flex-col gap-9">
                        <HomeSharedTally
                            v-if="shared_tally"
                            :shared-tally="shared_tally"
                            :currency-symbol="balance.currency_symbol"
                            :decimals="balance.decimals"
                        />
                        <HomeCategoriesBreakdown
                            :categories="categories_breakdown"
                            :current-month-label="current_month_label"
                            :currency-symbol="balance.currency_symbol"
                            :decimals="balance.decimals"
                        />
                    </div>
                </div>
            </template>
        </div>

        <HomeMobileFab :add-transaction-url="addTransactionUrl" :disabled="!primary_tally" />
    </WebLayout>
</template>
