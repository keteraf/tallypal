import { createI18n } from 'vue-i18n';
import en from './locales/en';
import it from './locales/it';

const i18n = createI18n({
    legacy: false, // Set to false to use Composition API
    locale: localStorage.getItem('locale') || 'en',
    fallbackLocale: 'en',
    globalInjection: true, // Allow $t in templates
    messages: {
        en,
        it,
    },
});

export default i18n;
