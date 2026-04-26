<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Feature {
    num: string;
    titleLead: string;
    titleEm: string;
    titleTail?: string;
    description: string;
    glossLead?: string;
    glossTail?: string;
    glossBars?: number[];
}

const features = (): Feature[] => [
    {
        num: '001',
        titleLead: t('welcome.features.items.0.title_lead'),
        titleEm: t('welcome.features.items.0.title_em'),
        titleTail: t('welcome.features.items.0.title_tail'),
        description: t('welcome.features.items.0.description'),
        glossLead: 'personal · household · trip · freelance',
    },
    {
        num: '002',
        titleLead: t('welcome.features.items.1.title_lead'),
        titleEm: t('welcome.features.items.1.title_em'),
        description: t('welcome.features.items.1.description'),
        glossBars: [80, 50, 65, 90, 30, 60],
        glossTail: '60 / 40 lock',
    },
    {
        num: '003',
        titleLead: t('welcome.features.items.2.title_lead'),
        titleEm: t('welcome.features.items.2.title_em'),
        description: t('welcome.features.items.2.description'),
        glossLead: '→ Marta pays Luca · €68.20',
    },
    {
        num: '004',
        titleLead: t('welcome.features.items.3.title_lead'),
        titleEm: t('welcome.features.items.3.title_em'),
        description: t('welcome.features.items.3.description'),
        glossLead: 'netflix · spotify · gym · salary · 1st mo.',
    },
    {
        num: '005',
        titleLead: t('welcome.features.items.4.title_lead'),
        titleEm: t('welcome.features.items.4.title_em'),
        description: t('welcome.features.items.4.description'),
        glossLead: '↓ scan · ↻ confirm · ✓ saved',
    },
    {
        num: '006',
        titleLead: t('welcome.features.items.5.title_lead'),
        titleEm: t('welcome.features.items.5.title_em'),
        description: t('welcome.features.items.5.description'),
        glossLead: '.csv · .pdf · e2e · sepa',
    },
];
</script>

<template>
    <section id="features" class="border-b border-rule-2">
        <div class="mx-auto w-full max-w-[1320px] px-6 py-20 md:px-8 md:py-24">
            <div class="mb-16 grid gap-10 md:grid-cols-[1fr_1.2fr] md:items-end md:gap-16">
                <div>
                    <div class="eyebrow">{{ $t('welcome.features.eyebrow') }}</div>
                    <h2 class="m-0 mt-4 font-serif text-[clamp(40px,5vw,68px)] leading-none tracking-[-0.02em] text-balance">
                        {{ $t('welcome.features.title_lead') }}
                        <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ $t('welcome.features.title_em') }}</em>
                    </h2>
                </div>
                <p class="m-0 max-w-[460px] text-[17px] text-muted-foreground">{{ $t('welcome.features.lede') }}</p>
            </div>

            <div class="grid grid-cols-2 border-t border-rule lg:grid-cols-3">
                <article
                    v-for="(feature, index) in features()"
                    :key="feature.num"
                    class="border-r border-b border-rule-2 px-2 py-8 lg:px-7"
                    :class="{
                        'border-r-0': (index + 1) % 2 === 0,
                        'lg:border-r': (index + 1) % 3 !== 0,
                        'lg:border-r-0': (index + 1) % 3 === 0,
                    }"
                >
                    <span class="font-mono text-[11px] tracking-[0.1em] text-muted-foreground">{{ feature.num }}</span>
                    <h3 class="mt-5 mb-2.5 font-serif text-[26px] leading-[1.15] tracking-[-0.01em]">
                        {{ feature.titleLead }}
                        <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ feature.titleEm }}</em>
                        <template v-if="feature.titleTail">{{ feature.titleTail }}</template>
                    </h3>
                    <p class="m-0 max-w-[32ch] text-[14.5px] text-muted-foreground">{{ feature.description }}</p>
                    <div
                        class="relative mt-5 flex h-16 items-center overflow-hidden rounded-sm border border-rule-2 bg-paper-2 px-3.5 font-mono text-[11px] tracking-[0.04em] text-muted-foreground"
                    >
                        <span v-if="feature.glossLead" class="mr-auto truncate">{{ feature.glossLead }}</span>
                        <div v-if="feature.glossBars" class="flex h-7 items-end gap-1">
                            <span
                                v-for="(h, i) in feature.glossBars"
                                :key="i"
                                class="w-1.5 rounded-sm"
                                :class="i === 0 || i === 3 ? 'bg-brand opacity-90' : 'bg-muted-foreground/30'"
                                :style="{ height: `${h}%` }"
                            />
                        </div>
                        <span v-if="feature.glossTail" class="ml-auto truncate">{{ feature.glossTail }}</span>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
