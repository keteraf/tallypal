<script setup lang="ts">
import { SegmentedControl } from '@/components/ui/segmented-control';
import { useAppearance } from '@/composables/useAppearance';
import { useI18n } from 'vue-i18n';

type Appearance = 'light' | 'dark' | 'system';

const { appearance, updateAppearance } = useAppearance();
const { t } = useI18n();

const options: { value: Appearance; label: string }[] = [
    { value: 'light', label: t('settings.appearance.light') },
    { value: 'dark', label: t('settings.appearance.dark') },
    { value: 'system', label: t('settings.appearance.system') },
];
</script>

<template>
    <SegmentedControl variant="mobile" class="max-w-xs" :options="options" :model-value="appearance" @update:model-value="updateAppearance" />
</template>
