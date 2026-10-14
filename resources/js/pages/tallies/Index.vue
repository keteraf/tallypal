<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuRadioGroup, DropdownMenuRadioItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { EmptyState } from '@/components/ui/empty-state';
import { SegmentedControl } from '@/components/ui/segmented-control';
import { TallyListRow, type TallyListRowAmount } from '@/components/ui/tally-list-row';
import { usePrimaryCurrency } from '@/composables/usePrimaryCurrency';
import WebLayout from '@/layouts/WebLayout.vue';
import { type CategoryColor, tallyColor, tallyGlyph } from '@/lib/category';
import { zeroSpending } from '@/lib/spending';
import type { MyBalance } from '@/pages/home/types';
import { balanceStatus } from '@/pages/home/utils';
import tallyRoutes from '@/routes/tallies';
import { Head, Link } from '@inertiajs/vue3';
import { ListFilter, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface TallyTotal {
    income: number;
    expense: number;
    currency_symbol: string;
    currency_code: string;
    decimals: number;
}

interface TallyIndexItem {
    tally_id: number;
    uuid: string;
    name: string;
    color: string | null;
    glyph: string | null;
    users_count: number;
    is_shared: boolean;
    transactions_count: number;
    last_activity_at: string;
    members_preview: { initials: string; color: CategoryColor }[];
    split_label: string | null;
    totals: TallyTotal[];
    /** The user's balance per currency, most-used first (B-04). */
    my_balance: MyBalance[];
}

const props = defineProps<{
    tallies: TallyIndexItem[];
    status?: string | null;
}>();

type TallyFilter = 'all' | 'shared' | 'private';
type TallySort = 'recent' | 'name';

const filter = ref<TallyFilter>('all');
const sort = ref<TallySort>('recent');

const filterOptions = computed(() => [
    { value: 'all' as const, label: t('tallies.filter_all') },
    { value: 'shared' as const, label: t('tallies.filter_shared') },
    { value: 'private' as const, label: t('tallies.filter_private') },
]);

const sortOptions = computed(() => [
    { value: 'recent' as const, label: t('tallies.sort_recent') },
    { value: 'name' as const, label: t('tallies.sort_name') },
]);

const sortLabel = computed(() => t('tallies.sort_label', { sort: sortOptions.value.find((option) => option.value === sort.value)?.label }));

const sharedCount = computed(() => props.tallies.filter((tally) => tally.is_shared).length);

/** Filtering and sorting stay client-side on `is_shared`, `last_activity_at` and `name`; there is no amount sort (O-04). */
const visibleTallies = computed(() => {
    const filtered = props.tallies.filter((tally) => filter.value === 'all' || tally.is_shared === (filter.value === 'shared'));

    return filtered.toSorted((a, b) =>
        sort.value === 'name' ? a.name.localeCompare(b.name) : Date.parse(b.last_activity_at) - Date.parse(a.last_activity_at),
    );
});

const memberLabel = (tally: TallyIndexItem): string => {
    if (tally.split_label !== null) return t('tallies.split_label', { split: tally.split_label });
    return tally.users_count > 1 ? t('tallies.members_count', { count: tally.users_count }) : t('tallies.members_just_you');
};

/** A solo tally has nothing to stack; the 22px stack shows one letter per member. */
const members = (tally: TallyIndexItem) =>
    tally.is_shared ? tally.members_preview.map((member) => ({ initials: Array.from(member.initials)[0], color: member.color })) : [];

const primaryCurrency = usePrimaryCurrency();

/**
 * One line per currency, most-used first: all-time spending, never summed (O-04, FP-01). A tally
 * with no transactions yet shows 0 in the primary currency (FP-14).
 */
const amounts = (tally: TallyIndexItem): TallyListRowAmount[] => {
    const lines = tally.totals.map((total) => ({ amount: total.expense, currency: total.currency_symbol, decimals: total.decimals }));

    if (lines.length > 0) {
        return lines;
    }

    const zero = zeroSpending(primaryCurrency.value);

    return [{ amount: zero.amount, currency: zero.currency_symbol, decimals: zero.decimals }];
};

const balanceLine = (tally: TallyIndexItem) => balanceStatus(tally.my_balance, t);

const typeLabel = (tally: TallyIndexItem): string =>
    tally.is_shared && tally.split_label !== null
        ? t('tallies.type_shared_split', { split: tally.split_label.replace(/ \/ /g, '/') })
        : t(tally.is_shared ? 'tallies.type_shared' : 'tallies.type_private');
</script>

<template>
    <Head :title="$t('tallies.title')" />

    <WebLayout>
        <div class="mx-auto w-full max-w-330 px-5 py-6 pb-28 md:px-8 md:py-8">
            <div v-if="status === 'tally-deleted'" role="status" class="mb-4 text-sm font-medium text-pos">
                {{ $t('tallies.deleted') }}
            </div>
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <h1 class="text-[24px] font-semibold tracking-[-0.03em] text-text sm:text-[26px]">{{ $t('tallies.title') }}</h1>
                    <p class="mt-[3px] text-[13px] text-text-3">
                        {{ $t('tallies.ledgers', { count: tallies.length }, tallies.length) }} ·
                        {{ $t('tallies.shared_count', { count: sharedCount }, sharedCount) }}
                    </p>
                </div>
                <div v-if="tallies.length > 0">
                    <Button as-child size="touch" class="size-11 justify-center rounded-full p-0 sm:h-12 sm:w-auto sm:rounded-full sm:px-6">
                        <Link :href="tallyRoutes.create.url()">
                            <Plus class="size-[18px] sm:size-4" />
                            <span class="hidden sm:inline">{{ $t('tallies.new_tally') }}</span>
                        </Link>
                    </Button>
                </div>
            </div>

            <EmptyState
                v-if="tallies.length === 0"
                :title="$t('tallies.empty_state')"
                :description="$t('tallies.empty_description')"
                :action-label="$t('tallies.create_new')"
                :action-href="tallyRoutes.create.url()"
            />

            <template v-else>
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <SegmentedControl v-model="filter" variant="mobile" :options="filterOptions" class="sm:w-auto" />
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="h-10 w-full justify-center text-[12.5px] sm:w-auto sm:px-4">
                                <ListFilter class="size-3.5" />
                                {{ sortLabel }}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuRadioGroup v-model="sort">
                                <DropdownMenuRadioItem v-for="option in sortOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <p v-if="visibleTallies.length === 0" class="py-10 text-center text-[13px] text-text-3">{{ $t('tallies.no_matches') }}</p>

                <div>
                    <Link v-for="tally in visibleTallies" :key="tally.tally_id" :href="tallyRoutes.transactions.index.url(tally.uuid)" class="block">
                        <TallyListRow
                            :name="tally.name"
                            :meta="memberLabel(tally)"
                            :members="members(tally)"
                            :type-label="typeLabel(tally)"
                            :type-shared="tally.is_shared"
                            :detail="t('tallies.transactions_count', { count: tally.transactions_count }, tally.transactions_count)"
                            :amounts="amounts(tally)"
                            :amount-caption="t('tallies.total_spent')"
                            :status="balanceLine(tally).label"
                            :status-short="balanceLine(tally).shortLabel"
                            :status-tone="balanceLine(tally).tone"
                            :status-extra="balanceLine(tally).otherSymbols"
                            :color="tallyColor(tally)"
                            :glyph="tallyGlyph(tally)"
                        />
                    </Link>
                </div>
            </template>
        </div>
    </WebLayout>
</template>
