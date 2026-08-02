<script setup lang="ts">
import NewTallyForm from '@/components/tallies/NewTallyForm.vue';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('tallies.title'),
        href: '/tallies',
    },
    {
        title: t('common.create'),
        href: '/tallies/create',
    },
];
</script>

<template>
    <Head :title="$t('tallies.create_title')" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-2xl px-5 py-6 pb-28 md:px-8 md:py-10">
            <div class="mb-6 border-b border-line pb-6">
                <div class="eyebrow mb-2">{{ $t('tallies.crumb') }}</div>
                <h1 class="text-[28px] font-semibold tracking-[-0.03em] text-text md:text-3xl">{{ $t('tallies.create_heading') }}</h1>
            </div>

            <div class="rounded-card border border-line bg-surface p-6 md:p-7">
                <NewTallyForm cancel-href="/tallies" />
            </div>
        </div>
    </WebLayout>
</template>
