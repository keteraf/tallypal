<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { CategoryGlyph } from '@/components/ui/category-glyph';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import { useInitials } from '@/composables/useInitials';
import { useNewTally } from '@/composables/useNewTally';
import { usePrimaryCurrency } from '@/composables/usePrimaryCurrency';
import { CATEGORY_COLORS, categoryColorFromString, type CategoryColor } from '@/lib/category';
import { spendingToneClass, zeroSpending } from '@/lib/spending';
import { dashboard, home, stats, you } from '@/routes';
import tallies from '@/routes/tallies';
import { type NavItem, type SidebarTally } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Activity, ChartColumn, House, LayoutGrid, LucideBookOpenText, Plus, Sun, Tally4, Tally5, Telescope } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { GitHubIcon } from 'vue3-simple-icons';

/**
 * App-wide desktop shell (`≥ md`). Rail on md–lg, expanded ≥ lg (driven by the
 * layout). Primary nav is Home / Tallies / Stats plus an admin group for Super
 * Admins; "You" is reached through the user block, and adding a transaction lives
 * in each page's top bar (and the `n` shortcut).
 */
const { t } = useI18n();

const page = usePage();
const isSuperAdmin = computed(() => page.props.auth?.user?.roles?.includes('Super Admin'));

const { updateAppearance } = useAppearance();
const { state: sidebarState } = useSidebar();
const newTally = useNewTally();
const primaryCurrency = usePrimaryCurrency();
const { getInitials } = useInitials();
const user = computed(() => page.props.auth.user);

const sidebarTallies = computed<SidebarTally[]>(() => page.props.sidebar_tallies ?? []);
const currentPath = computed(() => page.url.split('?')[0]);

const tallyUrl = (tally: SidebarTally): string => tallies.transactions.index.url(tally.uuid);
const isTallyActive = (tally: SidebarTally): boolean => currentPath.value === tallyUrl(tally) || currentPath.value.startsWith(`${tallyUrl(tally)}/`);
const tallyColor = (tally: SidebarTally): CategoryColor =>
    (CATEGORY_COLORS as readonly string[]).includes(tally.color ?? '') ? (tally.color as CategoryColor) : categoryColorFromString(tally.uuid);

type SidebarAmount = SidebarTally['monthly_totals'][number];
/** The month's spending: a plain number, no sign (FP-01). */
const formatAmount = (line: SidebarAmount): string =>
    line.amount.toLocaleString('en-US', { minimumFractionDigits: line.decimals, maximumFractionDigits: line.decimals });
/** One line per currency; a tally with no transactions yet has one zero line in the primary currency (FP-14). */
const spending = (tally: SidebarTally): SidebarAmount[] => (tally.monthly_totals.length > 0 ? tally.monthly_totals : [zeroSpending(primaryCurrency.value)]);
const otherCurrencySymbols = (tally: SidebarTally): string =>
    spending(tally)
        .slice(1)
        .filter((line) => line.amount !== 0)
        .map((line) => line.currency_symbol)
        .join(' ');
const amountTitle = (tally: SidebarTally): string => spending(tally).map((line) => `${line.currency_symbol} ${formatAmount(line)}`).join(' · ');
/** The rail shows only the glyph, so its tooltip leads with the name; the full sidebar already prints it. */
const tallyTitle = (tally: SidebarTally): string | undefined =>
    (sidebarState.value === 'collapsed' ? [tally.name, amountTitle(tally)].filter(Boolean).join(' · ') : amountTitle(tally)) || undefined;

// `updateTheme` toggles the `dark` class on <html>, so it is the resolved theme even when the preference is `system`.
const toggleTheme = () => updateAppearance(document.documentElement.classList.contains('dark') ? 'light' : 'dark');

const mainNavItems = computed<NavItem[]>(() => [
    { title: t('navigation.home'), href: '/home', icon: House, kbd: '1' },
    { title: t('navigation.tallies'), href: tallies.index.url(), icon: Tally5, kbd: '2' },
    { title: t('navigation.stats'), href: stats.url(), icon: ChartColumn, kbd: '3' },
]);

const adminNavItems = computed<NavItem[]>(() =>
    isSuperAdmin.value ? [{ title: t('navigation.dashboard'), href: dashboard.url(), icon: LayoutGrid }] : [],
);

