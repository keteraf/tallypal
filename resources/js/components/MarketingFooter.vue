<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { MarketingContainer } from '@/components/ui/marketing-container';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface FooterCol {
    title: string;
    links: { label: string; href: string }[];
}

const footerColumns = (): FooterCol[] => [
    {
        title: t('welcome.footer.product'),
        links: [
            { label: t('welcome.footer.links.features'), href: '#features' },
            { label: t('welcome.footer.links.pricing'), href: '#pricing' },
            { label: t('welcome.footer.links.changelog'), href: '#' },
            { label: t('welcome.footer.links.roadmap'), href: '#' },
        ],
    },
    {
        title: t('welcome.footer.company'),
        links: [
            { label: t('welcome.footer.links.about'), href: '#' },
            { label: t('welcome.footer.links.press'), href: '#' },
            { label: 'hello@tallypal.it', href: 'mailto:hello@tallypal.it' },
        ],
    },
    {
        title: t('welcome.footer.legal'),
        links: [
            { label: t('welcome.footer.links.privacy'), href: '#' },
            { label: t('welcome.footer.links.terms'), href: '#' },
            { label: t('welcome.footer.links.cookies'), href: '#' },
        ],
    },
];
</script>

<template>
    <footer class="mt-hero-bottom border-t border-line pt-[52px] pb-[40px]">
        <MarketingContainer>
            <div class="grid grid-cols-2 gap-8 border-b border-line pb-9 min-[760px]:grid-cols-[1.6fr_repeat(3,1fr)]">
                <div>
                    <AppLogo size="lg" />
                    <p class="mt-3.5 max-w-[32ch] text-[13.5px] text-text-3">{{ $t('welcome.footer.tagline') }}</p>
                </div>
                <div v-for="(col, index) in footerColumns()" :key="index">
                    <h5 class="eyebrow mb-3">{{ col.title }}</h5>
                    <a v-for="link in col.links" :key="link.label" :href="link.href" class="block py-1 text-nav-lnk text-text-2 hover:text-text">
                        {{ link.label }}
                    </a>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 pt-[22px] font-mono text-[11.5px] tracking-[0.04em] text-text-3">
                <span>{{ $t('welcome.footer.copyright') }}</span>
                <span>tallypal.it</span>
            </div>
        </MarketingContainer>
    </footer>
</template>
