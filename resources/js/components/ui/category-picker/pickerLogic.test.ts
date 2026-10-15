import { describe, expect, it } from 'vitest';
import {
    categorySearchTerms,
    categoryToClear,
    filterCategories,
    filterKeyAction,
    frequentRow,
    gridKeyAction,
    normaliseSearchText,
    type PickerCategory,
    rowKeyAction,
    rowTabStop,
    withSoftHyphens,
} from './pickerLogic';

const EXPENSE_SLUGS = [
    'bills',
    'clothing',
    'dining_out',
    'education',
    'entertainment',
    'gifts_donations',
    'groceries',
    'healthcare',
    'household_supplies',
    'insurance',
    'personal_care',
    'rent',
    'subscriptions',
    'transportation',
    'travel',
    'utilities',
];
const INCOME_SLUGS = [
    'salary',
    'side_work',
    'bonus',
    'investments',
    'rental_income',
    'refunds_returns',
    'gifts_received',
    'sold_items',
    'pension_benefits',
    'winnings',
    'other_income',
];

const categories: PickerCategory[] = [
    ...EXPENSE_SLUGS.map((slug, i) => ({ category_id: i + 1, slug, type: 'expense' as const, color: 'c1' as const })),
    ...INCOME_SLUGS.map((slug, i) => ({ category_id: i + 17, slug, type: 'income' as const, color: 'c3' as const })),
];
const expense = categories.filter((category) => category.type === 'expense');
const income = categories.filter((category) => category.type === 'income');
const idOf = (slug: string): number => categories.find((category) => category.slug === slug)!.category_id;
const slugs = (list: PickerCategory[]): string[] => list.map((category) => category.slug);

describe('alias search (C-D7)', () => {
    it('normalises case, accents and surrounding space', () => {
        expect(normaliseSearchText('  Più Città  ')).toBe('piu citta');
        expect(normaliseSearchText('ÉBAY')).toBe('ebay');
    });

    it('searches the English label, the Italian label and the aliases together', () => {
        expect(categorySearchTerms('salary')).toEqual(['Salary', 'Stipendio', 'paycheck', 'busta paga']);
        expect(categorySearchTerms('sold_items')).toEqual(['Sold items', 'Vendite', 'ebay', 'vinted', 'subito', 'second hand', 'usato']);
    });

    it('has a catalogue entry for every one of the 27 categories', () => {
        for (const slug of [...EXPENSE_SLUGS, ...INCOME_SLUGS]) {
            expect(categorySearchTerms(slug).length, slug).toBeGreaterThan(2);
        }
    });

    it('matches an English label', () => {
        expect(slugs(filterCategories(expense, 'groc'))).toEqual(['groceries']);
    });

    it('matches an Italian label whatever the UI language', () => {
        expect(slugs(filterCategories(income, 'stipendio'))).toEqual(['salary']);
        expect(slugs(filterCategories(expense, 'bollette'))).toEqual(['bills']);
    });

    it('matches an alias', () => {
        expect(slugs(filterCategories(income, 'ebay'))).toEqual(['sold_items']);
        expect(slugs(filterCategories(expense, 'netflix'))).toEqual(['subscriptions']);
        expect(slugs(filterCategories(income, 'busta paga'))).toEqual(['salary']);
    });

    it('is case- and accent-insensitive on both sides', () => {
        expect(slugs(filterCategories(income, 'STIPÈNDIO'))).toEqual(['salary']);
        expect(slugs(filterCategories(expense, 'Supermércato'))).toEqual(['groceries']);
    });

    it('keeps the order of the list it filters', () => {
        // "re": fREElance, pREmi, inteREst, REntal, REfunds, REgali, welfaRE, pREmio, altRE — not Salary or Sold items.
        expect(slugs(filterCategories(income, 're'))).toEqual([
            'side_work',
            'bonus',
            'investments',
            'rental_income',
            'refunds_returns',
            'gifts_received',
            'pension_benefits',
            'winnings',
            'other_income',
        ]);
    });

    it('returns the whole list for an empty or blank query', () => {
        expect(filterCategories(expense, '')).toEqual(expense);
        expect(filterCategories(expense, '   ')).toEqual(expense);
    });

    it('returns nothing when no term matches', () => {
        expect(filterCategories(expense, 'zzz')).toEqual([]);
    });

    it('never matches a category of the other type', () => {
        expect(filterCategories(expense, 'stipendio')).toEqual([]);
    });
});

