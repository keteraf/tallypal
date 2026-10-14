<script setup lang="ts">
import { SectionHeader } from '@/components/ui/section-header';
import { TallyRail, TallyRailCard, type TallyRailCardAmount } from '@/components/ui/tally-rail';
import { useNewTally } from '@/composables/useNewTally';
import { usePrimaryCurrency } from '@/composables/usePrimaryCurrency';
import { tallyColor, tallyGlyph } from '@/lib/category';
import { zeroSpending } from '@/lib/spending';
import talliesRoutes from '@/routes/tallies';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { TallySummary } from '../types';
import { balanceStatus } from '../utils';

const props = defineProps<{
    tallies: TallySummary[];
    /** `YYYY-MM-01` of the month the amounts cover. */
    monthStart: string;
}>();

const { t, locale } = useI18n();

const { open: openNewTally } = useNewTally();
const primaryCurrency = usePrimaryCurrency();

/**
 * One line per currency spent in this month. A quiet month shows 0 in the tally's most-used
 * currency; a tally with no transactions yet shows 0 in the primary currency (FP-14).
 */
const amounts = (tally: TallySummary): TallyRailCardAmount[] => {
    const spent = tally.monthly_totals.filter((total) => total.amount !== 0);
    const lines = spent.length > 0 ? spent : tally.monthly_totals.slice(0, 1);

    return (lines.length > 0 ? lines : [zeroSpending(primaryCurrency.value)]).map((total) => ({
        amount: total.amount,
        currency: total.currency_symbol,
        decimals: total.decimals,
    }));
};

/** "spent in Oct" / "spesi a ott": what the card amount is, in the user's locale (FP-01). */
const amountCaption = computed(() => {
    const month = new Intl.DateTimeFormat(locale.value, { month: 'short', timeZone: 'UTC' })
        .format(new Date(`${props.monthStart}T00:00:00Z`))
        .replace('.', '');

    return t('home.tallies.spent_in', { month });
});

const meta = (tally: TallySummary): string => {
    if (tally.split_label !== null) {
        return t('tallies.split_label', { split: tally.split_label });
    }

    return tally.member_count > 1 ? t('tallies.members_count', { count: tally.member_count }) : t('tallies.members_just_you');
};

const status = (tally: TallySummary) => balanceStatus(tally.my_balance, t);

/**
 * A solo tally has nothing to stack: the avatars only show once somebody else is in. The 22px
 * stack shows one letter per member, as in the design.
 */
const members = (tally: TallySummary) =>
    tally.member_count > 1 ? tally.members_preview.map((member) => ({ initials: Array.from(member.initials)[0], color: member.color })) : [];
</script>

<template>
    <section v-if="tallies.length > 0">
        <SectionHeader :label="t('home.tallies.title')" :action-label="t('home.tallies.see_all')" :action-href="talliesRoutes.index.url()" />

        <!-- Mobile: horizontal rail. Desktop: card grid (same card, per COMPONENT_INVENTORY B7). -->
        <TallyRail bleed class="md:hidden">
            <Link
                v-for="tally in props.tallies"
                :key="tally.uuid"
                :href="talliesRoutes.transactions.index.url(tally.uuid)"
                class="w-[156px] shrink-0 snap-start"
            >
                <TallyRailCard
                    :name="tally.name"
                    :meta="meta(tally)"
                    :members="members(tally)"
                    :amounts="amounts(tally)"
                    :amount-caption="amountCaption"
                    :color="tallyColor(tally)"
                    :glyph="tallyGlyph(tally)"
                />
            </Link>
            <TallyRailCard variant="new" class="w-[156px] shrink-0 snap-start" :label="t('tallies.new_tally')" @click="openNewTally()" />
        </TallyRail>

        <div class="hidden gap-3.5 md:grid md:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]">
            <Link v-for="tally in props.tallies" :key="tally.uuid" :href="talliesRoutes.transactions.index.url(tally.uuid)">
                <TallyRailCard
                    :name="tally.name"
                    :meta="meta(tally)"
                    :members="members(tally)"
                    :amounts="amounts(tally)"
                    :amount-caption="amountCaption"
                    :color="tallyColor(tally)"
                    :glyph="tallyGlyph(tally)"
                    :status="status(tally).label"
                    :status-tone="status(tally).tone"
                    :status-extra="status(tally).otherSymbols"
                    class="h-full w-full"
                />
            </Link>
            <TallyRailCard variant="new" :label="t('tallies.new_tally')" class="w-full" @click="openNewTally()" />
        </div>
    </section>
</template>
