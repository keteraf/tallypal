<script setup lang="ts">
import TallyShareDialog from '@/components/tallies/TallyShareDialog.vue';
import { Button } from '@/components/ui/button';
import WebLayout from '@/layouts/WebLayout.vue';
import tallyRoutes from '@/routes/tallies';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Share2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    tallies: any[];
}>();

const showShareDialog = ref(false);
const selectedTally = ref<{ tally_id: number; uuid: string; name: string; owner_id: number } | null>(null);

const openShareDialog = (tally: any) => {
    selectedTally.value = {
        tally_id: tally.tally_id,
        uuid: tally.uuid,
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

const splitName = (name: string) => {
    if (!name) return { lead: '', em: '' };
    const parts = name.split(/\s+/);
    if (parts.length === 1) return { lead: '', em: parts[0] };
    return { lead: parts.slice(0, -1).join(' '), em: parts[parts.length - 1] };
};

const memberLabel = computed(() => (tally: any) => {
    const count = tally.users_count ?? (Array.isArray(tally.users) ? tally.users.length : 0);
    if (!count) return t('tallies.members_just_you');
    return t('tallies.members_count', { count });
});
</script>

<template>
    <Head :title="$t('tallies.title')" />

    <WebLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-330 px-6 py-10 max-[768px]:pb-28 md:px-8">
            <div class="mb-9 flex flex-col gap-4 border-b border-rule pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="eyebrow mb-3">{{ $t('tallies.crumb') }}</div>
                    <h1 class="m-0 font-serif text-[clamp(36px,5vw,56px)] leading-none tracking-[-0.02em]">
                        {{ $t('tallies.title_lead') }}
                        <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ $t('tallies.title_em') }}</em>
                    </h1>
                    <p class="mt-3 max-w-[52ch] text-[15px] text-muted-foreground">{{ $t('tallies.subtitle') }}</p>
                </div>
                <div class="hidden min-[769px]:block">
                    <Button v-if="tallies.length > 0" as-child>
                        <Link :href="tallyRoutes.create.url()">
                            <Plus class="size-4" />
                            {{ $t('tallies.new_tally') }}
                        </Link>
                    </Button>
                </div>
            </div>

            <div v-if="tallies.length === 0" class="mt-24 flex flex-col items-center gap-7 md:mt-36">
                <div class="text-center">
                    <h2 class="font-serif text-3xl tracking-[-0.015em] text-foreground">{{ $t('tallies.empty_state') }}</h2>
                    <p class="mt-2 text-muted-foreground">{{ $t('tallies.empty_description') }}</p>
                </div>
                <Button size="lg" as-child>
                    <Link :href="tallyRoutes.create.url()">
                        <Plus class="size-4" />
                        {{ $t('tallies.create_new') }}
                    </Link>
                </Button>
            </div>

            <div v-else class="grid grid-cols-1 border-t border-l border-rule-2 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="tally in tallies"
                    :key="tally.tally_id"
                    :href="tallyRoutes.transactions.index.url(tally.uuid)"
                    class="flex min-h-55 flex-col gap-5 border-r border-b border-rule-2 bg-background px-7 py-7 transition-colors hover:bg-paper-2"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-serif text-[26px] leading-[1.1] tracking-[-0.015em] text-foreground">
                                <template v-if="splitName(tally.name).lead">{{ splitName(tally.name).lead }} </template>
                                <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ splitName(tally.name).em }}</em>
                            </div>
                            <p v-if="tally.description" class="mt-1 line-clamp-2 text-[13.5px] wrap-break-word text-muted-foreground">
                                {{ tally.description }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex size-8 shrink-0 items-center justify-center rounded-full border border-rule text-muted-foreground transition-colors hover:border-brand hover:text-brand"
                            :aria-label="$t('common.share')"
                            @click.stop.prevent="openShareDialog(tally)"
                        >
                            <Share2 class="size-3.5" />
                        </button>
                    </div>

                    <div class="flex items-center text-muted-foreground">
                        <span class="font-mono text-[11px] tracking-[0.06em]">{{ memberLabel(tally) }}</span>
                    </div>

                    <div class="mt-auto grid grid-cols-2 gap-4 border-t border-rule-2 pt-4">
                        <div>
                            <div class="eyebrow">{{ $t('tallies.in') }}</div>
                            <div class="tabular mt-1 font-mono text-[17px] text-pos">+ {{ formatCurrency(tally.income_total ?? 0) }}</div>
                        </div>
                        <div>
                            <div class="eyebrow">{{ $t('tallies.out') }}</div>
                            <div class="tabular mt-1 font-mono text-[17px] text-neg">− {{ formatCurrency(tally.expense_total ?? 0) }}</div>
                        </div>
                    </div>
                </Link>

                <Link
                    :href="tallyRoutes.create.url()"
                    class="flex min-h-55 flex-col items-center justify-center gap-3 border border-r border-b border-dashed border-rule-2 px-7 py-7 text-muted-foreground transition-colors hover:border-brand hover:text-brand"
                >
                    <span
                        class="inline-flex size-10 items-center justify-center rounded-full border border-current font-sans text-[28px] leading-none font-light"
                        >+</span
                    >
                    <span class="font-serif text-[22px] italic">{{ $t('tallies.start_new') }}</span>
                </Link>
            </div>
        </div>

        <div
            v-if="tallies.length > 0"
            class="fixed inset-x-0 bottom-0 z-40 border-t border-rule bg-background/95 p-3 backdrop-blur min-[769px]:hidden"
            style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)"
        >
            <Button as-child class="w-full">
                <Link :href="tallyRoutes.create.url()">
                    <Plus class="size-4" />
                    {{ $t('tallies.new_tally') }}
                </Link>
            </Button>
        </div>

        <TallyShareDialog v-model:open="showShareDialog" :tally="selectedTally" />
    </WebLayout>
</template>
