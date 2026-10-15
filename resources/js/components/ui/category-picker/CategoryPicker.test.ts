import i18n from '@/i18n';
import { mount, type VueWrapper } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import type { PickerCategory } from './pickerLogic';
import CategoryPicker from './CategoryPicker.vue';

const categories: PickerCategory[] = [
    ['bills', 'c4'],
    ['clothing', 'c6'],
    ['dining_out', 'c6'],
    ['groceries', 'c4'],
    ['rent', 'c1'],
    ['subscriptions', 'c2'],
    ['transportation', 'c2'],
    ['travel', 'c2'],
].map(([slug, color], i) => ({ category_id: i + 1, slug, type: 'expense' as const, color: color as PickerCategory['color'] }));
categories.push(
    ...['salary', 'side_work', 'refunds_returns', 'gifts_received', 'investments', 'sold_items'].map((slug, i) => ({
        category_id: i + 17,
        slug,
        type: 'income' as const,
        color: 'c3' as const,
    })),
);
const idOf = (slug: string): number => categories.find((category) => category.slug === slug)!.category_id;

let wrapper: VueWrapper | undefined;

function mountPicker(props: Record<string, unknown> = {}): VueWrapper {
    wrapper = mount(CategoryPicker, {
        props: { type: 'expense', categories, frequent: { expense: [], income: [] }, layout: 'inline', ...props },
        global: { plugins: [i18n] },
        attachTo: document.body,
    });

    return wrapper;
}

const rowLabels = (picker: VueWrapper): string[] =>
    picker.findAll('[role="radiogroup"]')[0].findAll('[data-picker-tile]').map((tile) => tile.text().replace(/[­︎]/g, ''));

afterEach(() => {
    wrapper?.unmount();
    wrapper = undefined;
    document.body.innerHTML = '';
});

