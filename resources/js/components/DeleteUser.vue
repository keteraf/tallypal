<script setup lang="ts">
import DeleteAccountDialog from '@/components/DeleteAccountDialog.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall :title="$t('common.delete') + ' account'" :description="$t('settings.profile.delete_description')" />
        <div class="space-y-4 rounded-sm border border-neg/30 bg-neg/[0.05] px-5 py-4">
            <div class="space-y-1 text-neg">
                <p class="font-mono text-xs tracking-[0.12em] uppercase">{{ $t('common.warning') }}</p>
                <p class="text-sm">{{ $t('settings.profile.delete_warning') }}</p>
            </div>
            <DeleteAccountDialog>
                <Button variant="destructive">{{ $t('common.delete') }} account</Button>
            </DeleteAccountDialog>
        </div>
    </div>
</template>
