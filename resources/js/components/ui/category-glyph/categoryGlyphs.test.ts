import { describe, expect, it } from 'vitest';
import { asTextGlyph, categoryGlyph, FALLBACK_GLYPH, INCOME_GLYPH } from './categoryGlyphs';

describe('asTextGlyph', () => {
    it('ends every glyph on exactly one text-presentation selector', () => {
        expect(asTextGlyph('✈')).toBe('✈︎');
        expect(asTextGlyph('✈️')).toBe('✈︎');
        expect(asTextGlyph('✈︎')).toBe('✈︎');
    });

    it('passes an empty or missing glyph through', () => {
        expect(asTextGlyph('')).toBe('');
        expect(asTextGlyph(undefined)).toBeUndefined();
    });
});

describe('categoryGlyph', () => {
    it('maps a known slug whatever the type', () => {
        expect(categoryGlyph('salary', 'income')).toBe('¤');
        expect(categoryGlyph('groceries', 'expense')).toBe('◈');
    });

    it('draws ↓ only for an uncategorised income', () => {
        expect(categoryGlyph(null, 'income')).toBe(INCOME_GLYPH);
        expect(categoryGlyph('unknown_slug', 'income')).toBe(FALLBACK_GLYPH);
        expect(categoryGlyph(null, 'expense')).toBe(FALLBACK_GLYPH);
    });
});
