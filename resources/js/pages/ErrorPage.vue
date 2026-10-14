<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    status: number;
}>();

const copyKeys: Record<number, string> = { 403: 'forbidden', 404: 'not_found', 503: 'unavailable' };
const copyKey = computed(() => `errors.${copyKeys[props.status] ?? 'generic'}`);
</script>

<template>
    <Head :title="t(`${copyKey}.eyebrow`)" />

    <div class="flex min-h-svh flex-col bg-background px-6 py-16 md:px-8">
        <div class="w-full max-w-xl">
            <div class="mb-16">
                <Link :href="home.url()" class="inline-flex items-center">
                    <AppLogo size="md" />
                </Link>
            </div>

            <div class="eyebrow mb-6 flex items-center gap-3">
                <span class="block h-px w-7 bg-muted-foreground" aria-hidden="true" />
                {{ t(`${copyKey}.eyebrow`) }}
            </div>

            <h1 class="mb-6 text-hero font-semibold tracking-[-0.045em] text-balance">
                {{ t(`${copyKey}.title`) }}
            </h1>

            <p class="mb-10 max-w-[50ch] text-lede text-pretty text-muted-foreground">
                {{ t(`${copyKey}.description`) }}
            </p>

            <Button as-child>
                <Link :href="home.url()">{{ t('errors.back_home') }}</Link>
            </Button>
        </div>
    </div>
</template>
