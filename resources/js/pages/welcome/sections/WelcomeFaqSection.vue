<script setup lang="ts">
import type { FaqItemData } from '@/components/ui/faq-accordion';
import { FaqAccordion } from '@/components/ui/faq-accordion';
import { MarketingSection, MarketingSectionHead } from '@/components/ui/marketing-section';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const faqs = (): FaqItemData[] =>
    Array.from({ length: 6 }, (_, i) => ({
        question: t(`welcome.faq.items.${i}.q`),
        answer: t(`welcome.faq.items.${i}.a`),
    }));
</script>

<template>
    <MarketingSection id="faq" rule>
        <MarketingSectionHead :kicker="t('welcome.faq.kicker')" :heading="t('welcome.faq.title')" :lede="t('welcome.faq.lede')" />
        <FaqAccordion v-reveal :items="faqs()" />
    </MarketingSection>
</template>
