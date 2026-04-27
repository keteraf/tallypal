<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
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
    <footer class="bg-background">
        <div class="mx-auto w-full max-w-[1320px] px-6 py-16 md:px-8">
            <div class="grid gap-12 border-b border-rule-2 pb-14 md:grid-cols-[2fr_1fr_1fr_1fr]">
                <div>
                    <AppLogo size="lg" />
                    <p class="mt-4 max-w-[32ch] text-sm text-muted-foreground">{{ $t('welcome.footer.tagline') }}</p>
                </div>
                <div v-for="(col, index) in footerColumns()" :key="index">
                    <h5 class="m-0 mb-3.5 font-mono text-[11px] font-medium tracking-[0.14em] text-muted-foreground uppercase">{{ col.title }}</h5>
                    <a
                        v-for="link in col.links"
                        :key="link.label"
                        :href="link.href"
                        class="block py-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >{{ link.label }}</a
                    >
                </div>
            </div>
            <div class="flex items-center justify-between pt-6 font-mono text-[11px] tracking-[0.06em] text-muted-foreground">
                <span>{{ $t('welcome.footer.copyright') }}</span>
                <span>tallypal.it</span>
            </div>
        </div>
    </footer>
</template>
