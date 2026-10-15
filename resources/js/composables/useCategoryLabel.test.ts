import i18n from '@/i18n';
import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it } from 'vitest';
import { defineComponent } from 'vue';
import { useCategoryLabel } from './useCategoryLabel';

type Category = { slug: string | null; name: string | null };

function labelOf(category: Category): string {
    let label = '';

    mount(
        defineComponent({
            setup() {
                label = useCategoryLabel()(category);

                return () => null;
            },
        }),
        { global: { plugins: [i18n] } },
    );

    return label;
}

describe('useCategoryLabel', () => {
    afterEach(() => {
        i18n.global.locale.value = 'en';
    });

    it('reads the i18n label by slug, in the active language', () => {
        expect(labelOf({ slug: 'salary', name: 'Salary' })).toBe('Salary');

        i18n.global.locale.value = 'it';

        expect(labelOf({ slug: 'salary', name: 'Salary' })).toBe('Stipendio');
    });

    it('says Uncategorised for a row with no category, never the raw type', () => {
        expect(labelOf({ slug: null, name: null })).toBe('Uncategorised');

        i18n.global.locale.value = 'it';

        expect(labelOf({ slug: null, name: null })).toBe('Senza categoria');
    });

    it('falls back to the stored name for a slug the catalogue does not know', () => {
        expect(labelOf({ slug: 'housing_17', name: 'Housing' })).toBe('Housing');
        expect(labelOf({ slug: 'housing_17', name: null })).toBe('Uncategorised');
    });
});
