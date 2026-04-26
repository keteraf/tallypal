<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

defineProps<{
    addTransactionUrl: string;
    disabled: boolean;
}>();
</script>

<template>
    <div
        class="fixed inset-x-0 bottom-0 z-40 border-t border-rule bg-background/95 p-3 backdrop-blur min-[769px]:hidden"
        style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)"
    >
        <Button as-child class="w-full" :disabled="disabled">
            <Link :href="addTransactionUrl">
                <Plus class="size-4" />
                {{ $t('transactions.add_button') }}
            </Link>
        </Button>
    </div>
</template>
