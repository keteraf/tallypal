<script setup lang="ts">
import type { SelectTriggerProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { ChevronDown } from "lucide-vue-next"
import { SelectIcon, SelectTrigger, useForwardProps } from "reka-ui"
import { useFormFieldAria } from "@/components/ui/form-field/context"
import { cn } from "@/lib/utils"

const props = withDefaults(
  defineProps<
    SelectTriggerProps & {
      class?: HTMLAttributes["class"]
      /** `default` = editorial underline; `filled` = mobile surface field; `pill` = compact inline select for setting rows. */
      variant?: "default" | "filled" | "pill"
    }
  >(),
  {
    variant: "default",
  },
)

const delegatedProps = reactiveOmit(props, "class", "variant")

const forwardedProps = useForwardProps(delegatedProps)

const variantClasses = {
  pill: "relative flex h-auto w-auto shrink-0 items-center rounded-full border-0 bg-surface-2 py-[7px] pl-[11px] pr-[26px] text-[12px] font-medium text-text-2 outline-none transition-colors hover:text-text focus:text-text data-[state=open]:text-text disabled:cursor-not-allowed disabled:opacity-50 [&>span]:truncate text-start font-sans",
  filled:
    "flex h-auto w-full items-center justify-between rounded-[14px] border border-transparent bg-surface-2 px-[15px] py-[14px] text-[15px] text-text data-[placeholder]:text-text-3 outline-none transition focus:border-accent aria-invalid:border-neg aria-invalid:focus:border-neg disabled:cursor-not-allowed disabled:opacity-50 [&>span]:truncate text-start font-sans",
  default:
    "flex h-11 w-full items-center justify-between rounded-none border-0 border-b border-line bg-transparent px-0 py-2.5 text-base text-foreground data-[placeholder]:text-muted-foreground transition-[color,border-color] outline-none focus:border-accent focus:ring-0 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:truncate text-start font-sans",
}

const { ariaInvalid, ariaDescribedby } = useFormFieldAria()

const variantClass = variantClasses[props.variant]
</script>

<template>
  <SelectTrigger
    v-bind="forwardedProps"
    :aria-invalid="ariaInvalid"
    :aria-describedby="ariaDescribedby"
    :class="cn(variantClass, props.class)"
  >
    <slot />
    <SelectIcon as-child>
      <ChevronDown
        v-if="variant === 'pill'"
        class="pointer-events-none absolute right-[9px] size-[11px] text-text-3"
      />
      <ChevronDown v-else class="w-4 h-4 text-muted-foreground shrink-0" />
    </SelectIcon>
  </SelectTrigger>
</template>
