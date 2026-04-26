<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Plan {
    name: string;
    tagLabel: string;
    tagBrand?: boolean;
    price: string;
    perLabel: string;
    description: string;
    features: { label: string; muted?: boolean }[];
    ctaLabel: string;
    featured?: boolean;
}

const plans = (): Plan[] => [
    {
        name: t('welcome.pricing.tiers.free.name'),
        tagLabel: t('welcome.pricing.tiers.free.tag'),
        price: '0',
        perLabel: t('welcome.pricing.tiers.free.per'),
        description: t('welcome.pricing.tiers.free.summary'),
        features: [
            { label: t('welcome.pricing.tiers.free.features.personal_ledger') },
            { label: t('welcome.pricing.tiers.free.features.shared_tally') },
            { label: t('welcome.pricing.tiers.free.features.equal_split') },
            { label: t('welcome.pricing.tiers.free.features.csv_export') },
            { label: t('welcome.pricing.tiers.free.features.recurring'), muted: true },
            { label: t('welcome.pricing.tiers.free.features.receipt_scan'), muted: true },
            { label: t('welcome.pricing.tiers.free.features.multi_currency'), muted: true },
        ],
        ctaLabel: t('welcome.pricing.tiers.free.cta'),
    },
    {
        name: t('welcome.pricing.tiers.pro.name'),
        tagLabel: t('welcome.pricing.tiers.pro.tag'),
        tagBrand: true,
        price: '3',
        perLabel: t('welcome.pricing.tiers.pro.per'),
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
        featured: true,
    },
];
</script>

<template>
    <section id="pricing" class="border-b border-rule-2">
        <div class="mx-auto w-full max-w-330 px-6 py-20 md:px-8 md:py-24">
            <div class="mb-16 grid gap-10 md:grid-cols-[1fr_1.2fr] md:items-end md:gap-16">
                <div>
                    <div class="eyebrow">{{ $t('welcome.pricing.eyebrow') }}</div>
                    <h2 class="m-0 mt-4 font-serif text-[clamp(40px,5vw,68px)] leading-none tracking-[-0.02em] text-balance">
                        {{ $t('welcome.pricing.title_lead') }}
                        <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ $t('welcome.pricing.title_em') }}</em>
                    </h2>
                </div>
                <p class="m-0 max-w-115 text-[17px] text-muted-foreground">{{ $t('welcome.pricing.lede') }}</p>
            </div>

            <div class="grid overflow-hidden rounded-sm border border-rule md:grid-cols-2">
                <article
                    v-for="plan in plans()"
                    :key="plan.name"
                    class="relative border-r border-rule-2 px-9 py-10 last:border-r-0 max-md:border-r-0 max-md:border-b max-md:border-rule-2 max-md:last:border-b-0"
                    :class="{ 'bg-paper-2': plan.featured }"
                >
                    <div class="mb-5 flex items-baseline justify-between">
                        <div class="font-serif text-2xl text-foreground italic">{{ plan.name }}</div>
                        <div
                            class="rounded-full border px-2 py-0.75 font-mono text-[10px] tracking-[0.14em] uppercase"
                            :class="plan.tagBrand ? 'border-brand text-brand' : 'border-rule text-muted-foreground'"
                        >
                            {{ plan.tagLabel }}
                        </div>
                    </div>
                    <div class="m-0 mb-1 font-serif text-[64px] leading-none tracking-[-0.025em]">
                        <span class="mr-1 align-super text-[32px] text-muted-foreground">€</span>{{ plan.price
                        }}<span class="ml-1.5 font-sans text-sm text-muted-foreground">{{ plan.perLabel }}</span>
                    </div>
                    <p class="m-0 mb-7 max-w-[36ch] text-[14.5px] text-muted-foreground">{{ plan.description }}</p>
                    <ul class="m-0 mb-8 list-none border-t border-rule-2 p-0">
                        <li
                            v-for="(feature, i) in plan.features"
                            :key="i"
                            class="flex items-baseline gap-3 border-b border-dashed border-rule-2 py-3 text-[14.5px]"
                            :class="{ 'text-muted-foreground line-through': feature.muted }"
                        >
                            <span :class="feature.muted ? 'text-muted-foreground' : 'text-brand'" class="-translate-y-0.5 text-[22px] leading-0"
                                >·</span
                            >
                            <span>{{ feature.label }}</span>
                        </li>
                    </ul>
                    <Button :variant="plan.featured ? 'default' : 'ghost'" as-child>
                        <Link :href="route('register')">{{ plan.ctaLabel }} →</Link>
                    </Button>
                </article>
            </div>
        </div>
    </section>
</template>
