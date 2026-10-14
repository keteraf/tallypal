<script setup lang="ts">
import type { QuickAction } from '@/components/ui/quick-actions';
import { QuickActions } from '@/components/ui/quick-actions';
import { useAddTransaction } from '@/composables/useAddTransaction';
import type { SettleSuggestion } from '@/pages/home/types';
import { formatBalanceAmount } from '@/pages/home/utils';
import talliesRoutes from '@/routes/tallies';
import { router } from '@inertiajs/vue3';
import { ArrowRightLeft, FolderPlus, Plus, Split } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    addTransactionUrl: string;
    /** The debt the Settle action opens (B-04); without one it opens the Tallies list. */
    settleSuggestion?: SettleSuggestion | null;
}>();

const { t } = useI18n();
const addTransaction = useAddTransaction();

const settleAction = computed<QuickAction>(() => {
    const suggestion = props.settleSuggestion;

    return {
        label: t('home.quick.settle'),
        sub: suggestion
            ? t('tallies.status.owes_you', {
                  name: suggestion.counterparty_name,
                  amount: formatBalanceAmount(suggestion.amount, suggestion.currency),
              })
            : t('home.quick.settle_none'),
        icon: ArrowRightLeft,
        onClick: () => router.visit(suggestion ? talliesRoutes.transactions.index.url(suggestion.tally_uuid) : talliesRoutes.index.url()),
    };
});

const actions = computed<QuickAction[]>(() => [
    {
        label: t('home.quick.add'),
        icon: Plus,
        primary: true,
        onClick: () => router.visit(props.addTransactionUrl),
    },
    {
        label: t('home.quick.split'),
        sub: t('home.quick.split_sub'),
        icon: Split,
        onClick: () => addTransaction.open(),
    },
    settleAction.value,
    {
        label: t('home.quick.new_tally'),
        icon: FolderPlus,
        onClick: () => router.visit(talliesRoutes.create.url()),
    },
]);
</script>

<template>
    <QuickActions variant="card" :title="t('home.quick.title')" :actions="actions" />
</template>
