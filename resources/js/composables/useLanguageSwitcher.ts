import { useI18n } from 'vue-i18n';

export const languages = [
    { code: 'en', name: 'English' },
    { code: 'it', name: 'Italiano' },
];

export function useLanguageSwitcher() {
    const { locale } = useI18n();

    const setLocale = (code: string) => {
        locale.value = code;
        localStorage.setItem('locale', code);
    };

    return { languages, locale, setLocale };
}
