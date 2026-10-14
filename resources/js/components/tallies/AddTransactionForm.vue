<script setup lang="ts">
import { AmountInput } from '@/components/ui/amount-input';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { ChipGroup, type ChipOption } from '@/components/ui/choice-chip';
import { FormField } from '@/components/ui/form-field';
import { filledFieldClass, Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { SegmentedControl } from '@/components/ui/segmented-control';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { categoryColorFromId } from '@/lib/category';
import tallies from '@/routes/tallies';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * Shared "Add transaction" form — the Add-tx sheet/dialog body from the design
 * source, in one screen: big `AmountInput`, type `SegmentedControl`, category
 * `ChipGroup` and filled `FormField`s. Replaces the three-step wizard; the posted
 * payload, the route and the server-side validation are unchanged, and the wizard's
 * per-step gate becomes a single completeness check on the submit button.
 *
 * Used by the full-page deep-link fallback (`pages/tallies/transactions/Create.vue`);
 * `AppOverlays` can mount the same body once the tally, category, currency and party
 * lists are available outside the per-tally controller.
 */
const props = defineProps<{
    tallyUuid: string;
    categories: Array<{ category_id: number; name: string }>;
    currencies: Array<{ currency_id: number; uuid: string; name: string; code: string; symbol: string }>;
    parties: Array<{ id: number; name: string }>;
    /** The user's default currency; prefills the currency select, which stays editable. */
    defaultCurrencyUuid?: string | null;
    /** Tallies offered by the inline Tally select; omitted on the full-page fallback, where the tally is fixed. */
    tallyOptions?: Array<{ uuid: string; name: string }>;
    /** Renders a cancel link beside submit (full-page fallback only). */
    cancelHref?: string;
}>();

const emit = defineEmits<{ (e: 'submitted'): void; (e: 'cancel'): void; (e: 'change-tally', uuid: string): void }>();

const page = usePage();
const { t } = useI18n();

const df = new DateFormatter('en-US', { dateStyle: 'long' });
const isCalendarOpen = ref(false);

const form = useForm({
    title: '',
    amount: '',
    party_id: page.props.auth.user.id.toString(),
    category_id: '',
    currency_id: props.currencies.find((currency) => currency.uuid === props.defaultCurrencyUuid)?.currency_id.toString() ?? '',
    type: 'expense' as 'expense' | 'income',
    transaction_date: today(getLocalTimeZone()).toString(),
    description: '',
});

const dateValue = computed({
    get: () => (form.transaction_date ? parseDate(form.transaction_date) : today(getLocalTimeZone())),
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

const categoryOptions = computed<ChipOption<string>[]>(() =>
    props.categories.map((category) => ({
        value: category.category_id.toString(),
        label: category.name,
        color: categoryColorFromId(category.category_id),
    })),
);

const selectedCurrency = computed(() => props.currencies.find((currency) => currency.currency_id.toString() === form.currency_id));

const isCurrencyChosen = ref(false);

const chooseCurrency = (currencyId: unknown) => {
    form.currency_id = String(currencyId);
    isCurrencyChosen.value = true;
};

/** Switching tally swaps the prefill (tally default, most-used, user default) until the user picks a currency themselves. */
watch(
    () => props.defaultCurrencyUuid,
    (uuid) => {
        if (isCurrencyChosen.value) {
            return;
        }

        form.currency_id = props.currencies.find((currency) => currency.uuid === uuid)?.currency_id.toString() ?? '';
    },
);

watch(
    () => props.parties,
    (parties) => {
        if (!parties.some((party) => party.id.toString() === form.party_id)) {
            form.party_id = (parties.find((party) => party.id === page.props.auth.user.id) ?? parties[0])?.id.toString() ?? '';
        }
    },
);

const partyLabel = computed(() => (form.type === 'income' ? t('transactions.fields.received_by') : t('transactions.fields.paid_by')));

const isComplete = computed(
    () =>
        form.title.trim().length > 0 &&
        form.category_id !== '' &&
        form.currency_id !== '' &&
        form.amount !== '' &&
        parseFloat(form.amount.replace(',', '.')) > 0,
);

const submit = () => {
    form.post(tallies.transactions.store.url(props.tallyUuid), {
        onSuccess: () => emit('submitted'),
    });
};
</script>

<template>
    <form class="flex flex-col" @submit.prevent="submit">
        <FormField :error="form.errors.amount ?? form.errors.currency_id">
            <AmountInput
                id="amount"
                v-model="form.amount"
                :label="t('transactions.fields.amount')"
                :hint="t('transactions.fields.amount_hint')"
                :currency="selectedCurrency?.symbol"
            >
                <template #currency>
                    <Select :model-value="form.currency_id" @update:model-value="chooseCurrency">
                        <SelectTrigger id="currency_id" variant="pill" class="tracking-normal" :aria-label="t('transactions.fields.currency')">
                            <SelectValue :placeholder="t('transactions.fields.currency_placeholder')">
                                {{ selectedCurrency ? `${selectedCurrency.code} ${selectedCurrency.symbol}` : '' }}
                            </SelectValue>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem v-for="currency in currencies" :key="currency.currency_id" :value="currency.currency_id.toString()">
                                    {{ currency.code }} {{ currency.symbol }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </template>
            </AmountInput>
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

        <FormField :label="t('transactions.fields.category')" :error="form.errors.category_id">
            <ChipGroup v-model="form.category_id" :options="categoryOptions" />
        </FormField>

        <div class="grid grid-cols-1 gap-x-2.5" :class="{ 'md:grid-cols-2': tallyOptions }">
            <FormField v-if="tallyOptions" :label="t('transactions.fields.tally')" for="tally_uuid">
                <Select :model-value="tallyUuid" @update:model-value="(uuid) => emit('change-tally', String(uuid))">
                    <SelectTrigger id="tally_uuid" variant="filled">
                        <SelectValue :placeholder="t('transactions.fields.tally')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectGroup>
                            <SelectItem v-for="tally in tallyOptions" :key="tally.uuid" :value="tally.uuid">{{ tally.name }}</SelectItem>
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
            <Textarea id="description" v-model="form.description" variant="filled" :placeholder="t('transactions.fields.description_placeholder')" />
        </FormField>

        <div
            class="mt-2 flex gap-3"
            :class="cancelHref ? 'justify-end' : 'sticky -bottom-5 z-10 -mx-5 bg-surface px-5 pt-3 pb-5 will-change-transform md:-bottom-6'"
        >
            <Button v-if="cancelHref" type="button" variant="ghost" size="touch" as-child>
                <Link :href="cancelHref">{{ t('common.cancel') }}</Link>
            </Button>
            <Button
                v-else
                type="button"
                variant="ghost"
                size="touch"
                class="hidden flex-1 bg-surface-2 text-text hover:bg-surface-3 md:inline-flex"
                @click="emit('cancel')"
            >
                {{ t('common.cancel') }}
            </Button>
            <Button type="submit" size="touch" :class="cancelHref ? '' : 'flex-1'" :disabled="form.processing || !isComplete">
                {{ form.processing ? t('common.adding') : t('transactions.add_button') }}
            </Button>
        </div>
    </form>
</template>