describe('frequent row', () => {
    const available = (list: PickerCategory[]): string[] => slugs(list);

    it('takes the server list: five on desktop, four on phones', () => {
        const frequent = ['travel', 'rent', 'groceries', 'utilities', 'clothing'];

        expect(frequentRow({ frequent, type: 'expense', available: available(expense), selected: null, size: 5 })).toEqual(frequent);
        expect(frequentRow({ frequent, type: 'expense', available: available(expense), selected: null, size: 4 })).toEqual(frequent.slice(0, 4));
    });

    it('pads a short list with the design defaults, without duplicating them', () => {
        expect(frequentRow({ frequent: ['travel', 'groceries'], type: 'expense', available: available(expense), selected: null, size: 5 })).toEqual([
            'travel',
            'groceries',
            'dining_out',
            'transportation',
            'bills',
        ]);
    });

    it('falls back to the defaults of the type when the server sends none', () => {
        expect(frequentRow({ frequent: [], type: 'expense', available: available(expense), selected: null, size: 5 })).toEqual([
            'groceries',
            'dining_out',
            'transportation',
            'bills',
            'subscriptions',
        ]);
        expect(frequentRow({ frequent: [], type: 'income', available: available(income), selected: null, size: 4 })).toEqual([
            'salary',
            'side_work',
            'refunds_returns',
            'gifts_received',
        ]);
    });

    it('drops duplicates and slugs that are not categories of the type', () => {
        expect(
            frequentRow({ frequent: ['salary', 'salary', 'groceries', 'nope'], type: 'income', available: available(income), selected: null, size: 5 }),
        ).toEqual(['salary', 'side_work', 'refunds_returns', 'gifts_received', 'investments']);
    });

    it('pins a selected category that is not in the row to the first slot', () => {
        expect(frequentRow({ frequent: [], type: 'income', available: available(income), selected: 'sold_items', size: 5 })).toEqual([
            'sold_items',
            'salary',
            'side_work',
            'refunds_returns',
            'gifts_received',
        ]);
    });

    it('leaves the row alone when the selected category is already in it', () => {
        expect(frequentRow({ frequent: [], type: 'expense', available: available(expense), selected: 'bills', size: 5 })).toEqual([
            'groceries',
            'dining_out',
            'transportation',
            'bills',
            'subscriptions',
        ]);
    });

    it('pins by row size: fifth on desktop is pinned first on a phone', () => {
        expect(frequentRow({ frequent: [], type: 'expense', available: available(expense), selected: 'subscriptions', size: 4 })).toEqual([
            'subscriptions',
            'groceries',
            'dining_out',
            'transportation',
        ]);
    });

    it('never pins a selected category of the other type', () => {
        expect(frequentRow({ frequent: [], type: 'income', available: available(income), selected: 'travel', size: 4 })).toEqual([
            'salary',
            'side_work',
            'refunds_returns',
            'gifts_received',
        ]);
    });
});

describe('clear rule', () => {
    it('clears a selection that does not belong to the new type, naming it', () => {
        expect(categoryToClear(idOf('groceries'), 'income', categories)?.slug).toBe('groceries');
        expect(categoryToClear(idOf('salary'), 'expense', categories)?.slug).toBe('salary');
    });

    it('keeps a selection of the new type', () => {
        expect(categoryToClear(idOf('salary'), 'income', categories)).toBeNull();
        expect(categoryToClear(idOf('groceries'), 'expense', categories)).toBeNull();
    });

    it('has nothing to clear without a selection', () => {
        expect(categoryToClear(null, 'income', categories)).toBeNull();
    });

    it('leaves an id it does not know alone', () => {
        expect(categoryToClear(999, 'income', categories)).toBeNull();
    });
});

