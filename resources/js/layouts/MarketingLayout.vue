<script setup lang="ts">
import MarketingFooter from '@/components/MarketingFooter.vue';
import MarketingHeader from '@/components/MarketingHeader.vue';

/**
 * Standalone shell for the marketing site: sticky header + footer, no bottom
 * nav, no sidebar (source `Tallypal Site.html` — a separate visual context
 * from `WebLayout`, see MIGRATION_PLAN.md Batch 4.3 / #M1).
 */
</script>

<template>
    <div class="min-h-screen bg-bg text-text">
        <MarketingHeader />
        <main>
            <slot />
        </main>
        <MarketingFooter />
    </div>
</template>
