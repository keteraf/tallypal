<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { CardHeader } from '@/components/ui/card-header';
import { categoryGlyph } from '@/components/ui/category-glyph';
import type { DataTableColumn } from '@/components/ui/data-table';
import { DataTable, DataTableItem } from '@/components/ui/data-table';
import { EmptyState } from '@/components/ui/empty-state';
import { transactionGlyphColor } from '@/lib/category';
import { formatCurrency } from '@/pages/home/utils';
import type { StatsActivityItem } from '@/pages/stats/types';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps<{
    /** Latest transactions in range; every row is in its own currency, never the KPI one. */
    activity: StatsActivityItem[];
}>();

const { t, locale } = useI18n();

const columns = computed<DataTableColumn[]>(() => [
    { key: 'title', label: t('stats.activity.transaction') },
    { key: 'tally_name', label: t('stats.activity.tally') },
    { key: 'amount', label: t('stats.activity.amount'), align: 'right' },
]);

const shortDate = (value: string): string =>
    new Intl.DateTimeFormat(locale.value, { month: 'short', day: '2-digit', timeZone: 'UTC' }).format(new Date(`${value.slice(0, 10)}T00:00:00Z`));

const signedAmount = (row: StatsActivityItem): string =>
    `${row.type === 'income' ? '+' : '−'} ${row.currency_symbol} ${formatCurrency(row.amount, row.decimals)}`;
</script>

<template>
    <EmptyState v-if="activity.length === 0" :title="t('stats.activity.empty_title')" :description="t('stats.activity.empty_description')" />

    <section v-else class="rounded-card bg-surface p-4 sm:p-[22px]">
        <CardHeader :title="t('stats.activity.title')" />

        <DataTable :columns="columns" :rows="activity">
            <template #cell-title="{ row }">
                <DataTableItem
                    :title="row.title"
                    :subtitle="shortDate(row.transaction_date)"
                    :color="transactionGlyphColor(row.type, row.category_name)"
                    :glyph="categoryGlyph(row.category_id ?? undefined, row.type)"
                >
                    <template #inline>
                        <Badge :variant="row.is_shared ? 'accent' : 'pill'" class="ml-[5px] whitespace-nowrap">{{ row.tally_name }}</Badge>
                    </template>
                </DataTableItem>
            </template>
            <template #cell-tally_name="{ row }">
                <Badge v-if="row.is_shared" variant="accent" class="whitespace-nowrap">{{ row.tally_name }}</Badge>
                <span v-else class="truncate">{{ row.tally_name }}</span>
            </template>
            <template #cell-amount="{ row }">
                <span :class="['num text-[14.5px] font-semibold', row.type === 'income' ? 'text-pos' : '']">{{ signedAmount(row) }}</span>
            </template>
        </DataTable>
    </section>
</template>
