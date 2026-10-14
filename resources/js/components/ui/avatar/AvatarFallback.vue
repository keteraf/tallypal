<script setup lang="ts">
import type { CategoryColor } from "@/lib/category"
import type { HTMLAttributes } from "vue"
import { AvatarFallback, type AvatarFallbackProps } from "reka-ui"
import { computed } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<
  AvatarFallbackProps & {
    class?: HTMLAttributes["class"]
    /** Tint from the categorical palette (`c1`…`c6`). Falls back to a neutral chip. */
    color?: CategoryColor
  }
>()

const delegatedProps = computed(() => {
  const { class: _class, color: _color, ...delegated } = props

  return delegated
})

const colorStyle = computed(() =>
  props.color
    ? {
        background: `color-mix(in oklch, var(--${props.color}) 26%, var(--surface))`,
        color: `var(--${props.color})`,
        borderColor: `color-mix(in oklch, var(--${props.color}) 30%, transparent)`,
      }
    : undefined,
)
</script>

<template>
  <AvatarFallback
    data-slot="avatar-fallback"
    v-bind="delegatedProps"
    :style="colorStyle"
    :class="cn(
      'flex size-full items-center justify-center rounded-full',
      color ? 'border font-semibold tracking-[0.02em]' : 'bg-muted',
      props.class,
    )"
  >
    <slot />
  </AvatarFallback>
</template>
