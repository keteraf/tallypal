<script setup lang="ts">
import { Button } from "@/components/ui/button"
import { asTextGlyph, categoryGlyph, FALLBACK_GLYPH } from "@/components/ui/category-glyph"
import { useFormFieldAria } from "@/components/ui/form-field/context"
import { Input } from "@/components/ui/input"
import { BottomSheet } from "@/components/ui/sheet"
import { categoryColorFromId } from "@/lib/category"
import { createReusableTemplate, useMediaQuery } from "@vueuse/core"
import { Search, X } from "lucide-vue-next"
import { computed, nextTick, ref, useId, watch } from "vue"
import { useI18n } from "vue-i18n"
import {
  categoryToClear,
  filterCategories,
  filterKeyAction,
  type FrequentCategories,
  frequentRow,
  gridKeyAction,
  type PickerCategory,
  type PickerCategoryType,
  type PickerKeyAction,
  ROW_SIZE,
  rowKeyAction,
  rowTabStop,
  withSoftHyphens,
} from "./pickerLogic"
import CategoryTile from "./CategoryTile.vue"

/**
 * Category picker (COMPONENT_INVENTORY E7, `Tallypal Categories.html` direction A + B's grid + C's filter):
 * a radio-group row of frequent tiles plus "All N", a status line, and the full set behind "All" — an
 * inline panel ≥ md, a second bottom sheet (80 %) < md whose filter never takes the keyboard unless tapped.
 * The rules live in `pickerLogic.ts`. `layout` forces one shape (design-system frames); `auto` follows md.
 */
const props = withDefaults(
  defineProps<{
    type: PickerCategoryType
    categories: readonly PickerCategory[]
    frequent: FrequentCategories
    layout?: "auto" | "inline" | "sheet"
  }>(),
  { layout: "auto" },
)

const emit = defineEmits<{
  /** A selection of the other type was dropped (type switch): its slug. */
  cleared: [slug: string]
}>()

/** Selected `category_id`; `null` = no category (optional, C-D1). */
const model = defineModel<number | null>({ default: null })
const open = defineModel<boolean>("open", { default: false })
const query = defineModel<string>("query", { default: "" })

const { t } = useI18n()
const { ariaInvalid, ariaDescribedby } = useFormFieldAria()
const [DefineBody, ReuseBody] = createReusableTemplate()

const isDesktop = useMediaQuery("(min-width: 768px)")
const usesSheet = computed(() => props.layout === "sheet" || (props.layout === "auto" && !isDesktop.value))

const panelId = useId()
const rowRef = ref<HTMLElement | null>(null)
const gridRef = ref<HTMLElement | null>(null)
const filterRef = ref<{ $el: HTMLInputElement } | null>(null)

const typeCategories = computed(() => props.categories.filter((category) => category.type === props.type))
const selected = computed(() => props.categories.find((category) => category.category_id === model.value) ?? null)
const row = computed(() => {
  const bySlug = new Map(typeCategories.value.map((category) => [category.slug, category]))

  return frequentRow({
    frequent: props.frequent[props.type],
    type: props.type,
    available: [...bySlug.keys()],
    selected: selected.value?.slug ?? null,
    size: usesSheet.value ? ROW_SIZE.phone : ROW_SIZE.desktop,
  }).map((slug) => bySlug.get(slug)!)
})
const tabStop = computed(() =>
  rowTabStop(
    row.value.map((category) => category.slug),
    selected.value?.slug ?? null,
  ),
)
const matches = computed(() => filterCategories(typeCategories.value, query.value))

const clearedSlug = ref<string | null>(null)
/** Re-keys the notice so the nudge replays on every clear. */
const clearCount = ref(0)
/** Where focus goes when the panel / sheet closes: the picked tile, else "All". */
const returnFocusTo = ref<number | null>(null)

const label = (category: PickerCategory): string => t(`categories.${category.slug}`)
const tileLabel = (category: PickerCategory): string => withSoftHyphens(label(category))
const tileColor = (category: PickerCategory) => category.color ?? categoryColorFromId(category.category_id)
const typeName = computed(() => t(`category_picker.type.${props.type}`))
const searchLabel = computed(() => t("category_picker.search", { count: typeCategories.value.length, type: typeName.value }))
const sheetTitle = computed(() => t(`category_picker.sheet_title.${props.type}`))
const clearedCategory = computed(() => props.categories.find((category) => category.slug === clearedSlug.value) ?? null)

watch(
  [() => props.type, model, () => props.categories],
  () => {
    const dropped = categoryToClear(model.value, props.type, props.categories)

    if (dropped !== null) {
      clearedSlug.value = dropped.slug
      clearCount.value++
      model.value = null
      emit("cleared", dropped.slug)
    }
  },
  { immediate: true },
)