describe('keyboard model', () => {
    describe('row: a radio group, roving tabindex', () => {
        it('→ and ← move one tile and wrap around', () => {
            expect(rowKeyAction('ArrowRight', 0, 6)).toEqual({ kind: 'focus', index: 1 });
            expect(rowKeyAction('ArrowRight', 5, 6)).toEqual({ kind: 'focus', index: 0 });
            expect(rowKeyAction('ArrowLeft', 3, 6)).toEqual({ kind: 'focus', index: 2 });
            expect(rowKeyAction('ArrowLeft', 0, 6)).toEqual({ kind: 'focus', index: 5 });
        });

        it('Home and End jump to the first tile and to "All"', () => {
            expect(rowKeyAction('Home', 4, 6)).toEqual({ kind: 'focus', index: 0 });
            expect(rowKeyAction('End', 1, 5)).toEqual({ kind: 'focus', index: 4 });
        });

        it('leaves every other key to the browser (Space/Enter click the tile)', () => {
            for (const key of ['ArrowUp', 'ArrowDown', 'Enter', ' ', 'Escape', 'Tab', 'a']) {
                expect(rowKeyAction(key, 2, 6), key).toBeNull();
            }
        });

        it('puts the tab stop on the selected tile, else on "All"', () => {
            expect(rowTabStop(['groceries', 'bills', 'rent'], 'bills')).toBe(1);
            expect(rowTabStop(['groceries', 'bills', 'rent'], null)).toBe(3);
            expect(rowTabStop(['groceries', 'bills', 'rent'], 'travel')).toBe(3);
        });
    });

    describe('grid: four columns, no wrap', () => {
        it('→ and ← step one tile and stop at the ends', () => {
            expect(gridKeyAction('ArrowRight', 2, 16)).toEqual({ kind: 'focus', index: 3 });
            expect(gridKeyAction('ArrowRight', 15, 16)).toEqual({ kind: 'focus', index: 15 });
            expect(gridKeyAction('ArrowLeft', 4, 16)).toEqual({ kind: 'focus', index: 3 });
            expect(gridKeyAction('ArrowLeft', 0, 16)).toEqual({ kind: 'focus', index: 0 });
        });

        it('↓ steps a row of four, stopping on the last tile', () => {
            expect(gridKeyAction('ArrowDown', 1, 16)).toEqual({ kind: 'focus', index: 5 });
            expect(gridKeyAction('ArrowDown', 13, 16)).toEqual({ kind: 'focus', index: 15 });
            expect(gridKeyAction('ArrowDown', 9, 11)).toEqual({ kind: 'focus', index: 10 });
        });

        it('↑ steps a row of four, and from the first row returns to the filter', () => {
            expect(gridKeyAction('ArrowUp', 9, 16)).toEqual({ kind: 'focus', index: 5 });
            expect(gridKeyAction('ArrowUp', 4, 16)).toEqual({ kind: 'focus', index: 0 });
            expect(gridKeyAction('ArrowUp', 3, 16)).toEqual({ kind: 'focus-filter' });
            expect(gridKeyAction('ArrowUp', 0, 16)).toEqual({ kind: 'focus-filter' });
        });

        it('Esc closes', () => {
            expect(gridKeyAction('Escape', 7, 16)).toEqual({ kind: 'close' });
        });

        it('leaves Enter, Space, Home and Tab to the browser', () => {
            for (const key of ['Enter', ' ', 'Home', 'End', 'Tab']) {
                expect(gridKeyAction(key, 2, 16), key).toBeNull();
            }
        });
    });

    describe('filter', () => {
        it('↓ moves to the first tile', () => {
            expect(filterKeyAction('ArrowDown', 3)).toEqual({ kind: 'focus', index: 0 });
        });

        it('↓ stays put when nothing matches', () => {
            expect(filterKeyAction('ArrowDown', 0)).toEqual({ kind: 'stay' });
        });

        it('Enter picks the first match', () => {
            expect(filterKeyAction('Enter', 2)).toEqual({ kind: 'pick-first' });
        });

        it('Enter with no match is swallowed, so it never submits the form', () => {
            expect(filterKeyAction('Enter', 0)).toEqual({ kind: 'stay' });
        });

        it('Esc closes', () => {
            expect(filterKeyAction('Escape', 4)).toEqual({ kind: 'close' });
        });

        it('leaves typing and caret keys to the input', () => {
            for (const key of ['a', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'Home', 'Backspace', 'Tab']) {
                expect(filterKeyAction(key, 4), key).toBeNull();
            }
        });
    });
});

describe('soft hyphens', () => {
    it('breaks the long words the design breaks, in both languages', () => {
        expect(withSoftHyphens('Transportation')).toBe('Transpor­tation');
        expect(withSoftHyphens('Abbonamenti')).toBe('Abbona­menti');
    });

    it('works word by word and leaves short words alone', () => {
        expect(withSoftHyphens('Prodotti per la casa')).toBe('Prodotti per la casa');
        expect(withSoftHyphens('Healthcare plan')).toBe('Health­care plan');
    });
});
