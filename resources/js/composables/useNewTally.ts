import { ref } from 'vue';

/**
 * Shared open-state for the "New tally" overlay.
 *
 * Mirrors {@link useAddTransaction}: a singleton boolean any trigger can flip
 * (sidebar/header button, empty states). The overlay renders as a bottom sheet
 * `< md` and a dialog `≥ md`; the full-page `tallies/create` route stays as a
 * deep-link fallback. SSR-safe — only mutated by client interactions.
 */
const isOpen = ref(false);

export function useNewTally() {
    return {
        isOpen,
        open: (): void => {
            isOpen.value = true;
        },
        close: (): void => {
            isOpen.value = false;
        },
    };
}
