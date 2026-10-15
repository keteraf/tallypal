/**
 * Category / tally colour tokens (`c1`…`c6`) — see `docs/design/DESIGN_TOKENS.md §7`.
 * Colour is user-chosen and persisted; unset rows fall back to a deterministic cycle.
 */
export type CategoryColor = 'c1' | 'c2' | 'c3' | 'c4' | 'c5' | 'c6';

export const CATEGORY_COLORS: readonly CategoryColor[] = ['c1', 'c2', 'c3', 'c4', 'c5', 'c6'] as const;

/** Geometric symbol catalogue for tally glyphs. Matches the design's set — not emoji. */
export const TALLY_GLYPHS: readonly string[] = ['⌂', '✈', '◐', '◈', '♪', '☕'] as const;

/** CSS custom-property reference for a category colour, e.g. `var(--c3)`. */
export function categoryColorVar(color: CategoryColor): string {
    return `var(--${color})`;
}

/** Soft translucent fill for glyph / icon / avatar backgrounds. */
export function categorySoftFill(color: CategoryColor, percent = 20): string {
    return `color-mix(in oklch, var(--${color}) ${percent}%, transparent)`;
}

/** Deterministic fallback colour for a row without a persisted colour: `c{(id % 6) + 1}`. */
export function categoryColorFromId(id: number): CategoryColor {
    return CATEGORY_COLORS[((id % 6) + 6) % 6];
}

/**
 * Deterministic fallback colour keyed by a string (category/tally name) rather than a
 * numeric id, for payloads that don't expose one yet. The same string always maps to the
 * same colour. Not for rows shown together: a hash can collide, so the By category cards colour
 * by rank (`breakdownRowColors`).
 */
export function categoryColorFromString(value: string): CategoryColor {
    let hash = 0;

    for (let i = 0; i < value.length; i++) {
        hash = (hash * 31 + value.charCodeAt(i)) | 0;
    }

    return categoryColorFromId(hash);
}

/**
 * Hues of a breakdown card's named rows, by rank: the design's order (blue, amber, cyan, magenta,
 * green). c5 (red) is left out because the design never gives a category that hue; it reads as `--neg`.
 */
export const BREAKDOWN_RANK_COLORS: readonly CategoryColor[] = ['c1', 'c4', 'c2', 'c6', 'c3'] as const;

export interface BreakdownRowColors {
    dot: CategoryColor | 'text-3';
    bar: CategoryColor | 'surface-3';
}

/**
 * Colours for the rows of a By category card (Home and Stats), by rank within the card instead of a
 * hash of the name (FP-12): the top five get distinct hues and the "Other" row is muted (`--text-3` dot,
 * `--surface-3` bar), so rows shown together never share a colour. The card holds at most five named
 * rows (`CategoryBars`); the wrap-around only keeps this total.
 */
export function breakdownRowColors(rows: readonly { is_other: boolean }[]): BreakdownRowColors[] {
    let rank = 0;

    return rows.map((row) => {
        if (row.is_other) {
            return { dot: 'text-3', bar: 'surface-3' };
        }

        const color = BREAKDOWN_RANK_COLORS[rank % BREAKDOWN_RANK_COLORS.length];
        rank++;

        return { dot: color, bar: color };
    });
}

/** A glyph tint: a category hue, `--pos` for the uncategorised-income arrow, `--text-3` for the uncategorised ◌. */
export type GlyphColor = CategoryColor | 'pos' | 'text-3';

/**
 * Tint of a transaction row's glyph tile (C-D4, scoped): a category's stored colour, income categories
 * included, falling back to the picker's id cycle when a row has none. A row with no category keeps the
 * neutral marks: the uncategorised income ↓ in `--pos` (matching its `+` amount) and the uncategorised
 * expense ◌ in `--text-3`. The amount keeps its own `--pos` rule; the tile never decides it.
 */
export function transactionGlyphColor(type: string, category: { category_id: number | null; color: CategoryColor | null }): GlyphColor {
    if (category.category_id === null) {
        return type === 'income' ? 'pos' : 'text-3';
    }

    return category.color ?? categoryColorFromId(category.category_id);
}

/**
 * Resolve a tally's display colour. Uses the persisted `color` when set; falls back
 * to a stable id-based cycle (`c{(tally_id % 6) + 1}`) so every row has a colour.
 */
export function tallyColor(tally: { color: string | null; tally_id: number }): CategoryColor {
    if (tally.color && (CATEGORY_COLORS as readonly string[]).includes(tally.color)) {
        return tally.color as CategoryColor;
    }

    return categoryColorFromId(tally.tally_id);
}

/**
 * Resolve a tally's display glyph. Uses the persisted `glyph` when set; falls back
 * to the first letter of the tally name.
 */
export function tallyGlyph(tally: { glyph: string | null; name: string }): string {
    return tally.glyph ?? tally.name.charAt(0).toUpperCase();
}
