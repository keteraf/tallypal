<script setup lang="ts">
import ResponsiveModal from '@/components/ResponsiveModal.vue';
import { AmountInput } from '@/components/ui/amount-input';
import type { AvatarGroupItem } from '@/components/ui/avatar';
import { Avatar, AvatarFallback, AvatarGroup, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { BalanceHero } from '@/components/ui/balance-hero';
import { Button } from '@/components/ui/button';
import { CardHeader } from '@/components/ui/card-header';
import { CATEGORY_GLYPHS, CategoryGlyph, categoryGlyph } from '@/components/ui/category-glyph';
import type { PickerCategoryType } from '@/components/ui/category-picker';
import { CategoryPicker, CategoryTile } from '@/components/ui/category-picker';
import { CheckList } from '@/components/ui/check-list';
import type { ChipOption } from '@/components/ui/choice-chip';
import { ChipGroup } from '@/components/ui/choice-chip';
import type { DataTableColumn } from '@/components/ui/data-table';
import { DataTable, DataTableItem } from '@/components/ui/data-table';
import { DetailHeader } from '@/components/ui/detail-header';
import { Dot } from '@/components/ui/dot';
import { EmptyState } from '@/components/ui/empty-state';
import { FormField } from '@/components/ui/form-field';
import { InOutCards } from '@/components/ui/in-out-cards';
import { Input } from '@/components/ui/input';
import { MarketingContainer } from '@/components/ui/marketing-container';
import { MarketingSection, MarketingSectionHead } from '@/components/ui/marketing-section';
import { MiniBar } from '@/components/ui/mini-bar';
import { NumberStepper } from '@/components/ui/number-stepper';
import type { QuickAction } from '@/components/ui/quick-actions';
import { QuickActions } from '@/components/ui/quick-actions';
import { SectionHeader } from '@/components/ui/section-header';
import { SegmentedControl } from '@/components/ui/segmented-control';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { SettingRow } from '@/components/ui/setting-row';
import { SettleBanner } from '@/components/ui/settle-banner';
import { SplitBar } from '@/components/ui/split-bar';
import { SplitLockButton } from '@/components/ui/split-lock-button';
import { SplitMemberRow } from '@/components/ui/split-member-row';
import { TallyListRow } from '@/components/ui/tally-list-row';
import { TallyRail, TallyRailCard } from '@/components/ui/tally-rail';
import { Textarea } from '@/components/ui/textarea';
import { TransactionRow } from '@/components/ui/transaction-row';
import { TrendBadge } from '@/components/ui/trend-badge';
import { TxDayHeader } from '@/components/ui/tx-day-header';
import { useAppearance } from '@/composables/useAppearance';
import { CATEGORY_COLORS } from '@/lib/category';
import { Head } from '@inertiajs/vue3';
import { ArrowLeftRight, MoreHorizontal, Plus, Settings2, Split } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { demoCategories, demoCategoryId, demoFrequent } from './categoryFixtures';

const { appearance, updateAppearance } = useAppearance();

const setAppearance = (value: string) => updateAppearance(value as 'light' | 'dark' | 'system');

const themeOptions = [
    { value: 'system', label: 'System' },
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
];

// Interactive demo state.
const talliesFilter = ref('all');
const range = ref('month');
const share = ref(60);
const partner = ref(40);
const splitTotal = computed(() => share.value + partner.value);
const splitMode = ref('custom');
const splitLocked = ref(true);
const splitShares = ref([50, 30, 20]);
const splitSharesTotal = computed(() => splitShares.value.reduce((sum, v) => sum + v, 0));
const splitBadShares = ref([50, 30, 10]);
const splitBadTotal = computed(() => splitBadShares.value.reduce((sum, v) => sum + v, 0));
const splitSegments = computed(() => splitShares.value.map((value, i) => ({ value, color: CATEGORY_COLORS[i] })));
const splitBadSegments = computed(() => splitBadShares.value.map((value, i) => ({ value, color: CATEGORY_COLORS[i] })));
const category = ref('groceries');
const tableColumns: DataTableColumn[] = [
    { key: 'item', label: 'Transaction' },
    { key: 'tally', label: 'Tally', hideBelow: 'xl' },
    { key: 'date', label: 'Date' },
    { key: 'amount', label: 'Amount', align: 'right' },
];
const tableRows = [
    {
        item: 'Mercato di Testaccio',
        category: 'Groceries',
        glyph: '◈',
        color: 'c4',
        tally: 'Personal',
        shared: false,
        date: 'Today, 11:24',
        amount: '− 42.80',
        positive: false,
    },
    {
        item: 'Rent — April',
        category: 'Housing',
        glyph: '⌂',
        color: 'c1',
        tally: 'Household · 60/40',
        shared: true,
        date: 'Apr 04',
        amount: '− 540.00',
        positive: false,
    },
    {
        item: 'Salary — April',
        category: 'Recurring',
        glyph: '↓',
        color: 'c3',
        tally: 'Personal',
        shared: false,
        date: 'Apr 02',
        amount: '+ 3,200.00',
        positive: true,
    },
] as const;
const tableColumnsThree = tableColumns.filter((column) => column.key !== 'tally');
const tallyColor = ref('c1');
const tags = ref<string[]>(['travel']);
const titleField = ref('');
const filledField = ref('Mercato di Testaccio');
const noteField = ref('');
const amountField = ref('42.80');
const tallyField = ref('personal');
const pillCurrency = ref('eur');

const trio: AvatarGroupItem[] = [
    { initials: 'M', color: 'c1' },
    { initials: 'L', color: 'c2' },
    { initials: 'S', color: 'c3' },
];
const crowd: AvatarGroupItem[] = [...trio, { initials: 'A', color: 'c6' }, { initials: 'R', color: 'c4' }];

const quickActions: QuickAction[] = [
    { label: 'Add', icon: Plus, primary: true },
    { label: 'Split', icon: Split },
    { label: 'Settle', icon: ArrowLeftRight },
    { label: 'New tally', icon: Plus },
];

const categoryOptions: ChipOption[] = [
    { value: 'groceries', label: 'Groceries', color: 'c4' },
    { value: 'housing', label: 'Housing', color: 'c1' },
    { value: 'dining', label: 'Dining', color: 'c6' },
    { value: 'travel', label: 'Travel', color: 'c2' },
    { value: 'health', label: 'Health', color: 'c3' },
];

const colorOptions: ChipOption[] = [
    { value: 'c1', color: 'c1', glyph: '⌂' },
    { value: 'c2', color: 'c2', glyph: '✈' },
    { value: 'c3', color: 'c3', glyph: '◐' },
    { value: 'c4', color: 'c4', glyph: '◆' },
    { value: 'c6', color: 'c6', glyph: '◇' },
];

const colorOfSlug = new Map(demoCategories.map((c) => [c.slug, c.color]));
const glyphMapRows = [
    ...Object.entries(CATEGORY_GLYPHS).map(([slug, glyph]) => ({ key: slug, glyph, color: colorOfSlug.get(slug) ?? undefined })),
    { key: 'income, no category', glyph: categoryGlyph(null, 'income'), color: 'pos' as const },
    { key: 'no category', glyph: categoryGlyph(null, 'expense'), color: undefined },
];

// Category picker (E7): every state, both types, in a desktop dialog frame and a phone sheet frame.
const { t, locale } = useI18n();
const pickerLocales = [
    { value: 'en', label: 'English' },
    { value: 'it', label: 'Italiano' },
];
const typeOptions = computed(() => [
    { value: 'expense', label: t('transactions.fields.expense') },
    { value: 'income', label: t('transactions.fields.income') },
]);

interface PickerDemo {
    key: string;
    caption: string;
    type: PickerCategoryType;
    model: number | null;
    open?: boolean;
    query?: string;
}

const pickerDemos = (layout: 'inline' | 'sheet'): PickerDemo[] => [
    {
        key: 'expense-selected',
        caption: 'Expense · selected',
        type: 'expense',
        model: demoCategoryId(layout === 'inline' ? 'groceries' : 'dining_out'),
    },
    {
        key: 'income-cleared',
        caption: 'Switched to Income · cleared',
        type: 'income',
        model: demoCategoryId(layout === 'inline' ? 'groceries' : 'dining_out'),
    },
    { key: 'income-selected', caption: 'Income · selected', type: 'income', model: demoCategoryId('salary') },
    { key: 'expense-cleared', caption: 'Switched to Expense · cleared', type: 'expense', model: demoCategoryId('salary') },
    { key: 'expense-empty', caption: 'Expense · empty', type: 'expense', model: null },
    { key: 'income-empty', caption: 'Income · empty', type: 'income', model: null },
    { key: 'income-pinned', caption: 'Income · Sold items (not frequent, pinned first)', type: 'income', model: demoCategoryId('sold_items') },
    { key: 'expense-pinned', caption: 'Expense · Travel (not frequent, pinned first)', type: 'expense', model: demoCategoryId('travel') },
    ...(layout === 'inline'
        ? [
              { key: 'expense-open', caption: 'Expense · "All" open', type: 'expense' as const, model: null, open: true },
              { key: 'income-open', caption: 'Income · "All" open', type: 'income' as const, model: demoCategoryId('salary'), open: true },
              { key: 'income-filtered', caption: 'Income · filtered "re"', type: 'income' as const, model: null, open: true, query: 're' },
              {
                  key: 'expense-alias',
                  caption: 'Expense · filtered by alias "netflix"',
                  type: 'expense' as const,
                  model: null,
                  open: true,
                  query: 'netflix',
              },
              { key: 'expense-no-match', caption: 'Expense · no match', type: 'expense' as const, model: null, open: true, query: 'zzz' },
          ]
        : []),
];
const inlinePickers = reactive(pickerDemos('inline'));
const sheetPickers = reactive(pickerDemos('sheet'));

const livePicker = reactive({
    open: false,
    type: 'expense' as PickerCategoryType,
    model: demoCategoryId('groceries') as number | null,
    lastCleared: '',
});

const glyphByColor: Record<string, string> = { c1: '⌂', c2: '✈', c3: '◐', c4: '◈', c5: '♪', c6: '☕' };
</script>

<template>
    <div class="min-h-screen bg-background text-text">
        <Head title="Design system" />

        <!-- Top bar -->
        <header class="sticky top-0 z-10 border-b border-line bg-[color-mix(in_oklch,var(--bg)_82%,transparent)] backdrop-blur-xl">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-5 py-4">
                <div>
                    <h1 class="text-[22px] font-semibold tracking-[-0.03em]">Design system</h1>
                    <p class="text-[12px] text-text-3">Phase 1 &amp; 2 · base components (dev only)</p>
                </div>
                <SegmentedControl
                    variant="mobile"
                    class="max-w-[240px]"
                    :options="themeOptions"
                    :model-value="appearance"
                    @update:model-value="setAppearance"
                />
            </div>
        </header>

        <main class="mx-auto max-w-5xl space-y-12 px-5 py-10">
            <!-- ============ ATOMS ============ -->
            <section class="space-y-6">
                <h2 class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Atoms</h2>

                <!-- Buttons -->
                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Button — variants</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button>Default</Button>
                        <Button variant="secondary">Secondary</Button>
                        <Button variant="outline">Outline</Button>
                        <Button variant="ghost">Ghost</Button>
                        <Button variant="destructive">Destructive</Button>
                        <Button variant="link">Link</Button>
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Button — sizes, touch &amp; disabled</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button size="sm">Small</Button>
                        <Button>Default</Button>
                        <Button size="lg">Large</Button>
                        <Button size="touch">Touch</Button>
                        <Button size="touch" class="w-full sm:w-auto">Full-width touch</Button>
                        <Button disabled>Disabled</Button>
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Icon buttons</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button variant="outline" size="icon" aria-label="Settings"><Settings2 /></Button>
                        <Button variant="secondary" size="icon" aria-label="Add"><Plus /></Button>
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Marketing — surface variant &amp; pill-lg size</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Button variant="surface">Log in</Button>
                        <Button variant="surface" size="pill-lg">See how it works</Button>
                        <Button size="pill-lg">Get Tallypal free</Button>
                    </div>
                </div>

                <!-- Badges + trend -->
                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Badge / Pill</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge>Default</Badge>
                        <Badge variant="secondary">Secondary</Badge>
                        <Badge variant="destructive">Destructive</Badge>
                        <Badge variant="outline">Outline</Badge>
                        <Badge variant="brand">Brand</Badge>
                        <Badge variant="pill">Groceries</Badge>
                        <Badge variant="accent">60/40 · Luca</Badge>
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Delta / Trend badge</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Badge variant="pos">+12.4%</Badge>
                        <Badge variant="neg">−4.1%</Badge>
                        <TrendBadge tone="pos-neg" direction="up" value="12.4%" label="vs March" />
                        <TrendBadge tone="pos-neg" direction="down" value="4.1%" label="vs March" />
                        <TrendBadge tone="neutral" direction="up" value="27.4%" label="vs Sep 1–14" />
                        <TrendBadge tone="neutral" direction="down" value="4.2%" label="vs March" />
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">
                        Marketing — status badge (hero, dot supplied by the consumer)
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Badge variant="status"><Dot color="pos" /> 11 currencies · web</Badge>
                    </div>
                </div>

                <!-- Avatars -->
                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Avatar — sizes</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Avatar size="xs"><AvatarFallback color="c1">M</AvatarFallback></Avatar>
                        <Avatar size="sm"><AvatarFallback color="c2">L</AvatarFallback></Avatar>
                        <Avatar size="md"><AvatarFallback color="c3">S</AvatarFallback></Avatar>
                        <Avatar size="lg"><AvatarFallback color="c6">A</AvatarFallback></Avatar>
                        <Avatar size="lg"><AvatarFallback>NC</AvatarFallback></Avatar>
                        <Avatar size="xl"><AvatarFallback color="c1">R</AvatarFallback></Avatar>
                        <Avatar size="xl"><AvatarFallback>NC</AvatarFallback></Avatar>
                        <Avatar size="xl">
                            <AvatarImage src="https://i.pravatar.cc/112?img=5" alt="Avatar" />
                            <AvatarFallback color="c4">IM</AvatarFallback>
                        </Avatar>
                        <Avatar size="lg">
                            <AvatarImage src="https://i.pravatar.cc/80?img=5" alt="Avatar" />
                            <AvatarFallback color="c4">IM</AvatarFallback>
                        </Avatar>
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Avatar — palette c1–c6</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <Avatar v-for="c in CATEGORY_COLORS" :key="c" size="md">
                            <AvatarFallback :color="c">{{ c.toUpperCase() }}</AvatarFallback>
                        </Avatar>
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Avatar group (stacked + overflow)</p>
                    <div class="flex flex-wrap items-center gap-6">
                        <AvatarGroup :items="trio" size="sm" />
                        <AvatarGroup :items="crowd" :max="3" size="sm" />
                    </div>
                </div>

                <!-- Dot / MiniBar / Glyph -->
                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Category dot</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Dot v-for="c in CATEGORY_COLORS" :key="c" :color="c" />
                        <Dot color="pos" />
                        <Dot color="neg" />
                        <Dot color="text-3" />
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Mini progress bar</p>
                    <div class="max-w-sm space-y-2">
                        <MiniBar :value="100" color="c1" />
                        <MiniBar :value="60" color="c4" />
                        <MiniBar :value="42" color="c6" />
                        <MiniBar :value="18" color="c3" />
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Category glyph — sizes &amp; palette</p>
                    <div class="flex flex-wrap items-end gap-3">
                        <CategoryGlyph color="c2" size="sm" glyph="✈" />
                        <CategoryGlyph color="c4" size="md" glyph="◈" />
                        <CategoryGlyph color="c1" size="lg" glyph="⌂" />
                        <CategoryGlyph v-for="c in CATEGORY_COLORS" :key="c" :color="c" size="md" :glyph="glyphByColor[c]" />
                    </div>
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">
                        Category glyph map (by slug, tinted by the category's stored colour)
                    </p>
                    <div class="flex flex-wrap gap-3" data-ds="glyph-map">
                        <div v-for="row in glyphMapRows" :key="row.key" class="flex w-16 flex-col items-center gap-1">
                            <CategoryGlyph
                                :color="row.color"
                                size="md"
                                :glyph="row.glyph"
                                :class="row.color ? '' : 'bg-[color-mix(in_oklch,var(--text-3)_20%,transparent)] text-text-3'"
                            />
                            <span class="font-mono text-[11px] text-text-3">{{ row.key }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============ INTERACTIVE ============ -->
            <section class="space-y-6">
                <h2 class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Interactive</h2>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="space-y-3">
                        <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Segmented — mobile ({{ talliesFilter }})</p>
                        <SegmentedControl
                            v-model="talliesFilter"
                            variant="mobile"
                            :options="[
                                { value: 'all', label: 'All' },
                                { value: 'shared', label: 'Shared' },
                                { value: 'private', label: 'Private' },
                            ]"
                        />
                        <SegmentedControl
                            v-model="range"
                            variant="mobile"
                            :options="[
                                { value: 'week', label: 'Week' },
                                { value: 'month', label: 'Month' },
                                { value: 'year', label: 'Year' },
                            ]"
                        />
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Number stepper (split %, total {{ splitTotal }})</p>
                    <div class="flex flex-wrap items-center gap-6">
                        <NumberStepper v-model="share" suffix="%" />
                        <NumberStepper v-model="partner" suffix="%" />
                        <span class="num text-[13px]" :class="splitTotal === 100 ? 'text-pos' : 'text-neg'">{{ splitTotal }}%</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Split editor extras (E3)</p>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-3">
                            <p class="text-xs text-text-3">SegmentedControl size="sm" · NumberStepper size="sm" · SplitLockButton</p>
                            <SegmentedControl
                                v-model="splitMode"
                                variant="mobile"
                                size="sm"
                                :options="[
                                    { value: 'equal', label: 'Equal' },
                                    { value: 'custom', label: 'Custom' },
                                ]"
                            />
                            <div class="flex flex-wrap items-center gap-4 rounded-sm bg-surface-2 px-3.5 py-3">
                                <NumberStepper v-model="splitShares[0]" size="sm" suffix="%" label="Marta share" />
                                <SplitLockButton v-model:locked="splitLocked" />
                                <span class="text-xs text-text-3">locked: {{ splitLocked }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 rounded-sm bg-surface-2 px-3.5 py-3">
                                <NumberStepper :model-value="0" size="sm" suffix="%" label="At minimum" />
                                <NumberStepper :model-value="100" size="sm" suffix="%" label="At maximum" />
                                <SplitLockButton :locked="false" />
                            </div>
                            <div class="pointer-events-none flex items-center gap-4 rounded-sm bg-surface-2 px-3.5 py-3 opacity-45">
                                <NumberStepper :model-value="50" size="sm" suffix="%" label="Equal mode" />
                                <span class="text-xs text-text-3">Equal mode: inert</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <p class="text-xs text-text-3">SplitBar — total {{ splitSharesTotal }}% and {{ splitBadTotal }}% (≠ 100)</p>
                            <div class="rounded-sm bg-surface-2 px-3.5 py-3">
                                <SplitBar :segments="splitSegments" />
                            </div>
                            <div class="rounded-sm bg-surface-2 px-3.5 py-3">
                                <SplitBar :segments="splitBadSegments" />
                            </div>
                            <div class="rounded-sm bg-surface-2 px-3.5 py-3">
                                <SplitBar :segments="[{ value: 100, color: 'c1' }]" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-3">
                    <div class="space-y-3">
                        <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Chips — single ({{ category }})</p>
                        <ChipGroup v-model="category" :options="categoryOptions" />
                    </div>
                    <div class="space-y-3">
                        <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Colour &amp; icon ({{ tallyColor }})</p>
                        <ChipGroup v-model="tallyColor" :options="colorOptions" />
                    </div>
                    <div class="space-y-3">
                        <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">
                            Chips — multiple ({{ tags.join(', ') || 'none' }})
                        </p>
                        <ChipGroup v-model="tags" multiple :options="categoryOptions" />
                    </div>
                </div>
            </section>

            <!-- ============ CATEGORY PICKER (E7) ============ -->
            <section class="space-y-6" data-ds-section="category-picker">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Category picker (E7)</h2>
                    <SegmentedControl
                        variant="mobile"
                        size="sm"
                        class="max-w-[220px]"
                        :options="pickerLocales"
                        :model-value="locale"
                        @update:model-value="(value) => (locale = String(value))"
                    />
                </div>

                <div class="space-y-3" data-ds="tile-states">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Tile states</p>
                    <div class="flex flex-wrap items-start gap-4">
                        <div
                            v-for="state in ['default', 'hover', 'focus', 'selected', 'sel + focus']"
                            :key="state"
                            class="flex w-[76px] flex-col items-center gap-2"
                        >
                            <CategoryTile
                                class="w-full"
                                tabindex="-1"
                                glyph="◈"
                                :label="t('categories.groceries')"
                                color="c4"
                                :selected="state.startsWith('sel')"
                                :preview="state === 'hover' ? 'hover' : state.endsWith('focus') ? 'focus' : undefined"
                            />
                            <span class="font-mono text-[11px] text-text-3">{{ state }}</span>
                        </div>
                        <div v-for="state in ['all', 'all · open']" :key="state" class="flex w-[76px] flex-col items-center gap-2">
                            <CategoryTile
                                class="w-full"
                                tabindex="-1"
                                variant="all"
                                glyph="⋯"
                                :label="t('category_picker.all', { count: 16 })"
                                :open="state !== 'all'"
                            />
                            <span class="font-mono text-[11px] text-text-3">{{ state }}</span>
                        </div>
                        <div class="flex w-[76px] flex-col items-center gap-2">
                            <CategoryTile class="w-full" tabindex="-1" glyph="◈" :label="t('categories.groceries')" color="c4" tall />
                            <span class="font-mono text-[11px] text-text-3">sheet · 80</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Desktop dialog (≥ md · inline panel · 5 + All)</p>
                    <div class="grid gap-5 lg:grid-cols-2">
                        <div v-for="demo in inlinePickers" :key="demo.key" class="space-y-2" :data-ds="`inline-${demo.key}`">
                            <p class="text-[12px] text-text-3">{{ demo.caption }}</p>
                            <div class="w-full max-w-[500px] rounded-lg bg-surface px-6 pt-5 pb-2 shadow-modal">
                                <FormField :label="t('transactions.fields.category')">
                                    <CategoryPicker
                                        v-model="demo.model"
                                        v-model:open="demo.open"
                                        v-model:query="demo.query"
                                        layout="inline"
                                        :type="demo.type"
                                        :categories="demoCategories"
                                        :frequent="demoFrequent"
                                    />
                                </FormField>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">
                        Phone sheet (&lt; md · second sheet at 80 % · 4 + All)
                    </p>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div v-for="demo in sheetPickers" :key="demo.key" class="space-y-2" :data-ds="`sheet-${demo.key}`">
                            <p class="text-[12px] text-text-3">{{ demo.caption }}</p>
                            <div class="-mx-5 max-w-[375px] rounded-t-lg border-t border-line bg-surface px-5 pt-2.5 pb-1 sm:mx-0 sm:w-full">
                                <div class="mx-auto mb-4 h-1 w-9.5 rounded-full bg-surface-3" />
                                <FormField :label="t('transactions.fields.category')">
                                    <CategoryPicker
                                        v-model="demo.model"
                                        v-model:open="demo.open"
                                        layout="sheet"
                                        :type="demo.type"
                                        :categories="demoCategories"
                                        :frequent="demoFrequent"
                                    />
                                </FormField>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3" data-ds="live">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">
                        In the Add-transaction shell (dialog ≥ md, sheet &lt; md) · selected {{ livePicker.model ?? 'none'
                        }}{{ livePicker.lastCleared ? ` · last cleared ${livePicker.lastCleared}` : '' }}
                    </p>
                    <Button variant="surface" data-ds="open-live-picker" @click="livePicker.open = true">Open the picker in a modal</Button>
                    <ResponsiveModal v-model:open="livePicker.open" :title="t('navigation.add_transaction_title')">
                        <SegmentedControl v-model="livePicker.type" variant="mobile" class="mb-[18px]" :options="typeOptions" />
                        <FormField :label="t('transactions.fields.category')">
                            <CategoryPicker
                                v-model="livePicker.model"
                                :type="livePicker.type"
                                :categories="demoCategories"
                                :frequent="demoFrequent"
                                @cleared="(slug) => (livePicker.lastCleared = slug)"
                            />
                        </FormField>
                        <FormField :label="t('transactions.fields.title')" for="ds-live-title">
                            <Input id="ds-live-title" variant="filled" placeholder="e.g. Mercato di Testaccio" />
                        </FormField>
                    </ResponsiveModal>
                </div>
            </section>

            <!-- ============ FORMS ============ -->
            <section class="space-y-6">
                <h2 class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Forms</h2>
                <div class="max-w-xl space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Amount input</p>
                    <AmountInput v-model="amountField" label="Amount" hint="Tap to enter the amount" currency="EUR" />
                </div>
                <div class="grid max-w-xl gap-x-6 sm:grid-cols-2">
                    <FormField label="Title" for="ds-title">
                        <Input id="ds-title" v-model="filledField" variant="filled" placeholder="e.g. Mercato di Testaccio" />
                    </FormField>
                    <FormField label="Amount" for="ds-amount" error="This field is required.">
                        <Input id="ds-amount" v-model="titleField" variant="filled" placeholder="0.00" />
                    </FormField>
                    <FormField label="Tally" for="ds-tally">
                        <Select v-model="tallyField">
                            <SelectTrigger id="ds-tally" variant="filled">
                                <SelectValue placeholder="Choose a tally" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="personal">Personal</SelectItem>
                                <SelectItem value="household">Household</SelectItem>
                                <SelectItem value="lisbon">Lisbon trip</SelectItem>
                            </SelectContent>
                        </Select>
                    </FormField>
                    <FormField label="Tally (error)" for="ds-tally-err" error="Choose a tally.">
                        <Select>
                            <SelectTrigger id="ds-tally-err" variant="filled">
                                <SelectValue placeholder="Choose a tally" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="personal">Personal</SelectItem>
                            </SelectContent>
                        </Select>
                    </FormField>
                    <FormField label="Note (error)" for="ds-note-err" error="The note is too long." class="sm:col-span-2">
                        <Textarea id="ds-note-err" variant="filled" placeholder="Add a note…" />
                    </FormField>
                    <FormField label="Disabled">
                        <Input variant="filled" placeholder="Disabled" disabled />
                    </FormField>
                    <FormField label="Note" for="ds-note" class="sm:col-span-2">
                        <Textarea id="ds-note" v-model="noteField" variant="filled" placeholder="Add a note…" />
                    </FormField>
                </div>
            </section>

            <!-- ============ MOLECULES ============ -->
            <section class="space-y-8">
                <h2 class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Molecules</h2>

                <div class="mx-auto w-full max-w-[400px] space-y-2">
                    <BalanceHero
                        label="Net this month"
                        :amount="1284.4"
                        :delta="{ tone: 'pos-neg', direction: 'up', value: '12.4%', label: 'vs March' }"
                    />
                    <InOutCards :income="3200" :expense="1915" />
                    <QuickActions :actions="quickActions" />

                    <SectionHeader label="Your tallies" action-label="See all" />
                    <TallyRail>
                        <TallyRailCard name="Lisbon trip" meta="3 members · 60 / 40" :amount="487.4" color="c2" glyph="✈" :members="trio" />
                        <TallyRailCard
                            name="Household"
                            meta="60 / 40 split"
                            :amount="1140"
                            :decimals="0"
                            color="c1"
                            glyph="⌂"
                            :members="trio.slice(0, 2)"
                        />
                        <TallyRailCard name="Freelance" meta="Private" :amount="2210" :decimals="0" color="c3" glyph="◐" />
                        <TallyRailCard variant="new" />
                    </TallyRail>

                    <SectionHeader label="Recent" action-label="All activity" />
                    <div class="rounded-card bg-surface px-4">
                        <TransactionRow
                            title="Mercato di Testaccio"
                            subtitle="Today, 11:24"
                            :pill="{ label: 'Groceries' }"
                            :amount="42.8"
                            color="c4"
                            glyph="◈"
                        />
                        <div class="border-t border-line" />
                        <TransactionRow
                            title="Rent — April"
                            subtitle="Apr 04"
                            :pill="{ label: '60/40 · Luca', variant: 'shared' }"
                            :amount="540"
                            color="c1"
                            glyph="⌂"
                        />
                        <div class="border-t border-line" />
                        <TransactionRow
                            title="Salary — April"
                            subtitle="Apr 02"
                            :pill="{ label: 'Recurring' }"
                            type="income"
                            :amount="3200"
                            color="c3"
                            glyph="↓"
                        />
                    </div>

                    <TxDayHeader label="Yesterday" total="− 133.00" />
                    <div class="rounded-card bg-surface px-4">
                        <TransactionRow
                            title="Dinner — Bairro Alto"
                            subtitle="Apr 09"
                            :pill="{ label: 'Split 3', variant: 'shared' }"
                            :amount="124.5"
                            color="c6"
                            glyph="☕"
                        />
                        <div class="border-t border-line" />
                        <TransactionRow
                            title="Spotify Family"
                            subtitle="Apr 08"
                            :pill="{ label: '50/50 · Luca', variant: 'shared' }"
                            :amount="8.5"
                            color="c2"
                            glyph="♪"
                        />
                    </div>

                    <SectionHeader label="Tallies list" />
                    <TallyListRow
                        name="Lisbon trip"
                        meta="50 / 50"
                        :members="trio"
                        :amount="487.4"
                        status="Luca owes 76.30"
                        status-tone="pos"
                        color="c2"
                        glyph="✈"
                    />
                    <TallyListRow
                        name="Household"
                        meta="60 / 40"
                        :members="trio.slice(0, 2)"
                        :amount="1140"
                        :decimals="0"
                        status="Settled"
                        color="c1"
                        glyph="⌂"
                    />
                    <TallyListRow name="Freelance" meta="Private ledger" :amount="2210" :decimals="0" status="Settled" color="c3" glyph="◐" />

                    <SectionHeader label="Detail header" />
                    <DetailHeader title="Lisbon trip" subtitle="3 members · 50 / 50" back-href="#">
                        <template #actions>
                            <Button variant="outline" size="icon" aria-label="More"><MoreHorizontal /></Button>
                        </template>
                    </DetailHeader>

                    <SectionHeader label="Settle banner" />
                    <SettleBanner direction="owes-you" name="Luca" :amount="76.3" />
                    <SettleBanner direction="you-owe" name="Sara" :amount="21" />
                    <SettleBanner direction="settled" />

                    <SectionHeader label="Card header" />
                    <div class="space-y-3">
                        <div class="rounded-card bg-surface p-4">
                            <CardHeader title="Title only" />
                            <p class="text-[13px] text-text-3">Body</p>
                        </div>
                        <div class="rounded-card bg-surface p-4">
                            <CardHeader title="Recent" action-label="All activity" action-href="#" />
                            <p class="text-[13px] text-text-3">Body</p>
                        </div>
                        <div class="rounded-card bg-surface p-4">
                            <CardHeader title="By category" action-label="Details">
                                <template #meta><span class="text-[12px] text-text-3">October</span></template>
                            </CardHeader>
                            <p class="text-[13px] text-text-3">Body</p>
                        </div>
                    </div>

                    <SectionHeader label="Empty state" />
                    <div class="space-y-3">
                        <EmptyState
                            title="No tallies yet"
                            description="Create a tally to start tracking and splitting expenses."
                            action-label="New tally"
                            action-href="#"
                        />
                        <EmptyState title="Nothing here yet" description="Add your first transaction to see it listed." />
                    </div>

                    <SectionHeader label="Setting row" />
                    <div class="space-y-3">
                        <div class="rounded-card bg-surface p-4">
                            <CardHeader title="Preferences" />
                            <div>
                                <SettingRow title="Theme" description="Light, dark or follow the system">
                                    <template #control>
                                        <Select :model-value="appearance" @update:model-value="(value) => setAppearance(String(value))">
                                            <SelectTrigger variant="pill" aria-label="Theme">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="light">Light</SelectItem>
                                                <SelectItem value="dark">Dark</SelectItem>
                                                <SelectItem value="system">System</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </template>
                                </SettingRow>
                                <SettingRow title="Default currency">
                                    <template #control>
                                        <Select v-model="pillCurrency">
                                            <SelectTrigger variant="pill" aria-label="Default currency">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="none">None</SelectItem>
                                                <SelectItem value="eur">EUR €</SelectItem>
                                                <SelectItem value="usd">USD $</SelectItem>
                                                <SelectItem value="gbp">GBP £</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </template>
                                </SettingRow>
                                <SettingRow title="Password" description="Change your password" href="#" />
                            </div>
                        </div>
                        <div class="rounded-card bg-surface p-4">
                            <CardHeader title="Data" />
                            <div>
                                <SettingRow tone="neg" title="Sign out" href="#" />
                                <SettingRow tone="neg" title="Delete account" description="This cannot be undone">
                                    <template #control><Button variant="ghost" size="sm" class="text-neg">Delete</Button></template>
                                </SettingRow>
                            </div>
                        </div>
                    </div>

                    <SectionHeader label="Pill select" />
                    <div class="flex flex-wrap items-center gap-3 rounded-card bg-surface p-4">
                        <Select model-value="eur">
                            <SelectTrigger variant="pill" aria-label="Default"><SelectValue /></SelectTrigger>
                            <SelectContent><SelectItem value="eur">EUR €</SelectItem><SelectItem value="usd">USD $</SelectItem></SelectContent>
                        </Select>
                        <Select>
                            <SelectTrigger variant="pill" aria-label="Placeholder"><SelectValue placeholder="Choose" /></SelectTrigger>
                            <SelectContent><SelectItem value="eur">EUR €</SelectItem></SelectContent>
                        </Select>
                        <Select model-value="eur" disabled>
                            <SelectTrigger variant="pill" aria-label="Disabled"><SelectValue /></SelectTrigger>
                            <SelectContent><SelectItem value="eur">EUR €</SelectItem></SelectContent>
                        </Select>
                    </div>

                    <SectionHeader label="Data table" />
                    <div class="space-y-3">
                        <div class="rounded-card bg-surface p-4">
                            <CardHeader title="Recent" action-label="All activity" action-href="#" />
                            <DataTable :columns="tableColumns" :rows="[...tableRows]">
                                <template #cell-item="{ row }">
                                    <DataTableItem :title="row.item" :subtitle="row.category" :color="row.color" :glyph="row.glyph">
                                        <template #inline
                                            ><Badge :variant="row.shared ? 'accent' : 'pill'" class="ml-1.5 whitespace-nowrap">{{
                                                row.tally
                                            }}</Badge></template
                                        >
                                    </DataTableItem>
                                </template>
                                <template #cell-tally="{ row }"
                                    ><Badge :variant="row.shared ? 'accent' : 'pill'">{{ row.tally }}</Badge></template
                                >
                                <template #cell-amount="{ row }"
                                    ><span :class="['num text-[14.5px] font-semibold', row.positive ? 'text-pos' : '']">{{
                                        row.amount
                                    }}</span></template
                                >
                            </DataTable>
                        </div>
                        <p class="text-[12px] text-text-3">3 columns, linked rows</p>
                        <div class="rounded-card bg-surface p-4">
                            <DataTable :columns="tableColumnsThree" :rows="[...tableRows]" :row-href="() => '#'">
                                <template #cell-item="{ row }"
                                    ><DataTableItem :title="row.item" :subtitle="row.category" :color="row.color" :glyph="row.glyph"
                                /></template>
                                <template #cell-amount="{ row }"
                                    ><span :class="['num text-[14.5px] font-semibold', row.positive ? 'text-pos' : '']">{{
                                        row.amount
                                    }}</span></template
                                >
                            </DataTable>
                        </div>
                    </div>

                    <SectionHeader label="Split so far" />
                    <div class="rounded-card bg-surface px-4">
                        <SplitMemberRow name="Marta (you)" initials="M" :amount="380" :percentage="78" color="c1" />
                        <div class="border-t border-line" />
                        <SplitMemberRow name="Luca" initials="L" :amount="86" :percentage="18" color="c2" />
                        <div class="border-t border-line" />
                        <SplitMemberRow name="Sara" initials="S" :amount="21" :percentage="4" color="c3" />
                    </div>
                </div>
            </section>

            <!-- ============ MARKETING FOUNDATION (Batch 4.3a) ============ -->
            <section class="space-y-6">
                <h2 class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Marketing foundation</h2>

                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">MarketingContainer</p>
                    <MarketingContainer class="rounded-card border border-line bg-surface py-4">
                        <p class="text-sm text-text-2">Max-width {{ '1240px' }}, 28px side gutter at every width.</p>
                    </MarketingContainer>
                </div>

                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">MarketingSection + MarketingSectionHead</p>
                    <div class="overflow-hidden rounded-card border border-line">
                        <MarketingSection rule>
                            <MarketingSectionHead
                                kicker="How it works"
                                heading="Three taps from paid to settled."
                                lede="No bank connection, no onboarding wizard. Add what you spent, choose who it belongs to, and Tallypal does the rest."
                            />
                            <p class="text-sm text-text-2">Section body renders inside the container below the head.</p>
                        </MarketingSection>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="space-y-3">
                        <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">CheckList — tone="accent" (highlighted plan)</p>
                        <div class="max-w-xs rounded-card border border-line bg-surface p-5">
                            <CheckList
                                tone="accent"
                                :items="[
                                    { label: 'Everything in Free' },
                                    { label: 'Unlimited shared tallies & members' },
                                    { label: 'Recurring rules', state: 'off' },
                                ]"
                            />
                        </div>
                    </div>
                    <div class="space-y-3">
                        <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">CheckList — tone="pos" (privacy tile)</p>
                        <div class="max-w-xs rounded-card border border-line bg-surface p-5">
                            <CheckList tone="pos" :items="[{ label: 'End-to-end encrypted sync' }, { label: 'CSV & PDF export, one click' }]" />
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Glow tokens — hero / band (verify in both themes)</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="relative h-40 overflow-hidden rounded-card border border-line bg-bg">
                            <div class="absolute inset-0" style="background-image: var(--glow-hero)" />
                        </div>
                        <div class="relative h-40 overflow-hidden rounded-card border border-line bg-surface">
                            <div class="absolute inset-0" style="background-image: var(--glow-band)" />
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">Shadows — plan-hi / float / device</p>
                    <div class="flex flex-wrap items-center gap-10 py-4">
                        <div class="size-16 rounded-card bg-surface shadow-plan-hi" />
                        <div class="size-16 rounded-card bg-surface shadow-float" />
                        <div class="size-16 rounded-card bg-surface shadow-device" />
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
