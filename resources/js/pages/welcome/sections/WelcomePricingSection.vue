<script setup lang="ts">
import type { CheckListItemData } from '@/components/ui/check-list';
import { MarketingSection, MarketingSectionHead } from '@/components/ui/marketing-section';
import { PricingPlanCard } from '@/components/ui/pricing-plan-card';
import { register } from '@/routes';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Plan {
    name: string;
    tag: string;
    highlighted?: boolean;
    price: string;
    currency: string;
    per: string;
    description: string;
    features: CheckListItemData[];
    ctaLabel: string;
}

const plans = (): Plan[] => [
    {
        name: t('welcome.pricing.tiers.free.name'),
        tag: t('welcome.pricing.tiers.free.tag'),
        price: '0',
        currency: '€',
        per: t('welcome.pricing.tiers.free.per'),
        description: t('welcome.pricing.tiers.free.summary'),
        features: [
            { label: t('welcome.pricing.tiers.free.features.personal_ledger') },
            { label: t('welcome.pricing.tiers.free.features.shared_tally') },
            { label: t('welcome.pricing.tiers.free.features.equal_split') },
            { label: t('welcome.pricing.tiers.free.features.csv_export') },
            { label: t('welcome.pricing.tiers.free.features.recurring'), state: 'off' },
            { label: t('welcome.pricing.tiers.free.features.receipt_scan'), state: 'off' },
            { label: t('welcome.pricing.tiers.free.features.multi_currency'), state: 'off' },
        ],
        ctaLabel: t('welcome.pricing.tiers.free.cta'),
    },
    {
        name: t('welcome.pricing.tiers.pro.name'),
        tag: t('welcome.pricing.tiers.pro.tag'),
        highlighted: true,
        price: '3',
        currency: '€',
        per: t('welcome.pricing.tiers.pro.per'),
        description: t('welcome.pricing.tiers.pro.summary'),
        features: [
            { label: t('welcome.pricing.tiers.pro.features.everything_free') },
            { label: t('welcome.pricing.tiers.pro.features.unlimited_shared') },
            { label: t('welcome.pricing.tiers.pro.features.all_split_types') },
            { label: t('welcome.pricing.tiers.pro.features.recurring_rules') },
            { label: t('welcome.pricing.tiers.pro.features.receipt_scan') },
            { label: t('welcome.pricing.tiers.pro.features.multi_currency') },
            { label: t('welcome.pricing.tiers.pro.features.support') },
        ],
        ctaLabel: t('welcome.pricing.tiers.pro.cta'),
    },
];
</script>

<template>
    <MarketingSection id="pricing" rule>
        <MarketingSectionHead :kicker="t('welcome.pricing.kicker')" :heading="t('welcome.pricing.title')" :lede="t('welcome.pricing.lede')" />

        <div v-reveal class="grid grid-cols-[repeat(auto-fit,minmax(280px,1fr))] items-start gap-4">
            <PricingPlanCard
                v-for="plan in plans()"
                :key="plan.name"
                :name="plan.name"
                :tag="plan.tag"
                :highlighted="plan.highlighted"
                :price="plan.price"
                :currency="plan.currency"
                :per="plan.per"
                :description="plan.description"
                :features="plan.features"
                :cta-label="plan.ctaLabel"
                :cta-href="register.url()"
            />
        </div>
    </MarketingSection>
</template>
