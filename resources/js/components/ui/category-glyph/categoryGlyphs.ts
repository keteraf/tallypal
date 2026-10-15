/**
 * Category → glyph map (F-037), keyed on the category `slug` (owner decision C-D2, which replaced the
 * `category_id` key: ids of seeded rows are not guaranteed equal across environments). Source of truth: VERIFICATION.md, Categories batch.
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

export const CATEGORY_GLYPHS: Readonly<Record<string, string>> = {
    bills: '▤',
    clothing: `✂${TEXT_PRESENTATION}`,
    dining_out: `☕${TEXT_PRESENTATION}`,
    education: '✎',
    entertainment: '★',
    gifts_donations: `♥${TEXT_PRESENTATION}`,
    groceries: '◈',
    healthcare: '✚',
    household_supplies: '▦',
    insurance: '▣',
    personal_care: '✿',
    rent: '⌂',
    subscriptions: '♪',
    transportation: '◎',
    travel: `✈${TEXT_PRESENTATION}`,
    utilities: '↯',
    // Income (`Tallypal Categories.html`): none of these has an emoji form, so none needs U+FE0E.
    salary: '¤',
    side_work: '✦',
    bonus: '✪',
    investments: '▲',
    rental_income: '◫',
    refunds_returns: '↺',
    gifts_received: '❖',
    sold_items: '⬡',
    pension_benefits: '⊚',
    winnings: '⚄',
    other_income: '⊕',
};

/** Glyph for a transaction row: a mapped slug → its glyph; an uncategorised income → ↓; otherwise ◌. */
export function categoryGlyph(slug?: string | null, type?: 'income' | 'expense' | string | null): string {
    const mapped = slug != null ? CATEGORY_GLYPHS[slug] : undefined;

    if (mapped !== undefined) {
        return mapped;
    }

    return type === 'income' && slug == null ? INCOME_GLYPH : FALLBACK_GLYPH;
}
