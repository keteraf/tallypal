<script setup lang="ts">
import TallyShareDialog from '@/components/tallies/TallyShareDialog.vue';
import { Button } from '@/components/ui/button';
import WebLayout from '@/layouts/WebLayout.vue';
import tallies from '@/routes/tallies';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage, WhenVisible } from '@inertiajs/vue3';
import { Plus, Settings, Share2 } from 'lucide-vue-next';
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
        currency: { code: string; symbol: string; decimals: number };
    }>;
    debts: Array<{
        currency: { code: string; symbol: string; decimals: number };
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
        result.push({ balance: inc - exp, currency, currency_id: cid });
    }
    return result;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('tallies.title'),
        href: tallies.index.url(),
    },
    {
        title: props.tally.name,
        href: tallies.transactions.index.url(props.tally.uuid),
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

const categoryCode = (name?: string | null) => {
    if (!name) return '··';
    const cleaned = name.replace(/[^A-Za-z]/g, '');
    return (cleaned.slice(0, 2) || '··').toUpperCase();
};

const splitName = (name: string) => {
    if (!name) return { lead: '', em: '' };
    const parts = name.split(/\s+/);
    if (parts.length === 1) return { lead: '', em: parts[0] };
    return { lead: parts.slice(0, -1).join(' '), em: parts[parts.length - 1] };
};
</script>

<template>
    <Head :title="`${tally.name} - ${$t('transactions.title')}`" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-330 px-6 py-10 max-[768px]:pb-28 md:px-8">
            <div class="mb-9 flex flex-col gap-4 border-b border-rule pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <div class="eyebrow mb-3">{{ $t('tallies.tally_label') }}</div>
                    <h1 class="m-0 font-serif text-[clamp(36px,5vw,56px)] leading-none tracking-[-0.02em] wrap-break-word">
                        <template v-if="splitName(tally.name).lead">{{ splitName(tally.name).lead }} </template>
                        <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ splitName(tally.name).em }}</em>
                    </h1>
                    <p v-if="tally.description" class="mt-3 max-w-[52ch] text-[15px] wrap-break-word text-muted-foreground">{{ tally.description }}</p>
                </div>
                <div class="hidden flex-wrap items-center gap-2 min-[769px]:flex">
                    <Button v-if="isOwner" variant="ghost" @click="showShareDialog = true">
                        <Share2 class="size-4" />
                        {{ $t('common.share') }}
                    </Button>
                    <Button v-if="isOwner" variant="ghost" as-child>
                        <Link :href="tallies.edit.url(tally.uuid)">
                            <Settings class="size-4" />
                            {{ $t('common.edit') }}
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link :href="tallies.transactions.create.url(tally.uuid)">
                            <Plus class="size-4" />
                            {{ $t('transactions.add_button') }}
                        </Link>
                    </Button>
                </div>
                <div class="flex items-center gap-2 min-[769px]:hidden">
                    <Button v-if="isOwner" variant="ghost" size="icon" @click="showShareDialog = true" :aria-label="$t('common.share')">
                        <Share2 class="size-4" />
                    </Button>
                    <Button variant="ghost" as-child size="icon">
                        <Link :href="tallies.edit.url(tally.uuid)" :aria-label="$t('common.edit')">
                            <Settings class="size-4" />
                        </Link>
                    </Button>
                </div>
            </div>

            <div v-if="totals.length > 0" class="mb-9 grid grid-cols-1 overflow-hidden rounded-sm border border-rule md:grid-cols-3">
                <div class="border-b border-rule-2 px-7 py-6 md:border-r md:border-b-0">
                    <div class="eyebrow mb-3">{{ $t('transactions.total_income') }}</div>
                    <div v-if="incomeTotals.length === 0" class="font-serif text-[36px] leading-none tracking-[-0.02em] text-pos">+ 0.00</div>
                    <div
                        v-for="total in incomeTotals"
                        :key="`in-${total.currency_id}`"
                        class="font-serif text-[36px] leading-none tracking-[-0.02em] text-pos"
                    >
                        + {{ formatCurrency(total.total, total.currency) }}
                    </div>
                </div>
                <div class="border-b border-rule-2 px-7 py-6 md:border-r md:border-b-0">
                    <div class="eyebrow mb-3">{{ $t('transactions.total_expenses') }}</div>
                    <div v-if="expenseTotals.length === 0" class="font-serif text-[36px] leading-none tracking-[-0.02em] text-neg">− 0.00</div>
                    <div
                        v-for="total in expenseTotals"
                        :key="`out-${total.currency_id}`"
                        class="font-serif text-[36px] leading-none tracking-[-0.02em] text-neg"
                    >
                        − {{ formatCurrency(total.total, total.currency) }}
                    </div>
                </div>
                <div class="px-7 py-6">
                    <div class="eyebrow mb-3">{{ $t('transactions.net_balance') }}</div>
                    <div v-if="balances.length === 0" class="font-serif text-[36px] leading-none tracking-[-0.02em] text-pos">+ 0.00</div>
                    <div
                        v-for="b in balances"
                        :key="b.currency_id"
                        class="font-serif text-[36px] leading-none tracking-[-0.02em]"
                        :class="b.balance >= 0 ? 'text-pos' : 'text-neg'"
                    >
                        {{ b.balance >= 0 ? '+' : '−' }} {{ formatCurrency(Math.abs(b.balance), b.currency) }}
                    </div>
                </div>
            </div>

            <div v-if="debts && debts.length > 0" class="mb-9 rounded-sm border border-rule bg-paper-2 px-7 py-5">
                <div class="eyebrow mb-3">{{ $t('transactions.who_owes_what') }}</div>
                <div v-for="d in debts" :key="d.currency.code" class="space-y-1.5">
                    <div v-for="(s, index) in d.settlements" :key="index" class="text-sm text-foreground">
                        {{ getDebtMessage(s, d.currency) }}
                    </div>
                </div>
            </div>

            <section class="overflow-hidden rounded-sm border border-rule">
                <div class="flex items-baseline justify-between border-b border-rule-2 px-7 py-5">
                    <span class="font-serif text-xl text-foreground italic">{{ $t('transactions.title') }}</span>
                    <span class="font-mono text-[11px] tracking-[0.06em] text-muted-foreground uppercase"
                        >{{ transactions.data.length }} {{ $t('transactions.items') }}</span
                    >
                </div>
                <div v-if="transactions.data.length === 0" class="px-7 py-12 text-center text-muted-foreground">
                    {{ $t('transactions.empty_state') }}
                </div>
                <div v-else>
                    <Link
                        v-for="transaction in transactions.data"
                        :key="transaction.transaction_id"
                        :href="tallies.transactions.edit.url({ tally: tally.uuid, transaction: transaction.uuid })"
                        class="grid grid-cols-[36px_1fr_auto] items-center gap-4 border-b border-rule-2 px-7 py-4 transition-colors last:border-b-0 hover:bg-paper-2"
                    >
                        <span
                            class="inline-flex size-8 items-center justify-center rounded-full border border-rule bg-paper-2 font-mono text-[11px] tracking-[0.04em] text-muted-foreground"
                        >
                            {{ categoryCode(transaction.category?.name) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate text-foreground">{{ transaction.title }}</span>
                            <span class="mt-1 block font-mono text-[11px] tracking-[0.04em] text-muted-foreground">
                                {{ transaction.transaction_date }} · {{ transaction.category?.name ?? transaction.type }}
                            </span>
                        </span>
                        <span
                            class="tabular font-mono text-[15px] font-medium"
                            :class="transaction.type === 'income' ? 'text-pos' : 'text-foreground'"
                        >
                            {{ transaction.type === 'income' ? '+' : '−' }}
                            {{ formatCurrency(transaction.amount, transaction.currency) }}
                        </span>
                    </Link>

                    <WhenVisible
                        v-if="transactions.next_page_url"
                        data="transactions"
                        :params="{
                            data: { page: transactions.current_page + 1 },
                            only: ['transactions'],
                            preserveUrl: true,
                        }"
                        always
                        :buffer="500"
                    >
                        <template #fallback>
                            <div class="animate-pulse border-t border-rule-2 px-7 py-5">
                                <div class="grid grid-cols-[36px_1fr_auto] items-center gap-4">
                                    <div class="size-8 rounded-full border border-rule bg-paper-2" />
                                    <div class="space-y-2">
                                        <div class="h-3 w-3/5 rounded-sm bg-paper-2" />
                                        <div class="h-2 w-2/5 rounded-sm bg-paper-2" />
                                    </div>
                                    <div class="h-3 w-16 rounded-sm bg-paper-2" />
                                </div>
                            </div>
                        </template>
                    </WhenVisible>
                </div>
            </section>
        </div>

        <div
            class="fixed inset-x-0 bottom-0 z-40 border-t border-rule bg-background/95 p-3 backdrop-blur min-[769px]:hidden"
            style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)"
        >
            <Button as-child class="w-full">
                <Link :href="tallies.transactions.create.url(tally.uuid)">
                    <Plus class="size-4" />
                    {{ $t('transactions.add_button') }}
                </Link>
            </Button>
        </div>

        <TallyShareDialog v-model:open="showShareDialog" :tally="tally" />
    </WebLayout>
</template>
