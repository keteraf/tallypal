<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { CardHeader } from '@/components/ui/card-header';
import { categoryGlyph } from '@/components/ui/category-glyph';
import type { DataTableColumn } from '@/components/ui/data-table';
import { DataTable, DataTableItem } from '@/components/ui/data-table';
import { transactionGlyphColor } from '@/lib/category';
import tallies from '@/routes/tallies';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { PrimaryTally, RecentTransaction } from '../types';
import { formatCurrency } from '../utils';

const props = defineProps<{
    transactions: RecentTransaction[];
    primaryTally: PrimaryTally | null;
    decimals: number;
}>();

const { t } = useI18n();

const columns = computed<DataTableColumn[]>(() => [
    { key: 'title', label: t('home.recent.columns.transaction') },
    { key: 'tally_name', label: t('home.recent.columns.tally'), hideBelow: 'xl' },
    { key: 'transaction_date', label: t('home.recent.columns.date') },
    { key: 'amount', label: t('home.recent.columns.amount'), align: 'right' },
]);

const rows = computed(() => props.transactions.map((tx) => ({ ...tx })));

const txHref = (): string => (props.primaryTally ? tallies.transactions.index.url(props.primaryTally.uuid) : '/tallies');

const signedAmount = (tx: RecentTransaction): string =>
    `${tx.type === 'income' ? '+' : '−'} ${tx.currency_symbol} ${formatCurrency(tx.amount, props.decimals)}`;
</script>

<template>
    <section class="rounded-card bg-surface p-4 sm:p-[22px]">
        <CardHeader
            :title="t('home.recent.title')"
            :action-label="primaryTally ? t('home.recent.view_all') : undefined"
            :action-href="primaryTally ? tallies.transactions.index.url(primaryTally.uuid) : undefined"
        />

        <p v-if="transactions.length === 0" class="py-10 text-center text-[13px] text-text-3">
            {{ t('home.recent.empty') }}
        </p>

        <DataTable v-else :columns="columns" :rows="rows" :row-href="txHref">
            <template #cell-title="{ row }">
                <DataTableItem
                    :title="row.title"
                    :subtitle="row.category_name ?? row.type"
                    :color="transactionGlyphColor(row.type, row.category_name ?? row.category_code)"
                    :glyph="categoryGlyph(row.category_slug, row.type)"
                >
                    <template #inline>
                        <Badge :variant="row.is_shared ? 'accent' : 'pill'" class="ml-1.5 whitespace-nowrap">{{ row.tally_name }}</Badge>
                    </template>
                </DataTableItem>
            </template>
            <template #cell-tally_name="{ row }">
                <Badge v-if="row.is_shared" variant="accent" class="whitespace-nowrap">{{ row.tally_name }}</Badge>
                <span v-else class="truncate">{{ row.tally_name }}</span>
            </template>
            <template #cell-transaction_date="{ row }">
                <span class="text-[13px] text-text-3">{{ row.transaction_date }}</span>
            </template>
            <template #cell-amount="{ row }">
                <span :class="['num text-[14.5px] font-semibold', row.type === 'income' ? 'text-pos' : '']">{{ signedAmount(row) }}</span>
            </template>
        </DataTable>
    </section>
</template>
