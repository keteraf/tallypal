<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { Plus, Search } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import type { PrimaryTally } from '../types';
import { greetingPeriod } from '../utils';

const props = defineProps<{
    userName: string;
    currentMonthLabel: string;
    primaryTally: PrimaryTally | null;
    addTransactionUrl: string;
}>();

const firstName = computed(() => props.userName.split(/\s+/)[0] ?? props.userName);

/**
 * The viewer's local hour, read in the browser after mount (FP-15): the server knows no time zone, and
 * rendering it on the server would mismatch on hydration. Until then the greeting is the neutral one.
 */
const hour = ref<number | null>(null);

onMounted(() => {
    hour.value = new Date().getHours();
});

const greetingKey = computed(() => `home.greeting.${hour.value === null ? 'default' : greetingPeriod(hour.value)}`);
</script>

<template>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <h1 class="text-[24px] font-semibold tracking-[-0.03em] text-text sm:text-[26px]">{{ $t(greetingKey) }} {{ firstName }}.</h1>
            <p class="mt-[3px] text-[13px] text-text-3">
                <template v-if="primaryTally">{{ primaryTally.name }} · </template>{{ currentMonthLabel }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <Tooltip>
                <TooltipTrigger as-child>
                    <label
                        class="hidden h-10 w-[min(260px,22vw)] cursor-not-allowed items-center gap-[9px] rounded-full border border-line bg-surface px-3.5 text-[13.5px] text-text-3 min-[1001px]:flex"
                    >
                        <Search class="size-[15px]" />
                        <input
                            type="text"
                            disabled
                            :placeholder="$t('home.search.placeholder')"
                            :aria-label="$t('home.search.placeholder')"
                            class="min-w-0 flex-1 cursor-not-allowed border-0 bg-transparent text-[13.5px] outline-none placeholder:text-text-3"
                        />
                        <kbd class="rounded-[5px] border border-line px-[5px] py-px font-mono text-[10.5px]">⌘K</kbd>
                    </label>
                </TooltipTrigger>
                <TooltipContent>{{ $t('home.search.coming_soon') }}</TooltipContent>
            </Tooltip>
            <Button
                as-child
                size="touch"
                :disabled="!primaryTally"
                class="size-11 justify-center rounded-full p-0 sm:h-12 sm:w-auto sm:rounded-full sm:px-6"
            >
                <Link :href="addTransactionUrl">
                    <Plus class="size-[18px] sm:size-4" />
                    <span class="hidden sm:inline">{{ $t('transactions.add_button') }}</span>
                </Link>
            </Button>
        </div>
    </div>
</template>
