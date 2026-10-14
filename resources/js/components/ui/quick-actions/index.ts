import type { Component } from "vue"

export { default as QuickActions } from "./QuickActions.vue"

export interface QuickAction {
  label: string
  /** Secondary line, shown beside the title from `sm` up in the `card` variant. */
  sub?: string
  /** Lucide (or any) icon component rendered inside the circle. */
  icon?: Component
  /** Highlight the icon circle with the accent colour. */
  primary?: boolean
  onClick?: () => void
}
