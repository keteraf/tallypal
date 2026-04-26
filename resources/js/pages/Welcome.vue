<script setup lang="ts">
import WebHeader from '@/components/WebHeader.vue';
import WelcomeFaqSection from '@/pages/welcome/sections/WelcomeFaqSection.vue';
import WelcomeFeaturesSection from '@/pages/welcome/sections/WelcomeFeaturesSection.vue';
import WelcomeFooterSection from '@/pages/welcome/sections/WelcomeFooterSection.vue';
import WelcomeHeroSection from '@/pages/welcome/sections/WelcomeHeroSection.vue';
import WelcomePricingSection from '@/pages/welcome/sections/WelcomePricingSection.vue';
import WelcomeStatsSection from '@/pages/welcome/sections/WelcomeStatsSection.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    auth: {
        user: any;
    };
}>();
</script>

<template>
    <Head :title="$t('welcome.title')" />

    <div class="min-h-screen bg-background text-foreground">
        <WebHeader />
        <main>
            <WelcomeHeroSection />
            <WelcomeStatsSection />
            <WelcomeFeaturesSection />
            <WelcomePricingSection />
            <WelcomeFaqSection />
            <WelcomeFooterSection />
        </main>
    </div>
</template>