describe('CategoryPicker', () => {
    it('shows five frequent tiles and "All N" inline, four on the phone layout', () => {
        expect(rowLabels(mountPicker())).toEqual(['◈Groceries', '☕Dining Out', '◎Transportation', '▤Bills', '♪Subscriptions', '⋯All 8']);
        wrapper!.unmount();
        expect(rowLabels(mountPicker({ layout: 'sheet' }))).toHaveLength(5);
    });

    it('says "No category" while nothing is picked', () => {
        expect(mountPicker().find('[role="status"]').text()).toContain('No category');
    });

    it('clears a pick of the other type on a type switch, names it, and drops the notice on the next pick', async () => {
        const picker = mountPicker({ modelValue: idOf('groceries'), 'onUpdate:modelValue': (value: number | null) => picker.setProps({ modelValue: value }) });

        await picker.setProps({ type: 'income' });

        expect(picker.emitted('update:modelValue')?.at(-1)).toEqual([null]);
        expect(picker.emitted('cleared')).toEqual([['groceries']]);
        expect(picker.find('[role="status"]').text()).toBe('◌︎Groceries is an expense category — cleared.');

        await picker.find('[data-category-id="17"]').trigger('click');

        expect(picker.emitted('update:modelValue')?.at(-1)).toEqual([17]);
        expect(picker.find('[role="status"]').text()).toBe('');
    });

    it('keeps a pick that belongs to the new type', async () => {
        const picker = mountPicker({ modelValue: idOf('salary'), type: 'income' });

        await picker.setProps({ type: 'income' });

        expect(picker.emitted('cleared')).toBeUndefined();
    });

    it('opens "All" with the filter focused; Enter in the filter picks the first match and focuses it in the row', async () => {
        const picker = mountPicker();

        await picker.find('[data-picker-all]').trigger('click');
        await nextTick();

        const filter = picker.find('input');
        expect(document.activeElement).toBe(filter.element);

        await filter.setValue('vestiti');
        await filter.trigger('keydown', { key: 'Enter' });
        await nextTick();

        expect(picker.emitted('update:modelValue')?.at(-1)).toEqual([idOf('clothing')]);
        expect(picker.find('input').exists()).toBe(false);
        expect(document.activeElement?.getAttribute('data-category-id')).toBe(String(idOf('clothing')));
        expect(rowLabels(picker)[0]).toBe('✂Clothing');
    });

    it('shows the no-match line and swallows Enter so the form is not submitted', async () => {
        const picker = mountPicker({ open: true, query: 'zzz' });
        const enter = new KeyboardEvent('keydown', { key: 'Enter', cancelable: true, bubbles: true });

        picker.find('input').element.dispatchEvent(enter);

        expect(picker.text()).toContain('No match for “zzz”');
        expect(enter.defaultPrevented).toBe(true);
        expect(picker.emitted('update:modelValue')).toBeUndefined();
    });

    it('closes on Esc, returns focus to "All", and keeps the Esc from reaching a surrounding dialog', async () => {
        const picker = mountPicker();
        let escapesSeen = 0;
        const onWindowKeydown = (): number => escapesSeen++;
        window.addEventListener('keydown', onWindowKeydown);

        await picker.find('[data-picker-all]').trigger('click');
        await nextTick();
        await picker.find('input').trigger('keydown', { key: 'Escape' });
        await nextTick();
        window.removeEventListener('keydown', onWindowKeydown);

        expect(picker.find('input').exists()).toBe(false);
        expect(picker.emitted('update:open')?.at(-1)).toEqual([false]);
        expect(document.activeElement?.hasAttribute('data-picker-all')).toBe(true);
        expect(escapesSeen).toBe(0);
    });

    it('never focuses the filter on the phone layout', async () => {
        const picker = mountPicker({ layout: 'sheet' });

        await picker.find('[data-picker-all]').trigger('click');
        await nextTick();
        await nextTick();

        const filter = document.querySelector('input');
        expect(filter).not.toBeNull();
        expect(document.activeElement).not.toBe(filter);
        expect(document.body.textContent).toContain('Expense categories');
    });

    it('holds the phone sheet grid at its unfiltered height while a filter narrows it, and releases it when cleared', async () => {
        const height = vi.spyOn(HTMLElement.prototype, 'offsetHeight', 'get').mockReturnValue(318);
        const picker = mountPicker({ layout: 'sheet', open: true });
        await nextTick();
        const grid = (): HTMLElement => document.querySelector<HTMLElement>('[role="radiogroup"][id$="-grid"]')!;

        expect(grid().style.minHeight).toBe('');

        await picker.setProps({ query: 'tra' });
        expect(grid().style.minHeight).toBe('318px');

        height.mockReturnValue(120);
        await picker.setProps({ query: 'zzz' });
        expect(grid().style.minHeight).toBe('318px');

        await picker.setProps({ query: '' });
        expect(grid().style.minHeight).toBe('');
        height.mockRestore();
    });

    it('does not pin the inline panel height', async () => {
        vi.spyOn(HTMLElement.prototype, 'offsetHeight', 'get').mockReturnValue(318);
        const picker = mountPicker({ open: true });
        await picker.setProps({ query: 'tra' });

        expect(picker.find('[role="radiogroup"][id$="-grid"]').attributes('style') ?? '').not.toContain('min-height');
        vi.restoreAllMocks();
    });

    it('dismiss() closes the open panel first, then the cleared notice, then reports nothing left', async () => {
        const picker = mountPicker({ modelValue: idOf('groceries'), 'onUpdate:modelValue': (value: number | null) => picker.setProps({ modelValue: value }) });
        const dismiss = (): boolean => (picker.vm as unknown as { dismiss: () => boolean }).dismiss();

        expect(dismiss()).toBe(false);

        await picker.setProps({ type: 'income' });
        await picker.find('[data-picker-all]').trigger('click');
        await nextTick();

        expect(dismiss()).toBe(true);
        await nextTick();
        expect(picker.find('input').exists()).toBe(false);
        expect(picker.find('[role="status"]').text()).toContain('cleared');

        expect(dismiss()).toBe(true);
        await nextTick();
        expect(picker.find('[role="status"]').text()).toContain('No category');
        expect(dismiss()).toBe(false);
    });
});
