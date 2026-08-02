<script setup lang="ts">
import { MarketingSection, MarketingSectionHead } from '@/components/ui/marketing-section';
import { StepCard } from '@/components/ui/step-card';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const steps = () =>
    Array.from({ length: 3 }, (_, i) => ({
        title: t(`welcome.how.steps.${i}.title`),
        body: t(`welcome.how.steps.${i}.body`),
    }));
</script>

<template>
    <MarketingSection id="how" rule>
        <MarketingSectionHead :kicker="t('welcome.how.kicker')" :heading="t('welcome.how.title')" :lede="t('welcome.how.lede')" />
        <div v-reveal class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-[14px]">
            <StepCard v-for="(step, index) in steps()" :key="index" :number="String(index + 1).padStart(2, '0')" :title="step.title">
                {{ step.body }}
            </StepCard>
        </div>
    </MarketingSection>
</template>
