<script setup lang="ts">
import AppHead from '@/components/seo/AppHead.vue';
import WebLayout from '@/layouts/WebLayout.vue';
import HomeBalanceGrid from '@/pages/home/sections/HomeBalanceGrid.vue';
import HomeCategoriesBreakdown from '@/pages/home/sections/HomeCategoriesBreakdown.vue';
import HomeEmptyState from '@/pages/home/sections/HomeEmptyState.vue';
import HomeHeader from '@/pages/home/sections/HomeHeader.vue';
import HomeQuickActions from '@/pages/home/sections/HomeQuickActions.vue';
import HomeRecentTransactions from '@/pages/home/sections/HomeRecentTransactions.vue';
import HomeYourTallies from '@/pages/home/sections/HomeYourTallies.vue';
import type {
    BalanceShape,
    CategoryBreakdown,
    CurrencyTotal,
    PrimaryTally,
    RecentTransaction,
    SettleSuggestion,
    TallySummary,
    WeekHistory,
} from '@/pages/home/types';
import tallyRoutes from '@/routes/tallies';
import { computed } from 'vue';

const props = defineProps<{
    user_name: string;
    current_month_label: string;
    /** `YYYY-MM-01` of the current month, for locale-formatted captions. */
    current_month_start: string;
    primary_tally: PrimaryTally | null;
    tallies: TallySummary[];
    balance: BalanceShape;
    recent_transactions: RecentTransaction[];
    weekly_history: WeekHistory[];
    categories_breakdown: CategoryBreakdown[];
    categories_other_currencies: CurrencyTotal[];
    settle_suggestion: SettleSuggestion | null;
}>();

const addTransactionUrl = computed(() => {
    if (!props.primary_tally) return '/tallies';
    return tallyRoutes.transactions.create.url(props.primary_tally.uuid);
});
</script>

<template>
    <AppHead :title="$t('home.title')" :noindex="true" />

    <WebLayout>
        <div class="mx-auto w-full max-w-330 px-5 py-6 md:px-8 md:py-8">
            <HomeHeader
                :user-name="user_name"
                :current-month-label="current_month_label"
                :primary-tally="primary_tally"
                :add-transaction-url="addTransactionUrl"
            />

            <HomeEmptyState v-if="tallies.length === 0" />

            <template v-else>
                <div class="grid grid-cols-1 gap-3 min-[1101px]:grid-cols-12 sm:gap-4">
                    <div class="min-[1101px]:col-span-8 [&>*]:h-full">
                        <HomeBalanceGrid :balance="balance" :weekly-history="weekly_history" />
                    </div>
                    <div class="min-[1101px]:col-span-4 [&>*]:h-full">
                        <HomeQuickActions :add-transaction-url="addTransactionUrl" :settle-suggestion="settle_suggestion" />
                    </div>
                </div>
                <HomeYourTallies :tallies="tallies" :month-start="current_month_start" />

                <div class="mt-3 grid grid-cols-1 gap-3 min-[1101px]:grid-cols-12 sm:mt-4 sm:gap-4">
                    <div class="min-w-0 min-[1101px]:col-span-8 [&>*]:h-full">
                        <HomeRecentTransactions :transactions="recent_transactions" :primary-tally="primary_tally" :decimals="balance.decimals" />
                    </div>

                    <div class="min-w-0 min-[1101px]:col-span-4 [&>*]:h-full">
                        <HomeCategoriesBreakdown
                            :categories="categories_breakdown"
                            :current-month-label="current_month_label"
                            :currency-symbol="balance.currency_symbol"
                            :decimals="balance.decimals"
                            :other-currencies="categories_other_currencies"
                        />
                    </div>
                </div>
            </template>
        </div>
    </WebLayout>
</template>
