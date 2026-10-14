<script setup lang="ts">
import AppHead from '@/components/seo/AppHead.vue';
import JsonLd from '@/components/seo/JsonLd.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import MarketingHero from '@/pages/welcome/sections/MarketingHero.vue';
import WelcomeCtaSection from '@/pages/welcome/sections/WelcomeCtaSection.vue';
import WelcomeFaqSection from '@/pages/welcome/sections/WelcomeFaqSection.vue';
import WelcomeFeaturesSection from '@/pages/welcome/sections/WelcomeFeaturesSection.vue';
import WelcomeHowItWorksSection from '@/pages/welcome/sections/WelcomeHowItWorksSection.vue';
import WelcomePricingSection from '@/pages/welcome/sections/WelcomePricingSection.vue';

defineProps<{
    auth: {
        user: any;
    };
}>();
</script>

<template>
    <AppHead />
    <JsonLd />

    <MarketingLayout>
        <MarketingHero />
        <WelcomeHowItWorksSection />
        <WelcomeFeaturesSection />
        <WelcomePricingSection />
        <WelcomeFaqSection />
        <WelcomeCtaSection />
    </MarketingLayout>
</template>
