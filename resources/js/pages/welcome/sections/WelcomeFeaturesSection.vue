<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { CheckList } from '@/components/ui/check-list';
import { ChipGroup } from '@/components/ui/choice-chip';
import { Dot } from '@/components/ui/dot';
import { FeatureBento, FeatureTile } from '@/components/ui/feature-bento';
import { MarketingSection, MarketingSectionHead } from '@/components/ui/marketing-section';
import { SettleBanner } from '@/components/ui/settle-banner';
import { SplitMemberRow } from '@/components/ui/split-member-row';
import { VisAxis, VisGroupedBar, VisXYContainer } from '@unovis/vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const spendingTrend = [38, 52, 44, 66, 58, 78, 94, 62, 50, 70, 46, 34].map((amount, index) => ({ index, amount }));
const trendXAccessor = (d: { index: number }) => d.index;
const trendYAccessor = (d: { amount: number }) => d.amount;
const trendColorAccessor = (d: { index: number }) => (d.index === 6 ? 'var(--accent)' : 'var(--surface-3)');
const quarterLabels: Record<number, string> = { 0: 'JAN', 3: 'APR', 6: 'JUL', 9: 'OCT' };
const formatQuarterTick = (value: number) => quarterLabels[value] ?? '';

const categories = () => [
    { label: t('welcome.features.tiles.ledgers.categories.housing'), color: 'c1' as const, amount: '€ 540' },
    { label: t('welcome.features.tiles.ledgers.categories.groceries'), color: 'c4' as const, amount: '€ 312' },
    { label: t('welcome.features.tiles.ledgers.categories.eating_out'), color: 'c6' as const, amount: '€ 186' },
];

const currencies = [
    { value: 'EUR', label: 'EUR €' },
    { value: 'USD', label: 'USD $' },
    { value: 'GBP', label: 'GBP £' },
    { value: 'CHF', label: 'CHF' },
    { value: 'CAD', label: 'CAD $' },
];

const privacyItems = () => [
    { label: t('welcome.features.tiles.privacy.items.members_only') },
    { label: t('welcome.features.tiles.privacy.items.self_delete') },
];
</script>

<template>
    <MarketingSection id="features" rule>
        <MarketingSectionHead :kicker="t('welcome.features.kicker')" :heading="t('welcome.features.title')" :lede="t('welcome.features.lede')" />

        <FeatureBento v-reveal>
            <FeatureTile :title="t('welcome.features.tiles.splits.title')" glyph="%" color="c1">
                {{ t('welcome.features.tiles.splits.description') }}
                <template #demo>
                    <SplitMemberRow name="Marta" initials="M" :amount="324" :percentage="60" color="c1" />
                    <SplitMemberRow name="Luca" initials="L" :amount="216" :percentage="40" color="c2" />
                    <SettleBanner direction="owes-you" name="Luca" :amount="76.3">
                        <Button size="sm">{{ t('welcome.features.tiles.splits.cta') }}</Button>
                    </SettleBanner>
                </template>
            </FeatureTile>

            <FeatureTile :title="t('welcome.features.tiles.ledgers.title')" glyph="◐" color="c3">
                {{ t('welcome.features.tiles.ledgers.description') }}
                <template #demo>
                    <VisXYContainer :data="spendingTrend" :height="92" :margin="{ left: 0, right: 0, top: 0, bottom: 20 }" :y-domain="[0, undefined]">
                        <VisGroupedBar :x="trendXAccessor" :y="trendYAccessor" :color="trendColorAccessor" :rounded-corners="2" :bar-padding="0.25" />
                        <VisAxis
                            type="x"
                            :x="trendXAccessor"
                            :tick-format="formatQuarterTick"
                            :tick-line="false"
                            :domain-line="false"
                            :grid-line="false"
                            :num-ticks="4"
                        />
                    </VisXYContainer>
                    <div class="mt-3 flex flex-col">
                        <div
                            v-for="category in categories()"
                            :key="category.label"
                            class="flex items-center gap-2.5 border-t border-line py-2 text-[13px] first:border-t-0 first:pt-0"
                        >
                            <Dot :color="category.color" />
                            <span class="flex-1">{{ category.label }}</span>
                            <span class="num text-[12.5px] font-semibold">{{ category.amount }}</span>
                        </div>
                    </div>
                </template>
            </FeatureTile>

            <FeatureTile :title="t('welcome.features.tiles.currency.title')" glyph="⇄" color="c2">
                {{ t('welcome.features.tiles.currency.description') }}
                <template #demo>
                    <ChipGroup model-value="EUR" :options="currencies" />
                </template>
            </FeatureTile>

            <FeatureTile :title="t('welcome.features.tiles.privacy.title')" glyph="◇" color="c4">
                {{ t('welcome.features.tiles.privacy.description') }}
                <template #demo>
                    <CheckList :items="privacyItems()" tone="pos" />
                </template>
            </FeatureTile>
        </FeatureBento>
    </MarketingSection>
</template>
