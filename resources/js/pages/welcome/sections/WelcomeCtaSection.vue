<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { CtaBand } from '@/components/ui/cta-band';
import { MarketingContainer } from '@/components/ui/marketing-container';
import { register } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <MarketingContainer v-reveal as="section" class="pb-2">
        <CtaBand :heading="t('welcome.cta_band.heading')" :body="t('welcome.cta_band.body')">
            <template #actions>
                <Button as-child size="pill-lg">
                    <Link :href="register.url()">{{ t('welcome.cta_band.cta') }}</Link>
                </Button>
            </template>
        </CtaBand>
    </MarketingContainer>
</template>
