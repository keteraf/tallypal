/**
 * Category → glyph map (F-037), keyed on `categories.category_id` — never the derived,
 * colliding two-letter `category_code`. Source of truth: VERIFICATION.md F-037.
 *
 * Glyphs render as text, tinted by CSS `color`. Every glyph with an emoji form
 * (`Emoji=Yes`) carries U+FE0E (text presentation) so it never renders as a colour emoji.
 */
const TEXT_PRESENTATION = '︎';

/**
 * Glyphs are tinted text, never colour emoji: whatever selector came in, end on U+FE0E (FP-03).
 * Every component that draws a tally or category glyph goes through this.
 */
export function asTextGlyph<T extends string | undefined>(glyph: T): T {
    return (glyph ? glyph.replace(/\uFE0E|\uFE0F/g, '') + TEXT_PRESENTATION : glyph) as T;
}

export const INCOME_GLYPH = '↓';
export const FALLBACK_GLYPH = '◌';

export const CATEGORY_GLYPHS: Readonly<Record<number, string>> = {
    1: '▤', // Bills
    2: `✂${TEXT_PRESENTATION}`, // Clothing
    3: `☕${TEXT_PRESENTATION}`, // Dining Out
    4: '✎', // Education
    5: '★', // Entertainment
    6: `♥${TEXT_PRESENTATION}`, // Gifts & Donations
    7: '◈', // Groceries
    8: '✚', // Healthcare
    9: '▦', // Household Supplies
    10: '▣', // Insurance
    11: '✿', // Personal Care
    12: '⌂', // Rent
    13: '♪', // Subscriptions
    14: '◎', // Transportation
    15: `✈${TEXT_PRESENTATION}`, // Travel
    16: '↯', // Utilities
};

/** Glyph for a transaction row: income → ↓, mapped id → its glyph, otherwise ◌. */
export function categoryGlyph(categoryId?: number | null, type?: 'income' | 'expense' | string | null): string {
    if (type === 'income') {
        return INCOME_GLYPH;
    }

    return (categoryId != null ? CATEGORY_GLYPHS[categoryId] : undefined) ?? FALLBACK_GLYPH;
}
