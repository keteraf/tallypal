import en from '@/i18n/locales/en';
import it from '@/i18n/locales/it';
import type { CategoryColor } from '@/lib/category';
import { CATEGORY_ALIASES, DEFAULT_FREQUENT, SOFT_HYPHENATED } from './categoryCatalogue';

/**
 * The `CategoryPicker` logic (COMPONENT_INVENTORY E7), kept free of Vue so every rule is unit-tested:
 * the alias search, the frequent row, the clear rule on a type switch and the keyboard model.
 */

export type PickerCategoryType = 'expense' | 'income';

/** A category as the form data sends it (`TransactionController::formData()`). */
export interface PickerCategory {
    category_id: number;
    slug: string;
    type: PickerCategoryType;
    color: CategoryColor | null;
}

/** `frequent_categories` from the form data (B-15): slugs, most used first. */
export type FrequentCategories = Readonly<Record<PickerCategoryType, readonly string[]>>;

/**
 * What a key press does. `null` means "not ours": the browser keeps its default (typing, Tab,
 * Space/Enter clicking a tile). `stay` is handled but moves nothing (it still prevents the default).
 */
export type PickerKeyAction = { kind: 'focus'; index: number } | { kind: 'focus-filter' } | { kind: 'pick-first' } | { kind: 'close' } | { kind: 'stay' };

/** Columns of the "All" grid (DESIGN_TOKENS §7.2). */
export const GRID_COLUMNS = 4;

/** Frequent tiles in the row: five beside "All" ≥ md, four on phones. */
export const ROW_SIZE = { desktop: 5, phone: 4 } as const;

const labels = (locale: typeof en, slug: string): string | undefined => (locale.categories as Record<string, string>)[slug];

/** Lower-case, accents stripped, outer space trimmed and inner runs collapsed: "  Più Città " → "piu citta". */
export function normaliseSearchText(value: string): string {
    return value
        .normalize('NFD')
        .replace(/\p{M}/gu, '')
        .toLowerCase()
        .trim()
        .replace(/\s+/g, ' ');
}

/** Everything the filter matches for one category: the English label, the Italian label, then its aliases. */
export function categorySearchTerms(slug: string): string[] {
    return [labels(en, slug), labels(it, slug), ...(CATEGORY_ALIASES[slug] ?? [])].filter((term): term is string => term !== undefined);
}

/** The categories whose labels (either language) or aliases contain the query, in list order. A blank query keeps them all. */
export function filterCategories<T extends { slug: string }>(categories: readonly T[], query: string): T[] {
    const needle = normaliseSearchText(query);

    if (needle === '') {
        return [...categories];
    }

    return categories.filter((category) => categorySearchTerms(category.slug).some((term) => normaliseSearchText(term).includes(needle)));
}

/**
 * The frequent row: the server's list padded with the design defaults (no slug twice, only slugs of
 * this type), cut to `size`. A selected category that is not among them is pinned to the first slot.
 */
export function frequentRow(input: {
    frequent: readonly string[];
    type: PickerCategoryType;
    available: readonly string[];
    selected: string | null;
    size: number;
}): string[] {
    const { frequent, type, available, selected, size } = input;
    const pool = [...new Set([...frequent, ...DEFAULT_FREQUENT[type]])].filter((slug) => available.includes(slug));
    const row = pool.slice(0, size);

    if (selected !== null && available.includes(selected) && !row.includes(selected)) {
        return [selected, ...row].slice(0, size);
    }

    return row;
}

/** The selected category when it does not belong to `type` (it must be cleared and named); otherwise `null`. */
export function categoryToClear(selectedId: number | null, type: PickerCategoryType, categories: readonly PickerCategory[]): PickerCategory | null {
    const selected = selectedId === null ? undefined : categories.find((category) => category.category_id === selectedId);

    return selected !== undefined && selected.type !== type ? selected : null;
}

/** Index of the row's single tab stop: the selected tile, else "All" (index `row.length`). */
export function rowTabStop(row: readonly string[], selected: string | null): number {
    const index = selected === null ? -1 : row.indexOf(selected);

    return index === -1 ? row.length : index;
}

/** Row (a radio group, "All" last): ← → move one tile and wrap, Home / End jump to the ends. */
export function rowKeyAction(key: string, index: number, count: number): PickerKeyAction | null {
    switch (key) {
        case 'ArrowRight':
            return { kind: 'focus', index: (index + 1) % count };
        case 'ArrowLeft':
            return { kind: 'focus', index: (index - 1 + count) % count };
        case 'Home':
            return { kind: 'focus', index: 0 };
        case 'End':
            return { kind: 'focus', index: count - 1 };
        default:
            return null;
    }
}

/** "All" grid: ← → one tile, ↑ ↓ a row of `columns`, clamped (no wrap); ↑ from the first row returns to the filter; Esc closes. */
export function gridKeyAction(key: string, index: number, count: number, columns = GRID_COLUMNS): PickerKeyAction | null {
    const last = count - 1;

    switch (key) {
        case 'ArrowRight':
            return { kind: 'focus', index: Math.min(index + 1, last) };
        case 'ArrowLeft':
            return { kind: 'focus', index: Math.max(index - 1, 0) };
        case 'ArrowDown':
            return { kind: 'focus', index: Math.min(index + columns, last) };
        case 'ArrowUp':
            return index - columns < 0 ? { kind: 'focus-filter' } : { kind: 'focus', index: index - columns };
        case 'Escape':
            return { kind: 'close' };
        default:
            return null;
    }
}

/** Filter field: ↓ to the first tile, Enter picks the first match (and never submits the form), Esc closes. */
export function filterKeyAction(key: string, matchCount: number): PickerKeyAction | null {
    switch (key) {
        case 'ArrowDown':
            return matchCount > 0 ? { kind: 'focus', index: 0 } : { kind: 'stay' };
        case 'Enter':
            return matchCount > 0 ? { kind: 'pick-first' } : { kind: 'stay' };
        case 'Escape':
            return { kind: 'close' };
        default:
            return null;
    }
}

/** A tile label with the design's soft hyphens, word by word, so long words break cleanly over two lines. */
export function withSoftHyphens(label: string): string {
    return label
        .split(' ')
        .map((word) => SOFT_HYPHENATED[word] ?? word)
        .join(' ');
}
