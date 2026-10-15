import type { PickerCategoryType } from './pickerLogic';

/**
 * Search aliases by category slug (owner decision C-D7: a frontend catalogue, not a DB column). The
 * picker filter matches them together with the English and Italian labels. Source: VERIFICATION.md,
 * Categories batch (both tables), copied from `Tallypal Categories.html`.
 */
export const CATEGORY_ALIASES: Readonly<Record<string, readonly string[]>> = {
    bills: ['bolletta'],
    clothing: ['vestiti'],
    dining_out: ['restaurant', 'bar', 'pizza'],
    education: ['scuola', 'corso'],
    entertainment: ['cinema', 'concerto'],
    gifts_donations: ['charity'],
    groceries: ['supermercato'],
    healthcare: ['farmacia', 'medico', 'pharmacy'],
    household_supplies: ['pulizie', 'cleaning'],
    insurance: ['polizza'],
    personal_care: ['parrucchiere'],
    rent: ['canone'],
    subscriptions: ['netflix', 'spotify'],
    transportation: ['taxi', 'metro', 'benzina', 'fuel'],
    travel: ['volo', 'hotel', 'flight'],
    utilities: ['luce', 'gas', 'acqua'],
    salary: ['paycheck', 'busta paga'],
    side_work: ['freelance', 'invoice', 'fattura', 'lavoretti'],
    bonus: ['commission', 'provvigioni', 'premio', 'tredicesima'],
    investments: ['dividends', 'interest', 'crypto', 'dividendi', 'interessi'],
    rental_income: ['tenant', 'inquilino', 'canone'],
    refunds_returns: ['rimborso', 'return', 'reso', 'cashback', 'cancellation', 'storno'],
    gifts_received: ['regalo', 'present', 'birthday', 'compleanno'],
    sold_items: ['ebay', 'vinted', 'subito', 'second hand', 'usato'],
    pension_benefits: ['inps', 'naspi', 'welfare', 'sussidio'],
    winnings: ['lottery', 'lotteria', 'bet', 'scommessa', 'prize', 'premio'],
    other_income: ['altro', 'misc'],
};

/**
 * The design's frequent row before there is data (B-15 pads the server list with the same five,
 * `CategoryType::defaultSlugs()`); the picker pads again so a short or empty list still fills the row.
 */
export const DEFAULT_FREQUENT: Readonly<Record<PickerCategoryType, readonly string[]>> = {
    expense: ['groceries', 'dining_out', 'transportation', 'bills', 'subscriptions'],
    income: ['salary', 'side_work', 'refunds_returns', 'gifts_received', 'investments'],
};

/** Break points for the long label words, exactly where the design puts its soft hyphens. */
export const SOFT_HYPHENATED: Readonly<Record<string, string>> = {
    Transportation: 'Transpor­tation',
    Subscriptions: 'Subscrip­tions',
    Entertainment: 'Entertain­ment',
    Investments: 'Invest­ments',
    Healthcare: 'Health­care',
    Abbigliamento: 'Abbiglia­mento',
    Assicurazioni: 'Assicura­zioni',
    Investimenti: 'Investi­menti',
    Abbonamenti: 'Abbona­menti',
    Ristoranti: 'Risto­ranti',
    Istruzione: 'Istru­zione',
    Stipendio: 'Sti­pendio',
};
