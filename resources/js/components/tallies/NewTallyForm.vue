<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChipGroup, type ChipOption } from '@/components/ui/choice-chip';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CATEGORY_COLORS, TALLY_GLYPHS } from '@/lib/category';
import tallies from '@/routes/tallies';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * Shared "New tally" form: name plus one combined colour & icon choice. Used both as
 * the bottom-sheet/dialog body (via AppOverlays) and as the full-page deep-link
 * fallback (`pages/tallies/Create.vue`).
 */
const { t } = useI18n();

withDefaults(defineProps<{ cancelHref?: string }>(), {
    cancelHref: undefined,
});

const emit = defineEmits<{ (e: 'submitted'): void }>();

const page = usePage();
const currencies = computed(() => page.props.currencies ?? []);
const NO_CURRENCY = 'none';

const form = useForm({
    name: '',
    color: CATEGORY_COLORS[0] as string,
    glyph: TALLY_GLYPHS[0] as string,
    /** Starts at the user's default currency; only prefills new transactions, never restricts them. */
    default_currency_uuid: (page.props.auth.user.default_currency?.uuid ?? null) as string | null,
});

const defaultCurrency = computed({
    get: () => form.default_currency_uuid ?? NO_CURRENCY,
    set: (uuid: string) => {
        form.default_currency_uuid = uuid === NO_CURRENCY ? null : uuid;
    },
});

/** Each chip pairs a palette colour with a glyph; the colour is the chip's value. */
const styleOptions: ChipOption<string>[] = CATEGORY_COLORS.map((c, index) => ({
    value: c,
    color: c,
    glyph: TALLY_GLYPHS[index],
}));

const style = computed({
    get: () => form.color,
    set: (color: string) => {
        form.color = color;
        form.glyph = TALLY_GLYPHS[CATEGORY_COLORS.indexOf(color as (typeof CATEGORY_COLORS)[number])];
    },
});

const submit = () => {
    form.post(tallies.store.url(), {
        onSuccess: () => emit('submitted'),
    });
};
</script>

<template>
    <form class="flex flex-col" @submit.prevent="submit">
        <FormField :label="t('tallies.name_label')" for="name" :error="form.errors.name">
            <Input id="name" v-model="form.name" variant="filled" :placeholder="t('tallies.name_placeholder')" maxlength="50" autofocus />
        </FormField>

        <FormField :label="t('tallies.color_icon_label')">
            <ChipGroup v-model="style" :options="styleOptions" />
        </FormField>

        <FormField :label="t('tallies.default_currency_label')" for="default_currency_uuid" :error="form.errors.default_currency_uuid">
            <Select v-model="defaultCurrency">
                <SelectTrigger id="default_currency_uuid" variant="filled">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="NO_CURRENCY">{{ t('settings.you.default_currency_none') }}</SelectItem>
                    <SelectItem v-for="currency in currencies" :key="currency.uuid" :value="currency.uuid">
                        {{ currency.code }} {{ currency.symbol }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <p class="mt-1.5 text-[12px] text-text-3">{{ t('tallies.default_currency_hint') }}</p>
        </FormField>

        <div
            class="mt-2 flex gap-3"
            :class="cancelHref ? 'justify-end' : 'sticky -bottom-5 z-10 -mx-5 bg-surface px-5 pt-3 pb-5 will-change-transform md:-bottom-6'"
        >
            <Button v-if="cancelHref" type="button" variant="ghost" size="touch" as-child>
                <Link :href="cancelHref">{{ t('common.cancel') }}</Link>
            </Button>
            <Button type="submit" size="touch" :class="cancelHref ? '' : 'w-full'" :disabled="form.processing">
                {{ form.processing ? t('common.creating') : t('tallies.create_title') }}
            </Button>
        </div>
    </form>
</template>
