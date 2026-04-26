<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const stats = [
    { value: '38k', label: t('welcome.stats.tallies') },
    { value: '€2.4M', label: t('welcome.stats.settled') },
    { value: '11', label: t('welcome.stats.currencies') },
    { value: '0', label: t('welcome.stats.awkward') },
];
</script>

<template>
    <section class="border-b border-rule-2">
        <div class="mx-auto w-full max-w-[1320px] px-6 md:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4">
                <div
                    v-for="(stat, index) in stats"
                    :key="index"
                    class="border-r border-rule-2 px-1 py-7 last:border-r-0 md:px-3"
                    :class="{ 'border-r-0 md:border-r': index % 2 === 1 }"
                >
                    <div class="font-serif text-[44px] leading-none tracking-[-0.02em] text-foreground">{{ stat.value }}</div>
                    <div class="eyebrow mt-3">{{ stat.label }}</div>
                </div>
            </div>
        </div>
    </section>
</template>
