<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { SegmentedControl } from '@/components/ui/segmented-control';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import tallies from '@/routes/tallies';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { ref } from 'vue';
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
        category_id: number;
        currency_id: number;
        type: string;
        transaction_date: string;
        description: string | null;
    };
    categories: Array<{ category_id: number; name: string }>;
    currencies: Array<{ currency_id: number; name: string; code: string }>;
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

const dateValue = ref<any>(parseDate(props.transaction.transaction_date));
const isCalendarOpen = ref(false);

const transactionType = ref<'expense' | 'income'>(props.transaction.type as 'expense' | 'income');

const partyLabel = (type: string | undefined) => {
    return type === 'income' ? t('transactions.fields.received_by') : t('transactions.fields.paid_by');
};

const typeOptions = [
    { value: 'expense' as const, label: t('transactions.fields.expense') },
    { value: 'income' as const, label: t('transactions.fields.income'), accent: 'positive' as const },
];
</script>

<template>
    <Head :title="$t('transactions.edit_title', { name: tally.name })" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-330 px-6 py-10 md:px-8">
            <h1 class="m-0 mb-7 font-serif text-[clamp(32px,4vw,40px)] leading-[1.05] tracking-[-0.02em]">
                {{ $t('transactions.edit_lead') }}
                <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ transaction.title }}</em>
            </h1>

            <div class="max-w-220 rounded-sm border border-rule bg-background px-9 pt-9 pb-7">
                <Form
                    :action="tallies.transactions.update.url({ tally: tally.uuid, transaction: transaction.uuid })"
                    method="patch"
                    :defaults="{
                        title: transaction.title,
                        amount: transaction.amount.toFixed(2),
                        party_id: transaction.party_id,
                        category_id: transaction.category_id,
                        currency_id: transaction.currency_id,
                        type: transaction.type,
                        transaction_date: transaction.transaction_date,
                        description: transaction.description ?? '',
                    }"
                    #default="{ errors, processing }"
                >
                    <div class="grid gap-7 md:grid-cols-2 md:gap-x-7 md:gap-y-7">
                        <div class="flex flex-col gap-2">
                            <label for="title" class="eyebrow">{{ $t('transactions.fields.title') }}</label>
                            <Input
                                id="title"
                                name="title"
                                :default-value="transaction.title"
                                :placeholder="$t('transactions.fields.title_placeholder')"
                                required
                                autofocus
                            />
                            <div v-if="errors.title" class="text-sm font-medium text-destructive">{{ errors.title }}</div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="amount" class="eyebrow">{{ $t('transactions.fields.amount') }}</label>
                            <Input
                                id="amount"
                                name="amount"
                                type="number"
                                step="0.01"
                                :default-value="transaction.amount.toFixed(2)"
                                :placeholder="$t('transactions.fields.amount_placeholder')"
                                class="tabular font-mono text-[22px] tracking-[-0.01em]"
                                required
                            />
                            <div v-if="errors.amount" class="text-sm font-medium text-destructive">{{ errors.amount }}</div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="eyebrow">{{ $t('transactions.fields.type') }}</label>
                            <SegmentedControl v-model="transactionType" :options="typeOptions" name="type" />
                            <input type="hidden" name="type" :value="transactionType" />
                            <div v-if="errors.type" class="text-sm font-medium text-destructive">{{ errors.type }}</div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="party_id" class="eyebrow">{{ partyLabel(transactionType) }}</label>
                            <Select name="party_id" :default-value="transaction.party_id.toString()" required>
                                <SelectTrigger id="party_id">
                                    <SelectValue :placeholder="partyLabel(transactionType)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="party in parties" :key="party.id" :value="party.id.toString()">
                                            {{ party.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="errors.party_id" class="text-sm font-medium text-destructive">{{ errors.party_id }}</div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="category_id" class="eyebrow">{{ $t('transactions.fields.category') }}</label>
                            <Select name="category_id" :default-value="transaction.category_id.toString()" required>
                                <SelectTrigger id="category_id">
                                    <SelectValue :placeholder="$t('transactions.fields.category_placeholder')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="category in categories"
                                            :key="category.category_id"
                                            :value="category.category_id.toString()"
                                            >{{ category.name }}</SelectItem
                                        >
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="errors.category_id" class="text-sm font-medium text-destructive">{{ errors.category_id }}</div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="currency_id" class="eyebrow">{{ $t('transactions.fields.currency') }}</label>
                            <Select name="currency_id" :default-value="transaction.currency_id.toString()" required>
                                <SelectTrigger id="currency_id">
                                    <SelectValue :placeholder="$t('transactions.fields.currency_placeholder')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="currency in currencies"
                                            :key="currency.currency_id"
                                            :value="currency.currency_id.toString()"
                                            >{{ currency.code }} · {{ currency.name }}</SelectItem
                                        >
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="errors.currency_id" class="text-sm font-medium text-destructive">{{ errors.currency_id }}</div>
                        </div>

                        <div class="flex flex-col gap-2 md:col-span-2">
                            <label for="transaction_date" class="eyebrow">{{ $t('transactions.fields.date') }}</label>
                            <Popover v-model:open="isCalendarOpen">
                                <PopoverTrigger as-child>
                                    <button
                                        type="button"
                                        class="flex items-center gap-3 border-0 border-b border-rule bg-transparent px-0 pt-2.5 pb-2.5 text-left text-foreground transition-colors hover:border-foreground"
                                    >
                                        <CalendarIcon class="size-4 text-muted-foreground" />
                                        <span :class="dateValue ? '' : 'text-muted-foreground'">
                                            {{
                                                dateValue
                                                    ? df.format(dateValue.toDate(getLocalTimeZone()))
                                                    : $t('transactions.fields.date_placeholder')
                                            }}
                                        </span>
                                    </button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar v-model="dateValue" initial-focus @update:model-value="isCalendarOpen = false" />
                                </PopoverContent>
                            </Popover>
                            <input type="hidden" name="transaction_date" :value="dateValue?.toString()" />
                            <div v-if="errors.transaction_date" class="text-sm font-medium text-destructive">{{ errors.transaction_date }}</div>
                        </div>

                        <div class="flex flex-col gap-2 md:col-span-2">
                            <label for="description" class="eyebrow">{{ $t('transactions.fields.description') }}</label>
                            <Textarea
                                id="description"
                                name="description"
                                :default-value="transaction.description ?? ''"
                                :placeholder="$t('transactions.fields.description_placeholder')"
                            />
                            <div v-if="errors.description" class="text-sm font-medium text-destructive">{{ errors.description }}</div>
                        </div>
                    </div>

                    <div class="mt-9 flex justify-end gap-3 border-t border-rule-2 pt-6">
                        <Button variant="ghost" as-child>
                            <Link :href="tallies.transactions.index.url(tally.uuid)">{{ $t('common.cancel') }}</Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? $t('common.updating') : $t('transactions.update_button') }} →
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </WebLayout>
</template>
