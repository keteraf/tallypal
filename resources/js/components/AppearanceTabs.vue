<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { appearance, updateAppearance } = useAppearance();
const { t } = useI18n();

const tabs = [
    { value: 'light', Icon: Sun, label: t('settings.appearance.light') },
    { value: 'dark', Icon: Moon, label: t('settings.appearance.dark') },
    { value: 'system', Icon: Monitor, label: t('settings.appearance.system') },
] as const;
</script>

<template>
    <div class="inline-flex rounded-full border border-rule p-[3px] font-mono text-[11px] tracking-[0.06em] uppercase">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            type="button"
            @click="updateAppearance(value)"
            :class="[
                'inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition-all',
                appearance === value ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground',
            ]"
        >
            <component :is="Icon" class="size-3.5" />
            <span>{{ label }}</span>
        </button>
    </div>
</template>