watch(model, (value) => {
  if (value !== null) {
    clearedSlug.value = null
  }
})

watch(
  () => props.type,
  () => {
    open.value = false
  },
)

watch(open, (isOpen) => {
  if (!isOpen) {
    query.value = ""
  }
})

/**
 * Phone sheet: the grid's height with the whole list showing, held while a filter narrows it, so the sheet
 * keeps its size through every match count (and the no-match line) instead of shrinking under the thumb.
 */
const gridMinHeight = ref<string | undefined>(undefined)

watch(
  query,
  (value, previous) => {
    if (value === "") {
      gridMinHeight.value = undefined
    } else if (previous === "" && usesSheet.value && gridRef.value) {
      gridMinHeight.value = `${gridRef.value.offsetHeight}px`
    }
  },
  { flush: "pre" },
)

const tiles = (container: HTMLElement | null): HTMLElement[] => [...(container?.querySelectorAll<HTMLElement>("[data-picker-tile]") ?? [])]

function focusReturnTarget(): void {
  const target =
    returnFocusTo.value === null
      ? rowRef.value?.querySelector<HTMLElement>("[data-picker-all]")
      : rowRef.value?.querySelector<HTMLElement>(`[data-category-id="${returnFocusTo.value}"]`)

  target?.focus({ preventScroll: true })
}

function closePanel(focusTo: number | null): void {
  returnFocusTo.value = focusTo
  open.value = false

  // The sheet hands focus back in its close-auto-focus hook, once it has unmounted.
  if (!usesSheet.value) {
    nextTick(focusReturnTarget)
  }
}

function pick(category: PickerCategory): void {
  model.value = category.category_id
  clearedSlug.value = null

  if (open.value) {
    closePanel(category.category_id)
  }
}

/**
 * Closes the topmost thing the picker is showing — the open panel, else the "cleared" notice — and says
 * whether it did, so a surrounding form can leave Esc for its dialog when there was nothing to close.
 */
function dismiss(): boolean {
  if (open.value) {
    closePanel(null)

    return true
  }

  if (clearedSlug.value !== null) {
    clearedSlug.value = null

    return true
  }

  return false
}

defineExpose({ dismiss })

function toggleAll(): void {
  returnFocusTo.value = null
  open.value = !open.value

  // Desktop only: on a phone the filter waits for a tap, so the keyboard never pops unasked.
  if (open.value && !usesSheet.value) {
    nextTick(() => filterRef.value?.$el.focus())
  }
}

function onSheetCloseAutoFocus(event: Event): void {
  event.preventDefault()
  focusReturnTarget()
}

function run(event: KeyboardEvent, action: PickerKeyAction | null, focusables: HTMLElement[]): void {
  if (action === null) {
    return
  }

  // Handled keys never reach the surrounding dialog / form (Esc would close it, Enter would submit it).
  event.preventDefault()
  event.stopPropagation()

  switch (action.kind) {
    case "focus":
      focusables[action.index]?.focus()
      break
    case "focus-filter":
      filterRef.value?.$el.focus()
      break
    case "pick-first":
      pick(matches.value[0])
      break
    case "close":
      closePanel(null)
      break
  }
}

function onRowKeydown(event: KeyboardEvent): void {
  const focusables = tiles(rowRef.value)
  const index = focusables.indexOf(event.target as HTMLElement)

  if (index !== -1) {
    run(event, rowKeyAction(event.key, index, focusables.length), focusables)
  }
}

function onGridKeydown(event: KeyboardEvent): void {
  const focusables = tiles(gridRef.value)
  const index = focusables.indexOf(event.target as HTMLElement)

  if (index !== -1) {
    run(event, gridKeyAction(event.key, index, focusables.length), focusables)
  }
}

function onFilterKeydown(event: KeyboardEvent): void {
  run(event, filterKeyAction(event.key, matches.value.length), tiles(gridRef.value))
}
</script>

