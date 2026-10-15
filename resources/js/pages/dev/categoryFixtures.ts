import type { FrequentCategories, PickerCategory } from '@/components/ui/category-picker';
import type { CategoryColor } from '@/lib/category';

/**
 * The 27 reference categories as the form data sends them, with the colours `ReferenceDataSeeder` stores
 * (DESIGN_TOKENS §7.1). Design-system page only: the real picker gets them from the server.
 */
const rows: [string, PickerCategory['type'], CategoryColor][] = [
    ['bills', 'expense', 'c4'],
    ['clothing', 'expense', 'c6'],
    ['dining_out', 'expense', 'c6'],
    ['education', 'expense', 'c1'],
    ['entertainment', 'expense', 'c6'],
    ['gifts_donations', 'expense', 'c5'],
    ['groceries', 'expense', 'c4'],
    ['healthcare', 'expense', 'c3'],
    ['household_supplies', 'expense', 'c4'],
    ['insurance', 'expense', 'c1'],
    ['personal_care', 'expense', 'c6'],
    ['rent', 'expense', 'c1'],
    ['subscriptions', 'expense', 'c2'],
    ['transportation', 'expense', 'c2'],
    ['travel', 'expense', 'c2'],
    ['utilities', 'expense', 'c5'],
    ['salary', 'income', 'c3'],
    ['side_work', 'income', 'c2'],
    ['bonus', 'income', 'c4'],
    ['investments', 'income', 'c1'],
    ['rental_income', 'income', 'c1'],
    ['refunds_returns', 'income', 'c2'],
    ['gifts_received', 'income', 'c6'],
    ['sold_items', 'income', 'c4'],
    ['pension_benefits', 'income', 'c1'],
    ['winnings', 'income', 'c5'],
    ['other_income', 'income', 'c3'],
];

export const demoCategories: PickerCategory[] = rows.map(([slug, type, color], index) => ({ category_id: index + 1, slug, type, color }));

/** `frequent_categories` for an account without history: the design defaults, as B-15 pads them. */
export const demoFrequent: FrequentCategories = {
    expense: ['groceries', 'dining_out', 'transportation', 'bills', 'subscriptions'],
    income: ['salary', 'side_work', 'refunds_returns', 'gifts_received', 'investments'],
};

export const demoCategoryId = (slug: string): number => demoCategories.find((category) => category.slug === slug)!.category_id;
