<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed } from 'vue';
import type { PrimaryTally } from '../types';

const props = defineProps<{
    userName: string;
    currentMonthLabel: string;
    primaryTally: PrimaryTally | null;
    addTransactionUrl: string;
}>();

const firstName = computed(() => props.userName.split(/\s+/)[0] ?? props.userName);
</script>

<template>
    <div class="mb-9 flex flex-col gap-4 border-b border-rule pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="eyebrow mb-3">
                <template v-if="primaryTally">{{ primaryTally.name }} · </template>{{ currentMonthLabel }}
            </div>
            <h1 class="m-0 font-serif text-[clamp(36px,5vw,56px)] leading-none tracking-[-0.02em]">
                {{ $t('home.greeting') }}
                <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ firstName }}.</em>
            </h1>
        </div>
        <div class="hidden items-center gap-2 min-[769px]:flex">
            <Button as-child :disabled="!primaryTally">
                <Link :href="addTransactionUrl">
                    <Plus class="size-4" />
                    {{ $t('transactions.add_button') }}
                </Link>
            </Button>
        </div>
    </div>
</template>
