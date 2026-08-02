import { ref } from 'vue';

/**
 * Shared open-state for the "Add transaction" overlay.
 *
 * The opener is viewport-aware at render time (bottom sheet `< md`, dialog `≥ md`);
 * this composable only holds the boolean state so any trigger — the bottom-nav FAB,
 * the sidebar primary button, quick actions — can open the same overlay. The
 * full-page `tallies/{tally}/transactions/create` route remains as a deep-link
 * fallback. Module-level ref = a singleton shared across every caller. SSR-safe:
 * the state is only ever mutated by client interactions, so it stays closed during
 * server rendering.
 */
const isOpen = ref(false);

export function useAddTransaction() {
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
