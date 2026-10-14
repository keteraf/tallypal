<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
</script>

<template>
    <AppLayout>
        <Head :title="$t('settings.appearance.title')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall :title="$t('settings.appearance.heading')" :description="$t('settings.appearance.description')" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
