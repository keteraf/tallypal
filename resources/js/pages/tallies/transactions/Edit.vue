<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, type DateValue } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    tally: {
        tally_id: number;
        name: string;
    };
    transaction: {
        transaction_id: number;
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tallies',
        href: route('tallies.index'),
    },
    {
        title: props.tally.name,
        href: route('tallies.transactions.index', props.tally.tally_id),
    },
    {
        title: 'Edit Transaction',
        href: route('tallies.transactions.edit', { tally: props.tally.tally_id, transaction: props.transaction.transaction_id }),
    },
];

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

const dateValue = ref<DateValue>(parseDate(props.transaction.transaction_date));
const isCalendarOpen = ref(false);

const transactionType = ref(props.transaction.type);

const partyLabel = (type: string | undefined) => {
    return type === 'income' ? 'Received by' : 'Paid by';
};
</script>

<template>
    <Head :title="`Edit Transaction - ${tally.name}`" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Edit Transaction in {{ tally.name }}</h1>
            </div>

            <div class="max-w-2xl rounded-lg border p-6 shadow-sm">
                <Form
                    :action="route('tallies.transactions.update', { tally: tally.tally_id, transaction: transaction.transaction_id })"
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
                            <Label for="title">Title</Label>
                            <Input id="title" name="title" :default-value="transaction.title" placeholder="e.g. Grocery shopping" required autofocus />
                            <div v-if="errors.title" class="text-sm font-medium text-destructive">
                                {{ errors.title }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="amount">Amount</Label>
                            <Input id="amount" name="amount" type="number" step="0.01" :default-value="transaction.amount.toFixed(2)" placeholder="0.00" required />
                            <div v-if="errors.amount" class="text-sm font-medium text-destructive">
                                {{ errors.amount }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">Type</Label>
                            <select
                                id="type"
                                name="type"
                                v-model="transactionType"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                            <div v-if="errors.type" class="text-sm font-medium text-destructive">
                                {{ errors.type }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="party_id">{{ partyLabel(transactionType) }}</Label>
                            <select
                                id="party_id"
                                name="party_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option v-for="party in parties" :key="party.id" :value="party.id" :selected="party.id === transaction.party_id">
                                    {{ party.name }}
                                </option>
                            </select>
                            <div v-if="errors.party_id" class="text-sm font-medium text-destructive">
                                {{ errors.party_id }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="category_id">Category</Label>
                            <select
                                id="category_id"
                                name="category_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option v-for="category in categories" :key="category.category_id" :value="category.category_id" :selected="category.category_id === transaction.category_id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="errors.category_id" class="text-sm font-medium text-destructive">
                                {{ errors.category_id }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="currency_id">Currency</Label>
                            <select
                                id="currency_id"
                                name="currency_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option v-for="currency in currencies" :key="currency.currency_id" :value="currency.currency_id" :selected="currency.currency_id === transaction.currency_id">
                                    {{ currency.code }} - {{ currency.name }}
                                </option>
                            </select>
                            <div v-if="errors.currency_id" class="text-sm font-medium text-destructive">
                                {{ errors.currency_id }}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="transaction_date">Date</Label>
                            <Popover v-model:open="isCalendarOpen">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        :class="cn('w-full justify-start text-left font-normal', !dateValue && 'text-muted-foreground')"
                                    >
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{ dateValue ? df.format(dateValue.toDate(getLocalTimeZone())) : 'Pick a date' }}
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
                        <Label for="description">Description (Optional)</Label>
                        <Textarea id="description" name="description" :default-value="transaction.description ?? ''" placeholder="More details about the transaction" class="min-h-25" />
                        <div v-if="errors.description" class="text-sm font-medium text-destructive">
                            {{ errors.description }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button variant="outline" as-child>
                            <Link :href="route('tallies.transactions.index', tally.tally_id)"> Cancel </Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </Form>
            </div>
        </div>
    </WebLayout>
</template>
