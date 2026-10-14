export interface DataTableColumn {
  key: string
  label: string
  /** `right` right-aligns the column and sizes it to its content (amount). */
  align?: "left" | "right"
  /** Drops the column at or below 1100px (`xl`). */
  hideBelow?: "xl"
}
