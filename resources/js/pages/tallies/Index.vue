<script setup lang="ts">
import TallyShareDialog from '@/components/tallies/TallyShareDialog.vue';
import { Button } from '@/components/ui/button';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Share2, TrendingDown, TrendingUp } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    tallies: any[];
}>();

const showShareDialog = ref(false);
const selectedTally = ref<{ tally_id: number; name: string; owner_id: number } | null>(null);

const openShareDialog = (tally: any) => {
    selectedTally.value = {
        tally_id: tally.tally_id,
        name: tally.name,
        owner_id: tally.owner_id,
    };
    showShareDialog.value = true;
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('tallies.title'),
        href: '/tallies',
    },
];
</script>

<template>
    <Head :title="$t('tallies.title')" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-[768px]:pb-24">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-bold">{{ $t('tallies.title') }}</h1>
                <div class="hidden min-[769px]:block">
                    <Button v-if="tallies.length > 0" as-child class="w-full sm:w-auto">
                        <Link :href="route('tallies.create')">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ $t('tallies.new_tally') }}
                        </Link>
                    </Button>
                </div>
            </div>

            <div v-if="tallies.length === 0" class="mt-20 flex flex-1 flex-col items-center gap-6 md:mt-40">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-600">{{ $t('tallies.empty_state') }}</h2>
                    <p class="mt-2 text-lg text-gray-500">{{ $t('tallies.empty_description') }}</p>
                </div>
                <Button size="lg" as-child>
                    <Link :href="route('tallies.create')">
                        <Plus class="mr-2 h-5 w-5" />
                        {{ $t('tallies.create_new') }}
                    </Link>
                </Button>
            </div>
            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="tally in tallies"
                    :key="tally.tally_id"
                    :href="route('tallies.transactions.index', tally.tally_id)"
                    class="block rounded-lg border p-4 shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-900"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h2 class="truncate text-xl font-semibold">{{ tally.name }}</h2>
                            <p class="mt-1 line-clamp-2 text-sm break-words text-gray-600 dark:text-gray-400">{{ tally.description }}</p>
                        </div>
                        <Button variant="ghost" size="icon" class="shrink-0" @click.stop.prevent="openShareDialog(tally)">
                            <Share2 class="h-4 w-4" />
                        </Button>
                    </div>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="flex items-center gap-1 text-sm font-medium text-green-600">
                            <TrendingUp class="h-4 w-4" />
                            <span>{{ formatCurrency(tally.income_total ?? 0) }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-sm font-medium text-red-600">
                            <TrendingDown class="h-4 w-4" />
                            <span>{{ formatCurrency(tally.expense_total ?? 0) }}</span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <div
            v-if="tallies.length > 0"
            class="fixed inset-x-0 bottom-0 z-40 border-t bg-background/95 p-3 backdrop-blur min-[769px]:hidden"
            style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)"
        >
            <Button as-child class="w-full sm:w-auto">
                <Link :href="route('tallies.create')">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ $t('tallies.new_tally') }}
                </Link>
            </Button>
        </div>

        <TallyShareDialog v-model:open="showShareDialog" :tally="selectedTally" />
    </WebLayout>
</template>
