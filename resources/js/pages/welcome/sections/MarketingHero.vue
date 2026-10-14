<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { FloatingStatCard } from '@/components/ui/floating-stat-card';
import { MarketingContainer } from '@/components/ui/marketing-container';
import { MarketingDeviceFrame } from '@/components/ui/marketing-device-frame';
import { StatusBadge } from '@/components/ui/status-badge';
import { register } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <section id="hero" class="relative overflow-hidden pt-hero-top pb-hero-bottom">
        <div
            class="pointer-events-none absolute -top-[280px] left-1/2 h-[620px] w-[1100px] -translate-x-1/2 blur-[20px]"
            style="background-image: var(--glow-hero)"
            aria-hidden="true"
        />

        <MarketingContainer
            class="relative grid gap-10 min-[900px]:grid-cols-[minmax(0,1.05fr)_minmax(0,.95fr)] min-[900px]:items-center min-[900px]:gap-[clamp(32px,5vw,72px)]"
        >
            <div>
                <StatusBadge v-reveal>{{ t('welcome.hero.badge') }}</StatusBadge>

                <h1 v-reveal class="mt-[26px] mb-5 text-hero font-semibold tracking-[-0.045em] text-balance">
                    {{ t('welcome.hero.title_lead') }}
                    <em class="text-accent not-italic">{{ t('welcome.hero.title_em') }}</em>
                </h1>

                <p v-reveal class="mb-[30px] max-w-[50ch] text-lede text-pretty text-text-2">
                    {{ t('welcome.hero.subtitle') }}
                </p>

                <div v-reveal class="flex flex-wrap items-center gap-3">
                    <Button as-child size="pill-lg">
                        <Link :href="register.url()">{{ t('welcome.hero.cta_primary') }}</Link>
                    </Button>
                    <Button as-child variant="surface" size="pill-lg">
                        <a href="#how">{{ t('welcome.hero.cta_secondary') }}</a>
                    </Button>
                    <span class="ml-1 text-badge text-text-3">{{ t('welcome.hero.cta_note') }}</span>
                </div>
            </div>

            <MarketingDeviceFrame v-reveal src="/marketing/home-light.png" src-dark="/marketing/home-dark.png" :label="t('navigation.home')">
                <FloatingStatCard position="left" tone="pos" :label="t('welcome.hero.floats.owed')" value="+ € 76.30" />
                <FloatingStatCard position="right" :label="t('welcome.hero.floats.split')" value="€ 540.00" />
            </MarketingDeviceFrame>
        </MarketingContainer>
    </section>
</template>
