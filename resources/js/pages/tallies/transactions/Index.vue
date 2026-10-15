<script setup lang="ts">
import TallyShareDialog from '@/components/tallies/TallyShareDialog.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { BalanceHero } from '@/components/ui/balance-hero';
import { Button } from '@/components/ui/button';
import { CardHeader } from '@/components/ui/card-header';
import { CategoryGlyph, categoryGlyph } from '@/components/ui/category-glyph';
import type { DataTableColumn } from '@/components/ui/data-table';
import { DataTable, DataTableItem } from '@/components/ui/data-table';
import { DetailHeader } from '@/components/ui/detail-header';
import { EmptyState } from '@/components/ui/empty-state';
import { InOutCards } from '@/components/ui/in-out-cards';
import { SettleBanner } from '@/components/ui/settle-banner';
import { Skeleton } from '@/components/ui/skeleton';
import { SplitMemberRow } from '@/components/ui/split-member-row';
import { useCategoryLabel } from '@/composables/useCategoryLabel';
import WebLayout from '@/layouts/WebLayout.vue';
import { categoryColorFromString, transactionGlyphColor, type CategoryColor } from '@/lib/category';
import tallies from '@/routes/tallies';
import { Head, InfiniteScroll, Link, usePage } from '@inertiajs/vue3';
import { ChevronLeft, Plus, Settings, Share2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface TransactionItem {
    transaction_id: number;
    uuid: string;
    title: string;
    amount: number;
    type: string;
    transaction_date: string;
    category: { category_id: number; slug: string; name: string; color: CategoryColor | null } | null;
    currency: { symbol: string; decimals: number } | null;
    party: { name: string; initials: string; color: CategoryColor } | null;
    [key: string]: unknown;
}

interface Props {
    /** One-shot flash from the last write, e.g. `transaction-deleted`. */
    status?: string | null;
    tally: {
        tally_id: number;
        uuid: string;
        name: string;
        description: string;
        owner_id: number;
        color: CategoryColor | null;
        glyph: string | null;
        users_count: number;
    };
    transactions: {
        data: TransactionItem[];
        next_page_url: string | null;
        current_page: number;
    };
    totals: Array<{
        type: string;
        currency_id: number;
        total: number;
        currency: { code: string; symbol: string; decimals: number };
    }>;
    per_person: Array<{ amount: number; currency_symbol: string; currency_code: string; decimals: number }>;
    debts: Array<{
        currency: { code: string; symbol: string; decimals: number };
        settlements: Array<{
            debtor: { id: number; name: string };
            creditor: { id: number; name: string };
            amount: number;
        }>;
    }>;
    /** Per currency, the current members whose balance is exactly zero (B-04). */
    settled_members: Array<{
        currency: { code: string; symbol: string; decimals: number };
        members: Array<{ id: number; name: string }>;
    }>;
    splits: Array<{
        currency: { code: string; symbol: string; decimals: number };
        members: Array<{ name: string; initials: string; amount: number; percentage: number }>;
    }>;
}

const props = defineProps<Props>();
const page = usePage();
const { t } = useI18n();
const categoryLabel = useCategoryLabel();

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

const currencyTotals = computed(() => {
    const currencyIds = [...new Set(props.totals.map((total) => total.currency_id))];

    return currencyIds.map((currencyId) => {
        const income = props.totals.find((total) => total.type === 'income' && total.currency_id === currencyId)?.total ?? 0;
        const expense = props.totals.find((total) => total.type === 'expense' && total.currency_id === currencyId)?.total ?? 0;
        const currency = props.totals.find((total) => total.currency_id === currencyId)?.currency;

        return { currency_id: currencyId, currency, income, expense, net: income - expense };
    });
});

const debtKeypath = (settlement: { debtor: { id: number }; creditor: { id: number } }) => {
    const currentUserId = page.props.auth.user.id;

    if (settlement.debtor.id === currentUserId) {
        return 'transactions.debts.you_owe';
    }
    if (settlement.creditor.id === currentUserId) {
        return 'transactions.debts.owes_you';
    }

    return 'transactions.debts.owes_each_other';
};

/** Second banner line (F-061): who is already settled in that currency, "Sara and Marco are settled". */
const settledLine = (currencyCode: string): string | null => {
    const names = props.settled_members.find((entry) => entry.currency.code === currencyCode)?.members.map((member) => member.name) ?? [];

    if (names.length === 0) {
        return null;
    }
    if (names.length === 1) {
        return t('transactions.debts.settled_one', { name: names[0] });
    }
    if (names.length === 2) {
        return t('transactions.debts.settled_two', { first: names[0], second: names[1] });
    }

    return t('transactions.debts.settled_many', { names: names.slice(0, -1).join(', '), last: names[names.length - 1] });
};

const columns = computed<DataTableColumn[]>(() => [
    { key: 'title', label: t('transactions.columns.item') },
    { key: 'party', label: t('transactions.columns.paid_by') },
    { key: 'transaction_date', label: t('transactions.columns.date') },
    { key: 'amount', label: t('transactions.columns.amount'), align: 'right' },
]);

const isShared = computed(() => props.tally.users_count > 1);

const tallyGlyphColor = computed<CategoryColor>(() => props.tally.color ?? categoryColorFromString(props.tally.name));
const tallyGlyphText = computed(() => props.tally.glyph || props.tally.name.charAt(0).toUpperCase());

const perPersonFor = (currencyCode?: string) => props.per_person.find((entry) => entry.currency_code === currencyCode);

const memberLabel = computed(() =>
    props.tally.users_count > 1 ? t('tallies.members_count', { count: props.tally.users_count }) : t('tallies.members_just_you'),
);

const transactionHref = (transaction: TransactionItem): string =>
    tallies.transactions.edit.url({ tally: props.tally.uuid, transaction: transaction.uuid });

const glyphColor = (transaction: TransactionItem) =>
    transactionGlyphColor(transaction.type, { category_id: transaction.category?.category_id ?? null, color: transaction.category?.color ?? null });

const signedAmount = (transaction: TransactionItem): string =>
    `${transaction.type === 'income' ? '+' : '−'}${formatCurrency(transaction.amount, transaction.currency ?? undefined)}`;
</script>

<template>
    <Head :title="`${tally.name} - ${$t('transactions.title')}`" />

    <WebLayout>
        <div class="mx-auto w-full max-w-330 px-5 py-6 pb-28 md:px-8 md:py-8">
            <div v-if="status === 'transaction-deleted'" role="status" class="mb-4 text-sm font-medium text-pos">
                {{ $t('transactions.deleted') }}
            </div>
            <div class="md:hidden">
                <DetailHeader :title="tally.name" :subtitle="memberLabel" :back-href="tallies.index.url()">
                    <template #glyph>
                        <CategoryGlyph :color="tallyGlyphColor" size="header" :glyph="tallyGlyphText" />
                    </template>
                    <template #actions>
                        <Button as-child size="icon-lg" class="rounded-full">
                            <Link :href="tallies.transactions.create.url(tally.uuid)" :aria-label="$t('transactions.add_button')">
                                <Plus class="size-4" />
                            </Link>
                        </Button>
                        <Button v-if="isOwner" variant="outline" size="icon" :aria-label="$t('common.share')" @click="showShareDialog = true">
                            <Share2 class="size-4" />
                        </Button>
                        <Button v-if="isOwner" variant="outline" size="icon" as-child>
                            <Link :href="tallies.edit.url(tally.uuid)" :aria-label="$t('common.edit')">
                                <Settings class="size-4" />
                            </Link>
                        </Button>
                    </template>
                </DetailHeader>
            </div>

            <div class="mb-[26px] hidden items-center gap-3.5 md:flex">
                <Link
                    :href="tallies.index.url()"
                    :aria-label="$t('common.back')"
                    class="grid size-10 shrink-0 place-items-center rounded-full border border-line bg-surface text-text-2 transition hover:bg-surface-2 hover:text-text"
                >
                    <ChevronLeft class="size-[17px]" :stroke-width="2.2" />
                </Link>
                <CategoryGlyph :color="tallyGlyphColor" size="xl" :glyph="tallyGlyphText" />
                <div class="min-w-0">
                    <h1 class="truncate text-[26px] font-semibold tracking-[-0.03em] text-text">{{ tally.name }}</h1>
                    <p class="mt-[3px] truncate text-[13px] text-text-3">{{ memberLabel }}</p>
                </div>
                <div class="ml-auto flex shrink-0 items-center gap-2.5">
                    <Button v-if="isOwner" variant="outline" size="lg" @click="showShareDialog = true">
                        <Share2 class="size-4" />
                        {{ $t('common.share') }}
                    </Button>
                    <Button v-if="isOwner" variant="outline" size="icon-lg" as-child>
                        <Link :href="tallies.edit.url(tally.uuid)" :aria-label="$t('common.edit')">
                            <Settings class="size-4" />
                        </Link>
                    </Button>
                    <Button as-child size="lg">
                        <Link :href="tallies.transactions.create.url(tally.uuid)">
                            <Plus class="size-4" />
                            {{ $t('transactions.add_expense') }}
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-3 min-[1101px]:grid-cols-12 md:gap-4">
                <div class="flex flex-col gap-3 min-[1101px]:col-span-5 md:gap-4">
                    <div v-if="currencyTotals.length > 0" class="rounded-card bg-surface px-5 py-[22px] sm:px-7 sm:py-[26px]">
                        <div
                            v-for="ct in currencyTotals"
                            :key="ct.currency_id"
                            class="mt-6 border-t border-line pt-6 first:mt-0 first:border-t-0 first:pt-0"
                        >
                            <template v-if="isShared">
                                <BalanceHero
                                    size="card"
                                    :label="$t('transactions.total_spent')"
                                    :amount="ct.expense"
                                    :currency="ct.currency?.symbol"
                                    :decimals="ct.currency?.decimals"
                                />
                                <div v-if="perPersonFor(ct.currency?.code)" class="mt-[22px]">
                                    <div class="text-[11px] font-semibold tracking-[0.06em] text-text-3 uppercase">
                                        {{ $t('transactions.per_person') }}
                                    </div>
                                    <div class="num mt-2 text-[20px] font-semibold sm:text-[26px]">
                                        {{ perPersonFor(ct.currency?.code)?.currency_symbol }}
                                        {{
                                            formatCurrency(perPersonFor(ct.currency?.code)?.amount ?? 0, {
                                                symbol: '',
                                                decimals: perPersonFor(ct.currency?.code)?.decimals ?? 2,
                                            }).trim()
                                        }}
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <BalanceHero
                                    size="card"
                                    :label="$t('transactions.net_balance')"
                                    :amount="ct.net"
                                    :currency="ct.currency?.symbol"
                                    :decimals="ct.currency?.decimals"
                                />
                                <InOutCards
                                    inline
                                    class="mt-[22px]"
                                    :income="ct.income"
                                    :expense="ct.expense"
                                    :currency="ct.currency?.symbol"
                                    :decimals="ct.currency?.decimals"
                                    :in-label="$t('transactions.total_income')"
                                    :out-label="$t('transactions.total_expenses')"
                                />
                            </template>
                        </div>
                    </div>

                    <template v-if="debts && debts.length > 0">
                        <template v-for="d in debts" :key="d.currency.code">
                            <SettleBanner v-for="(s, index) in d.settlements" :key="`${d.currency.code}-${index}`" class="mt-0">
                                <template #message>
                                    <i18n-t :keypath="debtKeypath(s)" tag="span">
                                        <template #debtor
                                            ><b class="font-semibold">{{ s.debtor.name }}</b></template
                                        >
                                        <template #creditor
                                            ><b class="font-semibold">{{ s.creditor.name }}</b></template
                                        >
                                        <template #amount
                                            ><span class="num font-semibold text-primary">{{ formatCurrency(s.amount, d.currency) }}</span></template
                                        >
                                    </i18n-t>
                                </template>
                                <template v-if="index === 0 && settledLine(d.currency.code)" #detail>{{ settledLine(d.currency.code) }}</template>
                            </SettleBanner>
                        </template>
                    </template>

                    <div v-if="splits && splits.length > 0" class="rounded-card bg-surface p-4 sm:p-[22px]">
                        <CardHeader :title="$t('transactions.split_so_far')">
                            <template v-if="isOwner" #action>
                                <button
                                    type="button"
                                    class="text-[12.5px] font-medium text-accent transition hover:text-text"
                                    @click="showShareDialog = true"
                                >
                                    {{ $t('transactions.edit_split') }}
                                </button>
                            </template>
                        </CardHeader>
                        <div v-for="split in splits" :key="split.currency.code" class="[&:not(:first-of-type)]:mt-3">
                            <p v-if="splits.length > 1" class="m-0 font-mono text-[11px] tracking-[0.06em] text-text-3 uppercase">
                                {{ split.currency.code }}
                            </p>
                            <SplitMemberRow
                                v-for="(member, idx) in split.members"
                                :key="member.name + idx"
                                :name="member.name"
                                :initials="member.initials"
                                :amount="member.amount"
                                :percentage="member.percentage"
                                :currency="split.currency.symbol"
                                :decimals="split.currency.decimals"
                                :color="categoryColorFromString(member.name)"
                                class="border-t border-line first:border-t-0"
                            />
                        </div>
                    </div>
                </div>

                <section class="min-[1101px]:col-span-7">
                    <div class="rounded-card bg-surface p-4 sm:p-[22px]">
                        <CardHeader :title="$t('transactions.title')" />

                        <EmptyState
                            v-if="transactions.data.length === 0"
                            class="bg-transparent"
                            :title="$t('transactions.empty_title')"
                            :description="$t('transactions.empty_description')"
                            :action-label="$t('transactions.add_expense')"
                            :action-href="tallies.transactions.create.url(tally.uuid)"
                        />

                        <InfiniteScroll v-else data="transactions" :buffer="500">
                            <DataTable :columns="columns" :rows="transactions.data" :row-href="transactionHref">
                                <template #cell-title="{ row }">
                                    <DataTableItem
                                        :title="row.title"
                                        :subtitle="categoryLabel({ slug: row.category?.slug ?? null, name: row.category?.name ?? null })"
                                        :color="glyphColor(row)"
                                        :glyph="categoryGlyph(row.category?.slug, row.type)"
                                    />
                                </template>
                                <template #cell-party="{ row }">
                                    <span v-if="row.party" class="flex min-w-0 items-center gap-2">
                                        <Avatar size="xs">
                                            <AvatarFallback :color="row.party.color">{{ row.party.initials }}</AvatarFallback>
                                        </Avatar>
                                        <span class="truncate">{{ row.party.name.split(' ')[0] }}</span>
                                    </span>
                                </template>
                                <template #cell-transaction_date="{ row }">
                                    <span class="text-[13px] text-text-3">{{ row.transaction_date }}</span>
                                </template>
                                <template #cell-amount="{ row }">
                                    <span :class="['num text-[14.5px] font-semibold', row.type === 'income' ? 'text-pos' : '']">{{
                                        signedAmount(row)
                                    }}</span>
                                </template>
                            </DataTable>

                            <template #loading>
                                <div class="flex items-center gap-[13px] border-t border-line py-3">
                                    <Skeleton class="size-[42px] shrink-0 rounded-[14px]" />
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <Skeleton class="h-3 w-3/5" />
                                        <Skeleton class="h-2.5 w-2/5" />
                                    </div>
                                    <Skeleton class="h-4 w-14 shrink-0" />
                                </div>
                            </template>
                        </InfiniteScroll>
                    </div>
                </section>
            </div>
        </div>

        <TallyShareDialog v-model:open="showShareDialog" :tally="tally" />
    </WebLayout>
</template>
