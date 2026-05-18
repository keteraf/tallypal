<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { register } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface LedgerEntry {
    initial: string;
    initialClass: string;
    label: string;
    amount: string;
    delta: string;
    deltaClass: string;
}

const ledgerEntries: LedgerEntry[] = [
    {
        initial: 'M',
        initialClass: 'bg-brand-soft text-brand',
        label: t('welcome.hero.ledger.entries.0.label'),
        amount: '€ 480.00',
        delta: '−€ 160',
        deltaClass: 'text-neg',
    },
    {
        initial: 'L',
        initialClass: 'bg-[oklch(0.94_0.04_200)] text-[oklch(0.45_0.12_220)]',
        label: t('welcome.hero.ledger.entries.1.label'),
        amount: '€ 62.40',
        delta: '+€ 41.60',
        deltaClass: 'text-pos',
    },
    {
        initial: 'S',
        initialClass: 'bg-[oklch(0.94_0.04_130)] text-[oklch(0.4_0.11_145)]',
        label: t('welcome.hero.ledger.entries.2.label'),
        amount: '€ 18.00',
        delta: '+€ 12.00',
        deltaClass: 'text-pos',
    },
    {
        initial: 'M',
        initialClass: 'bg-brand-soft text-brand',
        label: t('welcome.hero.ledger.entries.3.label'),
        amount: '€ 124.50',
        delta: '−€ 41.50',
        deltaClass: 'text-neg',
    },
];
</script>

<template>
    <section id="hero" class="border-b border-rule-2">
        <div class="mx-auto w-full max-w-330 px-6 py-16 md:px-8 md:py-24">
            <div class="grid gap-12 md:grid-cols-[1.15fr_1fr] md:items-end md:gap-16">
                <div>
                    <div class="eyebrow mb-9 flex items-center gap-3">
                        <span class="block h-px w-7 bg-muted-foreground" aria-hidden="true" />
                        {{ $t('welcome.hero.eyebrow') }}
                    </div>
                    <h1 class="m-0 mb-8 font-serif text-[clamp(56px,8vw,116px)] leading-[0.96] tracking-[-0.025em] text-balance">
                        {{ $t('welcome.hero.title_lead') }}
                        <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ $t('welcome.hero.title_em') }}</em>
                    </h1>
                    <p class="m-0 mb-9 max-w-120 text-[18px] leading-[1.55] text-muted-foreground">
                        {{ $t('welcome.hero.subtitle') }}
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <Button as-child>
                            <Link :href="register.url()">
                                {{ $t('welcome.hero.cta_primary') }}
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                        <span class="eyebrow">{{ $t('welcome.hero.cta_note') }}</span>
                    </div>
                </div>

                <aside class="rounded-md border border-rule bg-paper-2 px-7 pt-7 pb-6 shadow-[0_1px_0_oklch(0_0_0/0.02)]" aria-hidden="true">
                    <div class="mb-4 flex items-baseline justify-between border-b border-rule-2 pb-4">
                        <div class="font-serif text-xl text-foreground italic">{{ $t('welcome.hero.ledger.title') }}</div>
                        <div class="font-mono text-[11px] tracking-[0.06em] text-muted-foreground">{{ $t('welcome.hero.ledger.range') }}</div>
                    </div>
                    <div
                        v-for="(row, index) in ledgerEntries"
                        :key="index"
                        class="grid grid-cols-[auto_1fr_auto_auto] items-center gap-3.5 border-b border-dashed border-rule-2 py-2.5 text-sm last:border-b-0"
                    >
                        <span
                            class="inline-flex size-6.5 items-center justify-center rounded-full border font-mono text-[11px] font-medium"
                            :class="row.initialClass"
                            style="border-color: color-mix(in oklch, currentColor 35%, transparent)"
                            >{{ row.initial }}</span
                        >
                        <span class="truncate text-muted-foreground">{{ row.label }}</span>
                        <span class="tabular font-mono text-sm">{{ row.amount }}</span>
                        <span class="tabular font-mono text-sm" :class="row.deltaClass">{{ row.delta }}</span>
                    </div>
                    <div class="mt-4 flex items-baseline justify-between border-t border-rule pt-4">
                        <span class="eyebrow">{{ $t('welcome.hero.ledger.foot_label') }}</span>
                        <span class="font-serif text-[32px] tracking-[-0.01em]">€ 147.90</span>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</template>
