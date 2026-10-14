<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const faqItems = computed(() =>
    Array.from({ length: 6 }, (_, i) => ({
        '@type': 'Question',
        name: t(`welcome.faq.items.${i}.q`),
        acceptedAnswer: {
            '@type': 'Answer',
            text: t(`welcome.faq.items.${i}.a`),
        },
    })),
);

const schema = computed(() => ({
    '@context': 'https://schema.org',
    '@graph': [
        {
            '@type': 'SoftwareApplication',
            name: 'Tallypal',
            applicationCategory: 'FinanceApplication',
            operatingSystem: 'Web',
            url: 'https://tallypal.it',
            offers: {
                '@type': 'Offer',
                price: '0',
                priceCurrency: 'EUR',
            },
        },
        {
            '@type': 'FAQPage',
            mainEntity: faqItems.value,
        },
    ],
}));

const schemaJson = computed(() => JSON.stringify(schema.value));
</script>

<template>
    <Head>
        <!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-html="schemaJson" />
    </Head>
</template>
