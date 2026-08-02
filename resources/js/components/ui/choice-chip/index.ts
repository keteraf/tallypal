import type { CategoryColor } from "@/lib/category"

export { default as ChoiceChip } from "./ChoiceChip.vue"
export { default as ChipGroup } from "./ChipGroup.vue"

export interface ChipOption<V extends string | number = string | number> {
  value: V
  label?: string
  color?: CategoryColor
  glyph?: string
}
