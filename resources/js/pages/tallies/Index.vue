<script setup lang="ts">
import { Button } from '@/components/ui/button';
import TallyShareDialog from '@/components/tallies/TallyShareDialog.vue';
import WebLayout from '@/layouts/WebLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Share2, TrendingDown, TrendingUp } from 'lucide-vue-next';
import { ref } from 'vue';

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
        title: 'Tallies',
        href: '/tallies',
    },
];
</script>

<template>
    <Head title="Tallies" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h1 class="text-2xl font-bold">Tallies</h1>
                <Button v-if="tallies.length > 0" as-child class="w-full sm:w-auto">
                    <Link :href="route('tallies.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        New Tally
                    </Link>
                </Button>
            </div>

            <div v-if="tallies.length === 0" class="flex flex-1 flex-col items-center md:mt-40 mt-20 gap-6">
                <div class="text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-600">You don't have any tallies yet.</h2>
                    <p class="mt-2 text-lg text-gray-500">Get started by creating your first tally to keep track of things.</p>
                </div>
                <Button size="lg" as-child>
                    <Link :href="route('tallies.create')">
                        <Plus class="mr-2 h-5 w-5" />
                        Create new tally
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
                            <h2 class="text-xl font-semibold truncate">{{ tally.name }}</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2 break-words">{{ tally.description }}</p>
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

        <TallyShareDialog v-model:open="showShareDialog" :tally="selectedTally" />
    </WebLayout>
</template>
