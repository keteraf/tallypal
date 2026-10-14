<script setup lang="ts">
import { EmptyState } from '@/components/ui/empty-state';
import tallies from '@/routes/tallies';
</script>

<template>
    <EmptyState
        :title="$t('home.empty_title')"
        :description="$t('home.empty_description')"
        :action-label="$t('tallies.create_new')"
        :action-href="tallies.create.url()"
    />
</template>
