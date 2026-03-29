<script setup lang="ts">
import TallyShareDialog from '@/components/tallies/TallyShareDialog.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage, WhenVisible } from '@inertiajs/vue3';
import { Info, Plus, Settings, Share2, TrendingDown, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    tally: {
        tally_id: number;
        uuid: string;
        name: string;
        description: string;
        owner_id: number;
    };
    transactions: {
        data: any[];
        next_page_url: string | null;
        current_page: number;
    };
    totals: Array<{
        type: string;
        currency_id: number;
        total: number;
        currency: {
            code: string;
            symbol: string;
            decimals: number;
        };
    }>;
    debts: Array<{
        currency: {
            code: string;
            symbol: string;
            decimals: number;
        };
        settlements: Array<{
            debtor: { id: number; name: string };
            creditor: { id: number; name: string };
            amount: number;
        }>;
    }>;
}

const props = defineProps<Props>();
const page = usePage();
const { t } = useI18n();

const showShareDialog = ref(false);
const isOwner = computed(() => page.props.auth.user.id === props.tally.owner_id);

const formatCurrency = (value: number, currency?: { symbol: string; decimals: number }) => {
    return (
        (currency?.symbol ?? '') +
        ' ' +
        new Intl.NumberFormat('en-US', {
            minimumFractionDigits: currency?.decimals ?? 2,
            maximumFractionDigits: currency?.decimals ?? 2,
        }).format(value)
    );
};

const incomeTotals = computed(() => props.totals.filter((t) => t.type === 'income'));
const expenseTotals = computed(() => props.totals.filter((t) => t.type === 'expense'));

const balances = computed(() => {
    const result: Array<{ balance: number; currency: any; currency_id: number }> = [];
    const currencies = [...new Set(props.totals.map((t) => t.currency_id))];

    for (const cid of currencies as number[]) {
        const inc = props.totals.find((t) => t.type === 'income' && t.currency_id === cid)?.total ?? 0;
        const exp = props.totals.find((t) => t.type === 'expense' && t.currency_id === cid)?.total ?? 0;
        const currency = props.totals.find((t) => t.currency_id === cid)?.currency;
        result.push({
            balance: inc - exp,
            currency,
            currency_id: cid,
        });
    }
    return result;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('tallies.title'),
        href: route('tallies.index'),
    },
    {
        title: props.tally.name,
        href: route('tallies.transactions.index', props.tally.uuid),
    },
];

const getDebtMessage = (
    settlement: { debtor: { id: number; name: string }; creditor: { id: number; name: string }; amount: number },
    currency: any,
) => {
    const currentUserId = page.props.auth.user.id;
    const amount = formatCurrency(settlement.amount, currency);

    if (settlement.debtor.id === currentUserId) {
        return t('transactions.debts.you_owe', { creditor: settlement.creditor.name, amount });
    }
    if (settlement.creditor.id === currentUserId) {
        return t('transactions.debts.owes_you', { debtor: settlement.debtor.name, amount });
    }
    return t('transactions.debts.owes_each_other', { debtor: settlement.debtor.name, creditor: settlement.creditor.name, amount });
};
</script>

