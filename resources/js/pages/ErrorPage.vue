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

const is404 = computed(() => props.status === 404);
</script>

<template>
    <Head :title="is404 ? t('errors.not_found.eyebrow') : t('errors.generic.eyebrow')" />

    <div class="flex min-h-svh flex-col bg-background px-6 py-16 md:px-8">
        <div class="w-full max-w-xl">
            <div class="mb-16">
                <Link :href="home.url()" class="inline-flex items-center">
                    <AppLogo size="md" />
                </Link>
            </div>

            <div class="eyebrow mb-6 flex items-center gap-3">
                <span class="block h-px w-7 bg-muted-foreground" aria-hidden="true" />
                {{ is404 ? t('errors.not_found.eyebrow') : t('errors.generic.eyebrow') }}
            </div>

            <h1 class="mb-6 font-serif text-[clamp(48px,8vw,96px)] leading-[0.96] tracking-[-0.025em] text-balance">
                {{ is404 ? t('errors.not_found.title_lead') : t('errors.generic.title_lead') }}
                <em style="font-style: italic; color: var(--brand)">{{
                    is404 ? t('errors.not_found.title_em') : t('errors.generic.title_em')
                }}</em>
            </h1>

            <p class="mb-10 max-w-prose text-[18px] leading-[1.55] text-muted-foreground">
                {{ is404 ? t('errors.not_found.description') : t('errors.generic.description') }}
            </p>

            <Button as-child>
                <Link :href="home.url()">{{ t('errors.back_home') }}</Link>
            </Button>
        </div>
    </div>
</template>
