<script setup lang="ts">
import type { SelectTriggerProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { ChevronDown } from "lucide-vue-next"
import { SelectIcon, SelectTrigger, useForwardProps } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<SelectTriggerProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <SelectTrigger
    v-bind="forwardedProps"
    :class="cn(
      'flex h-11 w-full items-center justify-between rounded-none border-0 border-b border-rule bg-transparent px-0 py-2.5 text-base text-foreground data-[placeholder]:text-muted-foreground transition-[color,border-color] outline-none focus:border-brand focus:ring-0 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:truncate text-start font-sans',
      props.class,
    )"
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDown class="w-4 h-4 text-muted-foreground shrink-0" />
    </SelectIcon>
  </SelectTrigger>
</template>