<template>
    <Head :title="`${tally.name} - ${$t('transactions.title')}`" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-[768px]:pb-28">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div class="min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h1 class="text-2xl font-bold break-words">{{ tally.name }}</h1>
                        <div class="flex items-center gap-2 min-[769px]:hidden">
                            <Button v-if="isOwner" variant="outline" size="icon" @click="showShareDialog = true" :aria-label="$t('common.share')">
                                <Share2 class="h-4 w-4" />
                            </Button>
                            <Button variant="outline" as-child size="icon">
                                <Link :href="route('tallies.edit', tally.uuid)" :aria-label="$t('common.edit')">
                                    <Settings class="h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                    <p class="break-words text-gray-600 dark:text-gray-400">{{ tally.description }}</p>
                </div>
                <div class="hidden min-[769px]:flex min-[769px]:flex-wrap min-[769px]:items-center min-[769px]:gap-2">
                    <Button v-if="isOwner" variant="outline" @click="showShareDialog = true" class="w-full sm:w-auto">
                        <Share2 class="mr-2 h-4 w-4" />
                        {{ $t('common.share') }}
                    </Button>
                    <Button variant="outline" as-child class="w-full sm:w-auto">
                        <Link :href="route('tallies.edit', tally.uuid)">
                            <Settings class="mr-2 h-4 w-4" />
                            {{ $t('common.edit') }}
                        </Link>
                    </Button>
                    <Button as-child class="w-full sm:w-auto">
                        <Link :href="route('tallies.transactions.create', tally.uuid)">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ $t('transactions.add_button') }}
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium"> {{ $t('transactions.total_income') }} </CardTitle>
                        <TrendingUp class="h-4 w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div v-for="total in incomeTotals" :key="total.currency_id" class="text-2xl font-bold text-green-600">
                            + {{ formatCurrency(total.total, total.currency) }}
                        </div>
                        <div v-if="incomeTotals.length === 0" class="text-2xl font-bold text-green-600">0.00</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium"> {{ $t('transactions.total_expenses') }} </CardTitle>
                        <TrendingDown class="h-4 w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div v-for="total in expenseTotals" :key="total.currency_id" class="text-2xl font-bold text-red-600">
                            - {{ formatCurrency(total.total, total.currency) }}
                        </div>
                        <div v-if="expenseTotals.length === 0" class="text-2xl font-bold text-red-600">0.00</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium"> {{ $t('transactions.net_balance') }} </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-for="b in balances"
                            :key="b.currency_id"
                            :class="['text-2xl font-bold', b.balance >= 0 ? 'text-green-600' : 'text-red-600']"
                        >
                            {{ b.balance >= 0 ? '+' : '' }} {{ formatCurrency(b.balance, b.currency) }}
                        </div>
                        <div v-if="balances.length === 0" class="text-2xl font-bold text-green-600">0.00</div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="debts && debts.length > 0">
                <Card class="border-blue-100 bg-blue-50/50 dark:border-blue-900/50 dark:bg-blue-900/10">
                    <CardHeader class="flex flex-row items-center gap-2 pb-2">
                        <Info class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        <CardTitle class="text-sm font-medium text-blue-900 dark:text-blue-100"> {{ $t('transactions.who_owes_what') }} </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div v-for="d in debts" :key="d.currency.code" class="space-y-1">
                            <div v-for="(s, index) in d.settlements" :key="index" class="text-sm">
                                <span class="font-medium text-blue-900 dark:text-blue-100">{{ getDebtMessage(s, d.currency) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="mt-4">
                <h2 class="mb-4 text-lg font-semibold">{{ $t('transactions.title') }}</h2>

                <div v-if="transactions.data.length === 0" class="rounded-lg border py-12 text-center">
                    <p class="text-gray-500">{{ $t('transactions.empty_state') }}</p>
                </div>

                <div v-else class="space-y-4">
                    <Link
                        v-for="transaction in transactions.data"
                        :key="transaction.transaction_id"
                        :href="route('tallies.transactions.edit', { tally: tally.uuid, transaction: transaction.uuid })"
                        class="flex items-center justify-between rounded-lg border p-4 shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                    >
                        <div>
                            <p class="font-medium">{{ transaction.title }}</p>
                            <p class="text-sm text-gray-500">{{ transaction.transaction_date }} • {{ transaction.type }}</p>
                            <p v-if="transaction.description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ transaction.description }}</p>
                        </div>
                        <div class="text-right">
                            <p :class="['font-bold', transaction.type === 'expense' ? 'text-red-600' : 'text-green-600']">
                                {{ transaction.type === 'expense' ? '-' : '+' }}
                                {{ formatCurrency(transaction.amount, transaction.currency) }}
                            </p>
                            <p class="text-xs text-gray-400">{{ transaction.category?.name }}</p>
                        </div>
                    </Link>

                    <WhenVisible
                        v-if="transactions.next_page_url"
                        data="transactions"
                        :params="{
                            data: {
                                page: transactions.current_page + 1,
                            },
                            only: ['transactions'],
                            preserveUrl: true,
                        }"
                        always
                        :buffer="500"
                    >
                        <template #fallback>
                            <div class="flex justify-center py-4">
                                <div class="flex animate-pulse space-x-4">
                                    <div class="flex-1 space-y-4 py-1">
                                        <div class="h-4 w-3/4 rounded bg-gray-200"></div>
                                        <div class="space-y-2">
                                            <div class="h-4 rounded bg-gray-200"></div>
                                            <div class="h-4 w-5/6 rounded bg-gray-200"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </WhenVisible>
                </div>
            </div>
        </div>

        <div
            class="fixed inset-x-0 bottom-0 z-40 border-t bg-background/95 p-3 backdrop-blur min-[769px]:hidden"
            style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)"
        >
            <div class="flex justify-center">
                <Button as-child class="w-full">
                    <Link :href="route('tallies.transactions.create', tally.uuid)">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ $t('transactions.add_button') }}
                    </Link>
                </Button>
            </div>
        </div>

        <TallyShareDialog v-model:open="showShareDialog" :tally="tally" />
    </WebLayout>
</template>