<template>
  <div data-slot="category-picker">
    <DefineBody>
      <div :class="['flex items-center gap-2', usesSheet ? 'mb-3' : 'mb-2.5']">
        <label
          class="flex h-[42px] min-w-0 flex-1 cursor-text items-center gap-[9px] rounded-[var(--radius)] border border-transparent bg-surface-2 px-[13px] text-text-3 transition focus-within:border-accent"
        >
          <Search class="size-[15px] shrink-0" aria-hidden="true" />
          <Input
            ref="filterRef"
            v-model="query"
            variant="filled"
            autocomplete="off"
            enterkeyhint="search"
            :placeholder="searchLabel"
            :aria-label="searchLabel"
            :aria-controls="`${panelId}-grid`"
            class="h-auto min-w-0 flex-1 rounded-none border-0 bg-transparent p-0 text-[14px] focus:border-0"
            @keydown="onFilterKeydown"
          />
          <kbd v-if="!usesSheet" class="rounded-[5px] border border-line px-[5px] py-px font-mono text-[10.5px]" aria-hidden="true">esc</kbd>
        </label>
        <Button variant="surface" size="icon-lg" class="shrink-0 text-text-2" :aria-label="t('common.close')" @click="closePanel(null)">
          <X class="size-[15px]" />
        </Button>
      </div>

      <div :id="`${panelId}-grid`" ref="gridRef" role="radiogroup" :aria-label="sheetTitle" class="grid grid-cols-4 content-start gap-1.5" :style="{ minHeight: gridMinHeight }" @keydown="onGridKeydown">
        <CategoryTile
          v-for="category in matches"
          :key="category.category_id"
          data-picker-tile
          role="radio"
          :aria-checked="category.category_id === model"
          :tabindex="category.category_id === model ? 0 : -1"
          :glyph="categoryGlyph(category.slug, category.type)"
          :label="tileLabel(category)"
          :color="tileColor(category)"
          :selected="category.category_id === model"
          :tall="usesSheet"
          @click="pick(category)"
        />
        <p v-if="matches.length === 0" class="col-span-full py-[22px] text-center text-[13px] text-text-3">
          {{ t("category_picker.no_match", { query }) }}
        </p>
      </div>
    </DefineBody>

    <div ref="rowRef" role="radiogroup" :aria-label="t('transactions.fields.category')" :aria-invalid="ariaInvalid" :aria-describedby="ariaDescribedby" :class="['grid gap-1.5 aria-invalid:rounded-[var(--radius)] aria-invalid:outline-1 aria-invalid:outline-offset-4 aria-invalid:outline-neg aria-invalid:outline-solid', usesSheet ? 'grid-cols-5' : 'grid-cols-6']" @keydown="onRowKeydown">
      <CategoryTile
        v-for="(category, index) in row"
        :key="category.category_id"
        data-picker-tile
        :data-category-id="category.category_id"
        role="radio"
        :aria-checked="category.category_id === model"
        :tabindex="index === tabStop ? 0 : -1"
        :glyph="categoryGlyph(category.slug, category.type)"
        :label="tileLabel(category)"
        :color="tileColor(category)"
        :selected="category.category_id === model"
        @click="pick(category)"
      />
      <CategoryTile
        data-picker-tile
        data-picker-all
        variant="all"
        glyph="⋯"
        :label="t('category_picker.all', { count: typeCategories.length })"
        :open="open"
        :tabindex="tabStop === row.length ? 0 : -1"
        :aria-expanded="open"
        :aria-controls="usesSheet ? undefined : panelId"
        :aria-haspopup="usesSheet ? 'dialog' : undefined"
        @click="toggleAll"
      />
    </div>

    <div role="status">
      <p
        v-if="clearedCategory"
        :key="clearCount"
        class="mt-[9px] flex min-h-[17px] animate-nudge items-center gap-1.5 text-xs leading-[1.4] text-neg motion-reduce:animate-none"
      >
        <span aria-hidden="true">{{ asTextGlyph(FALLBACK_GLYPH) }}</span>
        <i18n-t keypath="category_picker.cleared" tag="span" scope="global">
          <template #name><b class="font-semibold">{{ label(clearedCategory) }}</b></template>
          <template #type>{{ t(`category_picker.type.${clearedCategory.type}`) }}</template>
        </i18n-t>
      </p>
      <p v-else-if="model === null" class="mt-[9px] flex min-h-[17px] items-center gap-1.5 text-xs leading-[1.4] text-text-3">
        <span aria-hidden="true">{{ asTextGlyph(FALLBACK_GLYPH) }}</span>{{ t("category_picker.none") }}
      </p>
    </div>

    <div v-if="!usesSheet && open" :id="panelId" class="mt-2.5 animate-drop rounded-card border border-line p-2.5 motion-reduce:animate-none">
      <ReuseBody />
    </div>

    <BottomSheet
      v-if="usesSheet"
      v-model:open="open"
      :title="sheetTitle"
      :description="searchLabel"
      content-class="max-h-[80%]"
      @close-auto-focus="onSheetCloseAutoFocus"
    >
      <ReuseBody />
    </BottomSheet>
  </div>
</template>
