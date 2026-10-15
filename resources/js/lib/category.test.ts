import { describe, expect, it } from 'vitest';
import { transactionGlyphColor } from './category';

describe('transactionGlyphColor', () => {
    it('tints a categorised row by its stored colour, income categories included', () => {
        expect(transactionGlyphColor('expense', { category_id: 4, color: 'c4' })).toBe('c4');
        expect(transactionGlyphColor('income', { category_id: 17, color: 'c3' })).toBe('c3');
    });

    it('falls back to the picker id cycle for a category without a stored colour', () => {
        expect(transactionGlyphColor('expense', { category_id: 8, color: null })).toBe('c3');
    });

    it('keeps the neutral marks for rows with no category: ↓ in --pos for income, ◌ in --text-3 for an expense', () => {
        expect(transactionGlyphColor('income', { category_id: null, color: null })).toBe('pos');
        expect(transactionGlyphColor('expense', { category_id: null, color: null })).toBe('text-3');
    });
});
