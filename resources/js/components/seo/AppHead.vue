<script setup lang="ts">
import type { AppPageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        canonical?: string;
        image?: string;
        noindex?: boolean;
    }>(),
    {
        title: undefined,
        description: undefined,
        canonical: undefined,
        image: undefined,
        noindex: false,
    },
);

const page = usePage<AppPageProps>();
const seo = computed(() => page.props.seo);

/** The `<title>` is the bare page title: the Inertia `title` callback (`app.ts`, `ssr.ts`) adds the app name, once (FP-16). */
const socialTitle = computed(() => (props.title ? `${props.title} — ${seo.value.site_name}` : seo.value.site_name));

const resolvedDescription = computed(() => props.description ?? seo.value.default_description);

const resolvedImage = computed(() => props.image ?? seo.value.default_og_image);

const resolvedCanonical = computed(() => props.canonical ?? seo.value.url);
</script>

<template>
    <Head :title="title">
        <meta head-key="description" name="description" :content="resolvedDescription" />
        <meta head-key="robots" v-if="noindex" name="robots" content="noindex, follow" />
        <link head-key="canonical" rel="canonical" :href="resolvedCanonical" />

        <!-- Open Graph -->
        <meta head-key="og:title" property="og:title" :content="socialTitle" />
        <meta head-key="og:description" property="og:description" :content="resolvedDescription" />
        <meta head-key="og:url" property="og:url" :content="resolvedCanonical" />
        <meta head-key="og:image" property="og:image" :content="resolvedImage" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:site_name" property="og:site_name" :content="seo.site_name" />
        <meta head-key="og:locale" property="og:locale" content="it_IT" />

        <!-- Twitter / X -->
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" :content="socialTitle" />
        <meta head-key="twitter:description" name="twitter:description" :content="resolvedDescription" />
        <meta head-key="twitter:image" name="twitter:image" :content="resolvedImage" />
    </Head>
</template>