const footerNavItems: NavItem[] = [];
if (isSuperAdmin.value) {
    footerNavItems.push(
        { title: t('navigation.pulse'), href: '/pulse', icon: Activity },
        { title: t('navigation.telescope'), href: '/telescope', icon: Telescope },
        { title: t('navigation.github_repo'), href: 'https://github.com/keteraf/tallypal', icon: GitHubIcon },
        { title: t('navigation.documentation'), href: 'https://laravel.com/docs/starter-kits#vue', icon: LucideBookOpenText },
    );
}
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <Link :href="home.url()" class="flex items-center gap-2.5 px-2 pt-3 pb-[22px] group-data-[collapsible=icon]:justify-center">
                        <span class="grid size-7 shrink-0 place-items-center rounded-mark bg-accent text-accent-ink">
                            <Tally4 class="size-4" stroke-width="2.2" />
                        </span>
                        <span class="text-brand text-text group-data-[collapsible=icon]:hidden">Tallypal</span>
                    </Link>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="group-data-[collapsible=icon]:[scrollbar-width:none] group-data-[collapsible=icon]:overflow-y-auto">
            <NavMain :items="mainNavItems" />

            <div class="mx-3 mt-[22px] mb-2 flex items-center justify-between group-data-[collapsible=icon]:hidden">
                <span class="text-[12px] font-semibold tracking-[0.06em] text-text-3 uppercase">{{ t('navigation.tallies') }}</span>
                <button
                    type="button"
                    :aria-label="t('tallies.new_tally')"
                    class="grid size-5 place-items-center rounded-md text-text-3 transition hover:text-accent"
                    @click="newTally.open()"
                >
                    <Plus class="size-3.5" />
                </button>
            </div>

            <ul v-if="sidebarTallies.length" class="mx-2 flex flex-col gap-1.5 group-data-[collapsible=icon]:mx-2.5">
                <li v-for="tally in sidebarTallies" :key="tally.uuid">
                    <Link
                        :href="tallyUrl(tally)"
                        :title="tallyTitle(tally)"
                        :aria-label="tally.name"
                        :aria-current="isTallyActive(tally) ? 'page' : undefined"
                        class="flex w-full items-center gap-2.5 rounded-(--radius) px-3 py-2 text-[13.5px] font-medium text-text-2 transition group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-[11px] hover:bg-surface hover:text-text aria-[current=page]:bg-surface-2 aria-[current=page]:text-text"
                    >
                        <CategoryGlyph size="xs" :color="tallyColor(tally)" :glyph="tally.glyph ?? tally.name.charAt(0).toUpperCase()" />
                        <span class="min-w-0 flex-1 truncate group-data-[collapsible=icon]:hidden">{{ tally.name }}</span>
                        <span class="ml-auto flex shrink-0 items-baseline gap-1 font-mono text-[12px] tabular-nums group-data-[collapsible=icon]:hidden">
                            <span :class="spendingToneClass(spending(tally)[0].amount)">{{ formatAmount(spending(tally)[0]) }}</span>
                            <span v-if="otherCurrencySymbols(tally)" class="text-text-3">· {{ otherCurrencySymbols(tally) }}</span>
                        </span>
                    </Link>
                </li>
            </ul>

            <NavMain v-if="adminNavItems.length" :items="adminNavItems" :label="t('navigation.admin')" />
        </SidebarContent>

        <SidebarFooter class="group-data-[collapsible=icon]:px-2.5">
            <NavFooter :items="footerNavItems" />
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton :tooltip="t('navigation.theme')" class="text-text-2 hover:bg-surface" @click="toggleTheme">
                        <Sun />
                        <span>{{ t('navigation.theme') }}</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <Link
                :href="you.url()"
                class="mt-2 flex items-center gap-2.5 border-t border-line px-3 pt-3 pb-1 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0"
            >
                <Avatar size="md">
                    <AvatarFallback color="c1">{{ getInitials(user.name) }}</AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1 group-data-[collapsible=icon]:hidden">
                    <div class="truncate text-[13px] font-medium text-text">{{ user.name }}</div>
                    <div class="truncate text-[11.5px] text-text-3">{{ user.email }}</div>
                </div>
            </Link>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
