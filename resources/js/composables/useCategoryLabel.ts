import { useI18n } from 'vue-i18n';

/**
 * Display label of a transaction row's category: the i18n label by slug (C-D3), the stored name for a
 * category the catalogue does not know, and "Uncategorised" for a row with none. Never the raw type.
 */
export function useCategoryLabel() {
    const { t, te } = useI18n();

    return (category: { slug: string | null; name: string | null }): string => {
        if (category.slug === null) {
            return t('categories.uncategorised');
        }

        return te(`categories.${category.slug}`) ? t(`categories.${category.slug}`) : (category.name ?? t('categories.uncategorised'));
    };
}
