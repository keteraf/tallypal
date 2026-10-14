/**
 * Base look of a filled form control (`.fld input/select` in the design source):
 * `--surface-2` fill, 14px radius, 15px text, accent focus border. `Input` composes
 * from it, and non-input controls that must sit in the same field row — the Add-tx
 * date popover trigger, for one — reuse it instead of restating the values.
 * `SelectTrigger` / `Textarea` still carry their own copies; folding those in belongs
 * to the Phase 5 hardcoded-value sweep.
 */
export const filledFieldClass =
  'w-full rounded-[14px] border border-transparent bg-surface-2 px-[15px] py-[14px] text-[15px] text-text outline-none transition focus:border-accent'
