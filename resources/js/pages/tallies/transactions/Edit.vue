<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { cn } from '@/lib/utils';
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
    categories: Array<{
        category_id: number;
        name: string;
    }>;
    currencies: Array<{
        currency_id: number;
        name: string;
        code: string;
    }>;
    parties: Array<{
        id: number;
        name: string;
    }>;
}

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('tallies.title'),
        href: route('tallies.index'),
    },
    {
        title: props.tally.name,
        href: route('tallies.transactions.index', props.tally.uuid),
    },
    {
        title: t('common.edit'),
        href: route('tallies.transactions.edit', { tally: props.tally.uuid, transaction: props.transaction.uuid }),
    },
];

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const dateValue = ref<any>(parseDate(props.transaction.transaction_date));
const isCalendarOpen = ref(false);

const transactionType = ref(props.transaction.type);

const partyLabel = (type: string | undefined) => {
    return type === 'income' ? t('transactions.fields.received_by') : t('transactions.fields.paid_by');
};
</script>

<template>
    <Head :title="$t('transactions.edit_title', { name: tally.name })" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ $t('transactions.edit_heading', { name: tally.name }) }}</h1>
            </div>

            <div class="max-w-2xl rounded-lg border p-6 shadow-sm">
                <Form
                    :action="route('tallies.transactions.update', { tally: tally.uuid, transaction: transaction.uuid })"
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
                    class="space-y-6"
                >
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="title">{{ $t('transactions.fields.title') }}</Label>
                            <Input
                                id="title"
                                name="title"
                                :default-value="transaction.title"
                                :placeholder="$t('transactions.fields.title_placeholder')"
                                required
                                autofocus
                            />
                            <div v-if="errors.title" class="text-sm font-medium text-destructive">
                                {{ errors.title }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="amount">{{ $t('transactions.fields.amount') }}</Label>
                            <Input
                                id="amount"
                                name="amount"
                                type="number"
                                step="0.01"
                                :default-value="transaction.amount.toFixed(2)"
                                :placeholder="$t('transactions.fields.amount_placeholder')"
                                required
                            />
                            <div v-if="errors.amount" class="text-sm font-medium text-destructive">
                                {{ errors.amount }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">{{ $t('transactions.fields.type') }}</Label>
                            <Select name="type" v-model="transactionType" required>
                                <SelectTrigger id="type">
                                    <SelectValue :placeholder="$t('transactions.fields.type')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="expense">{{ $t('transactions.fields.expense') }}</SelectItem>
                                        <SelectItem value="income">{{ $t('transactions.fields.income') }}</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="errors.type" class="text-sm font-medium text-destructive">
                                {{ errors.type }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="party_id">{{ partyLabel(transactionType) }}</Label>
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
                            <div v-if="errors.party_id" class="text-sm font-medium text-destructive">
                                {{ errors.party_id }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="category_id">{{ $t('transactions.fields.category') }}</Label>
                            <Select name="category_id" :default-value="transaction.category_id.toString()" required>
                                <SelectTrigger id="category_id">
                                    <SelectValue :placeholder="$t('transactions.fields.category')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="category in categories"
                                            :key="category.category_id"
                                            :value="category.category_id.toString()"
                                        >
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="errors.category_id" class="text-sm font-medium text-destructive">
                                {{ errors.category_id }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="currency_id">{{ $t('transactions.fields.currency') }}</Label>
                            <Select name="currency_id" :default-value="transaction.currency_id.toString()" required>
                                <SelectTrigger id="currency_id">
                                    <SelectValue :placeholder="$t('transactions.fields.currency')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="currency in currencies"
                                            :key="currency.currency_id"
                                            :value="currency.currency_id.toString()"
                                        >
                                            {{ currency.code }} - {{ currency.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="errors.currency_id" class="text-sm font-medium text-destructive">
                                {{ errors.currency_id }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="transaction_date">{{ $t('transactions.fields.date') }}</Label>
                            <Popover v-model:open="isCalendarOpen">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        :class="cn('w-full justify-start text-left font-normal', !dateValue && 'text-muted-foreground')"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ dateValue ? df.format(dateValue.toDate(getLocalTimeZone())) : $t('transactions.fields.date_placeholder') }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0">
                                    <Calendar v-model="dateValue" initial-focus @update:model-value="isCalendarOpen = false" />
                                </PopoverContent>
                            </Popover>
                            <input type="hidden" name="transaction_date" :value="dateValue?.toString()" />
                            <div v-if="errors.transaction_date" class="text-sm font-medium text-destructive">
                                {{ errors.transaction_date }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ $t('transactions.fields.description') }}</Label>
                        <Textarea
                            id="description"
                            name="description"
                            :default-value="transaction.description ?? ''"
                            :placeholder="$t('transactions.fields.description_placeholder')"
                            class="min-h-25"
                        />
                        <div v-if="errors.description" class="text-sm font-medium text-destructive">
                            {{ errors.description }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button variant="outline" as-child>
                            <Link :href="route('tallies.transactions.index', tally.uuid)"> {{ $t('common.cancel') }} </Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? $t('common.updating') : $t('transactions.update_button') }}
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </WebLayout>
</template>
