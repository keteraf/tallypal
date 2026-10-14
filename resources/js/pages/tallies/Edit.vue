<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChipGroup, type ChipOption } from '@/components/ui/choice-chip';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import WebLayout from '@/layouts/WebLayout.vue';
import { CATEGORY_COLORS, TALLY_GLYPHS } from '@/lib/category';
import tallies from '@/routes/tallies';
import { type BreadcrumbItem, type CurrencyOption } from '@/types';
import { Form, Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    other_members_count: number;
    currencies: CurrencyOption[];
    tally: {
        tally_id: number;
        uuid: string;
        name: string;
        description: string;
        owner_id: number;
        color: string | null;
        glyph: string | null;
        default_currency_uuid: string | null;
    };
}>();

const page = usePage();
const { t } = useI18n();
const isOwner = computed(() => page.props.auth.user.id === props.tally.owner_id);

const selectedColor = ref<string>(props.tally.color ?? '');
const selectedGlyph = ref<string>(props.tally.glyph ?? '');
const NO_CURRENCY = 'none';
const selectedCurrency = ref<string>(props.tally.default_currency_uuid ?? NO_CURRENCY);

const colorOptions: ChipOption<string>[] = CATEGORY_COLORS.map((c) => ({
    value: c,
    color: c,
}));

const glyphOptions: ChipOption<string>[] = TALLY_GLYPHS.map((g) => ({
    value: g,
    glyph: g,
}));

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
        href: tallies.edit.url(props.tally.uuid),
    },
];

const deleteForm = useForm({});

const deleteTally = () => {
    deleteForm.delete(tallies.destroy.url(props.tally.uuid));
};
</script>

<template>
    <Head :title="$t('tallies.edit_title', { name: tally.name })" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-2xl px-5 py-6 pb-28 md:px-8 md:py-10">
            <div class="mb-6 border-b border-line pb-6">
                <Link :href="tallies.transactions.index.url(tally.uuid)" class="eyebrow mb-2 block transition-colors hover:text-text">
                    {{ tally.name }}
                </Link>
                <h1 class="text-[28px] font-semibold tracking-[-0.03em] text-text md:text-3xl">{{ $t('tallies.edit_heading') }}</h1>
            </div>

            <div class="rounded-card border border-line bg-surface p-6 md:p-7">
                <Form
                    :action="tallies.update.url(tally.uuid)"
                    method="put"
                    :defaults="{
                        name: tally.name,
                        description: tally.description,
                        color: tally.color ?? '',
                        glyph: tally.glyph ?? '',
                    }"
                    #default="{ errors, processing }"
                >
                    <FormField :label="$t('tallies.name_label')" for="name" :error="errors.name">
                        <Input
                            id="name"
                            name="name"
                            variant="filled"
                            :default-value="tally.name"
                            maxlength="50"
                            :placeholder="$t('tallies.name_placeholder')"
                            required
                            autofocus
                        />
                    </FormField>

                    <FormField :label="$t('tallies.description_label')" for="description" :error="errors.description">
                        <Textarea
                            id="description"
                            name="description"
                            variant="filled"
                            :default-value="tally.description"
                            :placeholder="$t('tallies.description_placeholder')"
                        />
                    </FormField>

                    <FormField :label="$t('tallies.color_icon_label')">
                        <ChipGroup v-model="selectedColor" :options="colorOptions" />
                        <ChipGroup v-model="selectedGlyph" :options="glyphOptions" class="mt-2" />
                        <input type="hidden" name="color" :value="selectedColor" />
                        <input type="hidden" name="glyph" :value="selectedGlyph" />
                    </FormField>

                    <FormField :label="$t('tallies.default_currency_label')" for="default_currency_uuid" :error="errors.default_currency_uuid">
                        <Select v-model="selectedCurrency">
                            <SelectTrigger id="default_currency_uuid" variant="filled">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="NO_CURRENCY">{{ $t('settings.you.default_currency_none') }}</SelectItem>
                                <SelectItem v-for="currency in currencies" :key="currency.uuid" :value="currency.uuid">
                                    {{ currency.code }} {{ currency.symbol }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <input type="hidden" name="default_currency_uuid" :value="selectedCurrency === NO_CURRENCY ? '' : selectedCurrency" />
                        <p class="mt-1.5 text-[12px] text-text-3">{{ $t('tallies.default_currency_hint') }}</p>
                    </FormField>

                    <div class="mt-2 flex justify-end gap-3">
                        <Button type="button" variant="ghost" size="touch" as-child>
                            <Link :href="tallies.transactions.index.url(tally.uuid)">{{ $t('common.cancel') }}</Link>
                        </Button>
                        <Button type="submit" size="touch" :disabled="processing">
                            {{ processing ? $t('common.updating') : $t('common.update') }}
                        </Button>
                    </div>
                </Form>
            </div>

            <div v-if="isOwner" class="mt-6 space-y-4 rounded-sm border border-neg/30 bg-neg/[0.05] px-5 py-4">
                <div class="space-y-1 text-neg">
                    <p class="font-mono text-xs tracking-[0.12em] uppercase">{{ $t('common.warning') }}</p>
                    <p class="text-sm">{{ $t('tallies.delete_confirm_description', { name: tally.name }) }}</p>
                </div>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="destructive">
                            <Trash2 class="size-4" />
                            {{ $t('tallies.delete_tally') }}
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>{{ $t('tallies.delete_confirm_title') }}</DialogTitle>
                            <DialogDescription>{{ $t('tallies.delete_confirm_description', { name: tally.name }) }}</DialogDescription>
                            <p v-if="other_members_count > 0" class="text-sm font-medium text-neg">
                                {{ $t('tallies.delete_shared_notice', { name: tally.name, count: other_members_count }, other_members_count) }}
                            </p>
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="ghost" type="button">{{ $t('common.cancel') }}</Button>
                            </DialogClose>
                            <Button variant="destructive" :disabled="deleteForm.processing" @click="deleteTally">
                                {{ deleteForm.processing ? $t('common.deleting') : $t('tallies.delete_tally') }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </WebLayout>
</template>
