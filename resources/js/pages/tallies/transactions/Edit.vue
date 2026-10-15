<script setup lang="ts">
import { AmountInput } from '@/components/ui/amount-input';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { CategoryPicker, type FrequentCategories, type PickerCategory } from '@/components/ui/category-picker';
import { FormField } from '@/components/ui/form-field';
import { filledFieldClass, Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { SegmentedControl } from '@/components/ui/segmented-control';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import tallies from '@/routes/tallies';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    tally: {
        tally_id: number;
        uuid: string;
        name: string;
    };
    transaction: {
        transaction_id: number;
        uuid: string;
        title: string;
        amount: number;
        party_id: number;
        category_id: number | null;
        currency_id: number;
        type: string;
        transaction_date: string;
        description: string | null;
    };
    categories: Array<PickerCategory & { name: string }>;
    /** `frequent_categories` from the form data: slugs per type, most used first. */
    frequent_categories: FrequentCategories;
    currencies: Array<{ currency_id: number; name: string; code: string; symbol: string }>;
    parties: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('tallies.title'),
        href: tallies.index.url(),
    },
    {
        title: props.tally.name,
        href: tallies.transactions.index.url(props.tally.uuid),
    },
    {
        title: t('common.edit'),
        href: tallies.transactions.edit.url({ tally: props.tally.uuid, transaction: props.transaction.uuid }),
    },
];

const df = new DateFormatter('en-US', { dateStyle: 'long' });
const isCalendarOpen = ref(false);

const form = useForm({
    title: props.transaction.title,
    amount: props.transaction.amount.toFixed(2),
    party_id: props.transaction.party_id.toString(),
    category_id: props.transaction.category_id,
    currency_id: props.transaction.currency_id.toString(),
    type: props.transaction.type as 'expense' | 'income',
    transaction_date: props.transaction.transaction_date,
    description: props.transaction.description ?? '',
});

const dateValue = computed({
    get: () => parseDate(form.transaction_date),
    set: (val) => {
        if (val) {
            form.transaction_date = val.toString();
            isCalendarOpen.value = false;
        }
    },
});

const typeOptions = [
    { value: 'expense' as const, label: t('transactions.fields.expense') },
    { value: 'income' as const, label: t('transactions.fields.income'), accent: 'positive' as const },
];

const categoryPicker = useTemplateRef('categoryPicker');

/** Esc closes one layer at a time: the picker's open panel, then its "cleared" notice. */
const dismissPickerLayer = (event: KeyboardEvent) => {
    if (categoryPicker.value?.dismiss()) {
        event.preventDefault();
        event.stopPropagation();
    }
};

const selectedCurrency = computed(() => props.currencies.find((currency) => currency.currency_id.toString() === form.currency_id));

const partyLabel = computed(() => (form.type === 'income' ? t('transactions.fields.received_by') : t('transactions.fields.paid_by')));

const submit = () => {
    form.patch(tallies.transactions.update.url({ tally: props.tally.uuid, transaction: props.transaction.uuid }));
};
</script>

<template>
    <Head :title="$t('transactions.edit_title', { name: tally.name })" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-2xl px-5 py-6 pb-28 md:px-8 md:py-10">
            <div class="mb-6 border-b border-line pb-6">
                <Link :href="tallies.transactions.index.url(tally.uuid)" class="eyebrow mb-2 block transition-colors hover:text-text">
                    {{ tally.name }}
                </Link>
                <h1 class="text-[28px] font-semibold tracking-[-0.03em] text-text md:text-3xl">
                    {{ $t('transactions.edit_heading', { name: tally.name }) }}
                </h1>
            </div>

            <div class="sm:rounded-card sm:border sm:border-line sm:bg-surface sm:p-6 md:p-7">
                <form class="flex flex-col" @submit.prevent="submit" @keydown.esc="dismissPickerLayer">
                    <FormField :error="form.errors.amount">
                        <AmountInput
                            id="amount"
                            v-model="form.amount"
                            :label="t('transactions.fields.amount')"
                            :hint="t('transactions.fields.amount_hint')"
                            :currency="selectedCurrency?.symbol"
                            :tone="form.type === 'income' ? 'positive' : 'default'"
                        />
                    </FormField>

                    <FormField :error="form.errors.type">
                        <SegmentedControl v-model="form.type" variant="mobile" :options="typeOptions" />
                    </FormField>

                    <FormField :label="t('transactions.fields.title')" for="title" :error="form.errors.title">
                        <Input
                            id="title"
                            v-model="form.title"
                            variant="filled"
                            :placeholder="t('transactions.fields.title_placeholder')"
                            maxlength="25"
                            required
                            autofocus
                        />
                    </FormField>

                    <FormField :label="t('transactions.fields.category')" for="category_id" :error="form.errors.category_id">
                        <CategoryPicker
                            ref="categoryPicker"
                            v-model="form.category_id"
                            :type="form.type"
                            :categories="categories"
                            :frequent="frequent_categories"
                        />
                    </FormField>

                    <div class="grid grid-cols-2 gap-2.5">
                        <FormField :label="t('transactions.fields.currency')" for="currency_id" :error="form.errors.currency_id">
                            <Select v-model="form.currency_id">
                                <SelectTrigger id="currency_id" variant="filled">
                                    <SelectValue :placeholder="t('transactions.fields.currency_placeholder')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="currency in currencies"
                                            :key="currency.currency_id"
                                            :value="currency.currency_id.toString()"
                                        >
                                            {{ currency.code }} · {{ currency.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </FormField>

                        <FormField :label="t('transactions.fields.date')" :error="form.errors.transaction_date">
                            <Popover v-model:open="isCalendarOpen">
                                <PopoverTrigger as-child>
                                    <button type="button" :class="[filledFieldClass, 'flex items-center gap-3 text-start']">
                                        <CalendarIcon class="size-4 shrink-0 text-text-3" />
                                        <span class="truncate">{{ df.format(dateValue.toDate(getLocalTimeZone())) }}</span>
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto rounded-card border-line bg-surface p-0">
                                    <Calendar v-model="dateValue" initial-focus />
                                </PopoverContent>
                            </Popover>
                        </FormField>
                    </div>

                    <FormField :label="partyLabel" for="party_id" :error="form.errors.party_id">
                        <Select v-model="form.party_id">
                            <SelectTrigger id="party_id" variant="filled">
                                <SelectValue :placeholder="partyLabel" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="party in parties" :key="party.id" :value="party.id.toString()">{{ party.name }}</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </FormField>

                    <FormField :label="t('transactions.fields.description')" for="description" :error="form.errors.description">
                        <Textarea
                            id="description"
                            v-model="form.description"
                            variant="filled"
                            :placeholder="t('transactions.fields.description_placeholder')"
                        />
                    </FormField>

                    <div class="mt-2 flex justify-end gap-3">
                        <Button type="button" variant="ghost" size="touch" as-child>
                            <Link :href="tallies.transactions.index.url(tally.uuid)">{{ $t('common.cancel') }}</Link>
                        </Button>
                        <Button type="submit" size="touch" :disabled="form.processing">
                            {{ form.processing ? $t('common.updating') : $t('transactions.update_button') }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </WebLayout>
</template>
