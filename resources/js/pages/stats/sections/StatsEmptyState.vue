<script setup lang="ts">
import { EmptyState } from '@/components/ui/empty-state';
import { useAddTransaction } from '@/composables/useAddTransaction';

const addTransaction = useAddTransaction();
</script>

<template>
    <EmptyState
        :title="$t('stats.empty_title')"
        :description="$t('stats.empty_description')"
        :action-label="$t('navigation.add_transaction_title')"
        @action="addTransaction.open()"
    />
</template>
