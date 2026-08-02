export { default as CheckList } from "./CheckList.vue"
export { default as CheckListItem } from "./CheckListItem.vue"

export interface CheckListItemData {
  label: string
  /** @default "on" */
  state?: "on" | "off"
}
