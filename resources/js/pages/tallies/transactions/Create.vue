<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { SegmentedControl } from '@/components/ui/segmented-control';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Stepper } from '@/components/ui/stepper';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import tallies from '@/routes/tallies';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, today } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    tally: {
        tally_id: number;
        uuid: string;
        name: string;
    };
    categories: Array<{ category_id: number; name: string }>;
    currencies: Array<{ currency_id: number; name: string; code: string }>;
    parties: Array<{ id: number; name: string }>;
}

const props = defineProps<Props>();

const page = usePage();
const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('tallies.title'), href: tallies.index.url() },
    { title: props.tally.name, href: tallies.transactions.index.url(props.tally.uuid) },
    { title: t('transactions.add_button'), href: tallies.transactions.create.url(props.tally.uuid) },
];

const df = new DateFormatter('en-US', { dateStyle: 'long' });
const isCalendarOpen = ref(false);

const form = useForm({
    title: '',
    amount: '',
    party_id: page.props.auth.user.id.toString(),
    category_id: '',
    currency_id: '',
    type: 'expense' as 'expense' | 'income',
    transaction_date: today(getLocalTimeZone()).toString(),
    description: '',
});

const currentStep = ref(1);
const totalSteps = 3;

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

const partyLabel = computed(() =>
    form.type === 'income' ? t('transactions.fields.received_by') : t('transactions.fields.paid_by'),
);

const steps = computed(() => [
    { label: t('transactions.steps.what') },
    { label: t('transactions.steps.how_much') },
    { label: t('transactions.steps.when') },
]);

const canAdvance = computed(() => {
    if (currentStep.value === 1) {
        return form.title.trim().length > 0 && form.category_id !== '';
    }
    if (currentStep.value === 2) {
        return form.amount !== '' && parseFloat(form.amount) > 0 && form.currency_id !== '';
    }
    return true;
});

function nextStep() {
    if (canAdvance.value && currentStep.value < totalSteps) {
        currentStep.value++;
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
}

const stepFields: Record<number, string[]> = {
    1: ['title', 'type', 'category_id', 'party_id', 'description'],
    2: ['amount', 'currency_id'],
    3: ['transaction_date'],
};

function submit() {
    form.post(tallies.transactions.store.url(props.tally.uuid), {
        onError: (errors) => {
            for (let s = 1; s <= totalSteps; s++) {
                if (stepFields[s].some((f) => f in errors)) {
                    currentStep.value = s;
                    break;
                }
            }
        },
    });
}
</script>

<template>
    <Head :title="$t('transactions.add_title', { name: tally.name })" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-330 px-6 py-10 md:px-8">
            <h1 class="m-0 mb-7 font-serif text-[clamp(32px,4vw,40px)] leading-[1.05] tracking-[-0.02em]">
                {{ $t('transactions.add_lead') }}
                <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ tally.name }}</em>
            </h1>

            <div class="max-w-220 rounded-sm border border-rule bg-background px-9 pt-9 pb-7">
                <Stepper :steps="steps" :model-value="currentStep" class="mb-9" />

                <!-- Step 1: What -->
                <div v-if="currentStep === 1" class="flex flex-col gap-7">
                    <div class="flex flex-col gap-2">
                        <label for="title" class="eyebrow">{{ $t('transactions.fields.title') }}</label>
                        <Input
                            id="title"
                            v-model="form.title"
                            :placeholder="$t('transactions.fields.title_placeholder')"
                            maxlength="25"
                            autofocus
                        />
                        <div v-if="form.errors.title" class="text-sm font-medium text-destructive">{{ form.errors.title }}</div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="eyebrow">{{ $t('transactions.fields.type') }}</label>
                        <SegmentedControl v-model="form.type" :options="typeOptions" />
                        <div v-if="form.errors.type" class="text-sm font-medium text-destructive">{{ form.errors.type }}</div>
                    </div>

                    <div class="grid gap-7 md:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label for="party_id" class="eyebrow">{{ partyLabel }}</label>
                            <Select v-model="form.party_id">
                                <SelectTrigger id="party_id">
                                    <SelectValue :placeholder="partyLabel" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem v-for="party in parties" :key="party.id" :value="party.id.toString()">
                                            {{ party.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.party_id" class="text-sm font-medium text-destructive">{{ form.errors.party_id }}</div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="category_id" class="eyebrow">{{ $t('transactions.fields.category') }}</label>
                            <Select v-model="form.category_id">
                                <SelectTrigger id="category_id">
                                    <SelectValue :placeholder="$t('transactions.fields.category_placeholder')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="category in categories"
                                            :key="category.category_id"
                                            :value="category.category_id.toString()"
                                        >{{ category.name }}</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.category_id" class="text-sm font-medium text-destructive">{{ form.errors.category_id }}</div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="description" class="eyebrow">{{ $t('transactions.fields.description') }}</label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            :placeholder="$t('transactions.fields.description_placeholder')"
                        />
                        <div v-if="form.errors.description" class="text-sm font-medium text-destructive">{{ form.errors.description }}</div>
                    </div>
                </div>

                <!-- Step 2: How Much -->
                <div v-else-if="currentStep === 2" class="flex flex-col gap-7">
                    <div class="flex flex-col gap-2">
                        <label for="amount" class="eyebrow">{{ $t('transactions.fields.amount') }}</label>
                        <Input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            :placeholder="$t('transactions.fields.amount_placeholder')"
                            class="tabular font-mono text-[22px] tracking-[-0.01em]"
                            autofocus
                        />
                        <div v-if="form.errors.amount" class="text-sm font-medium text-destructive">{{ form.errors.amount }}</div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="currency_id" class="eyebrow">{{ $t('transactions.fields.currency') }}</label>
                        <Select v-model="form.currency_id">
                            <SelectTrigger id="currency_id">
                                <SelectValue :placeholder="$t('transactions.fields.currency_placeholder')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem
                                        v-for="currency in currencies"
                                        :key="currency.currency_id"
                                        :value="currency.currency_id.toString()"
                                    >{{ currency.code }} · {{ currency.name }}</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <div v-if="form.errors.currency_id" class="text-sm font-medium text-destructive">{{ form.errors.currency_id }}</div>
                    </div>
                </div>

                <!-- Step 3: When -->
                <div v-else class="flex flex-col gap-7">
                    <div class="flex flex-col gap-2">
                        <label class="eyebrow">{{ $t('transactions.fields.date') }}</label>
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
                                <Calendar v-model="dateValue" initial-focus />
                            </PopoverContent>
                        </Popover>
                        <div v-if="form.errors.transaction_date" class="text-sm font-medium text-destructive">{{ form.errors.transaction_date }}</div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="mt-9 flex items-center justify-between border-t border-rule-2 pt-6">
                    <Button v-if="currentStep > 1" variant="ghost" @click="prevStep">
                        ← {{ $t('transactions.steps.back') }}
                    </Button>
                    <Button v-else variant="ghost" as-child>
                        <Link :href="tallies.transactions.index.url(tally.uuid)">{{ $t('common.cancel') }}</Link>
                    </Button>

                    <Button v-if="currentStep < totalSteps" :disabled="!canAdvance" @click="nextStep">
                        {{ $t('transactions.steps.next') }} →
                    </Button>
                    <Button v-else :disabled="form.processing" @click="submit">
                        {{ form.processing ? $t('common.creating') : $t('transactions.add_button') }} →
                    </Button>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
