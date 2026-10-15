<script setup lang="ts">
import AddTransactionForm from '@/components/tallies/AddTransactionForm.vue';
import type { FrequentCategories, PickerCategory } from '@/components/ui/category-picker';
import WebLayout from '@/layouts/WebLayout.vue';
import tallies from '@/routes/tallies';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

interface Props {
    tally: {
        tally_id: number;
        uuid: string;
        name: string;
    };
    categories: Array<PickerCategory & { name: string }>;
    frequent_categories: FrequentCategories;
    currencies: Array<{ currency_id: number; uuid: string; name: string; code: string; symbol: string }>;
    default_currency_uuid: string | null;
    parties: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('tallies.title'), href: tallies.index.url() },
    { title: props.tally.name, href: tallies.transactions.index.url(props.tally.uuid) },
    { title: t('transactions.add_button'), href: tallies.transactions.create.url(props.tally.uuid) },
];
</script>

<template>
    <Head :title="$t('transactions.add_title', { name: tally.name })" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-2xl px-5 py-6 pb-28 md:px-8 md:py-10">
            <div class="mb-6 border-b border-line pb-6">
                <div class="eyebrow mb-2">{{ tally.name }}</div>
                <h1 class="text-[28px] font-semibold tracking-[-0.03em] text-text md:text-3xl">{{ $t('transactions.add_button') }}</h1>
            </div>

            <div class="rounded-card border border-line bg-surface p-6 md:p-7">
                <AddTransactionForm
                    :tally-uuid="tally.uuid"
                    :categories="categories"
                    :frequent-categories="frequent_categories"
                    :currencies="currencies"
                    :parties="parties"
                    :default-currency-uuid="default_currency_uuid"
                    :cancel-href="tallies.transactions.index.url(tally.uuid)"
                />
            </div>
        </div>
    </WebLayout>
</template>
