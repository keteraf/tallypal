<script setup lang="ts" generic="T extends Record<string, unknown>">
import type { DataTableColumn } from "./types"
import { Link } from "@inertiajs/vue3"
import { computed } from "vue"

const props = defineProps<{
  columns: DataTableColumn[]
  rows: T[]
  /** Makes every row a link. */
  rowHref?: (row: T) => string
}>()

const track = (column: DataTableColumn, index: number): string => {
  if (column.align === "right") {
    return "auto"
  }

  return index === 0 ? "minmax(0,2fr)" : "minmax(0,1fr)"
}

const gridStyle = computed(() => ({
  "--dt-full": props.columns.map(track).join(" "),
  "--dt-compact": props.columns.filter((column) => !column.hideBelow).map((column) => track(column, props.columns.indexOf(column))).join(" "),
}))

const headClass = (column: DataTableColumn): string[] => [
  "border-b border-line pb-2.5 text-[11px] font-semibold tracking-[0.06em] text-text-3 uppercase max-[640px]:hidden",
  column.align === "right" ? "text-right" : "",
  column.hideBelow ? "hidden min-[1101px]:block" : "",
].filter(Boolean)

const cellClass = (column: DataTableColumn, index: number): string[] => [
  "flex min-w-0 items-center border-b border-line py-3 group-last/row:border-b-0 group-hover/row:bg-surface-2",
  "max-[640px]:border-0 max-[640px]:p-0 max-[640px]:group-hover/row:bg-transparent",
  index === 0 ? "max-[640px]:min-w-0 max-[640px]:flex-1" : "",
  column.align === "right" ? "justify-end text-right max-[640px]:shrink-0" : "",
  index > 0 && column.align !== "right" ? "text-[13px] text-text-2 max-[640px]:hidden" : "",
  column.hideBelow ? "hidden min-[1101px]:flex" : "",
]
</script>

<template>
  <div
    role="table"
    class="grid gap-x-4 max-[640px]:block [grid-template-columns:var(--dt-compact)] min-[1101px]:[grid-template-columns:var(--dt-full)]"
    :style="gridStyle"
  >
    <div role="row" class="contents">
      <div v-for="(column, index) in columns" :key="column.key" role="columnheader" :class="headClass(column)">
        {{ column.label }}
      </div>
    </div>
    <component
      :is="rowHref ? Link : 'div'"
      v-for="(row, rowIndex) in rows"
      :key="rowIndex"
      :href="rowHref?.(row)"
      role="row"
      class="group/row contents max-[640px]:flex max-[640px]:items-center max-[640px]:gap-3 max-[640px]:border-b max-[640px]:border-line max-[640px]:py-3 max-[640px]:last:border-b-0"
    >
      <div v-for="(column, index) in columns" :key="column.key" role="cell" :class="cellClass(column, index)">
        <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">{{ row[column.key] }}</slot>
      </div>
    </component>
  </div>
</template>
