<script setup lang="ts">
import type { CheckListItemData } from "@/components/ui/check-list"
import { Button } from "@/components/ui/button"
import { CheckList } from "@/components/ui/check-list"
import { Link } from "@inertiajs/vue3"

withDefaults(
  defineProps<{
    name: string
    tag: string
    /** Accent border + glow, accent-tinted tag and check ticks (source `.plan.hi`). */
    highlighted?: boolean
    price: string
    currency: string
    per: string
    description: string
    features: CheckListItemData[]
    ctaLabel: string
    ctaHref: string
  }>(),
  {
    highlighted: false,
  },
)
</script>

<template>
  <article
    class="rounded-lg border bg-surface p-7"
    :class="highlighted ? 'border-[color-mix(in_oklch,var(--accent)_60%,transparent)] shadow-plan-hi' : 'border-line'"
  >
    <div class="flex items-center justify-between gap-3">
      <span class="text-plan-nm font-semibold tracking-[-0.02em]">{{ name }}</span>
      <span
        class="rounded-full px-2.5 py-1 text-[10.5px] font-semibold tracking-[0.08em] uppercase"
        :class="highlighted ? 'bg-[color-mix(in_oklch,var(--accent)_18%,transparent)] text-accent' : 'bg-surface-2 text-text-3'"
      >
        {{ tag }}
      </span>
    </div>

    <div class="mt-[18px] mb-0.5 flex items-baseline gap-1 font-mono text-price font-semibold tabular-nums tracking-[-0.05em]">
      <span class="text-price-cur text-text-3">{{ currency }}</span>{{ price
      }}<span class="ml-1 font-sans text-price-per font-normal tracking-normal text-text-3">{{ per }}</span>
    </div>

    <p class="m-0 mb-[22px] max-w-[34ch] text-sm text-text-2">{{ description }}</p>

    <CheckList class="mb-6" :items="features" :tone="highlighted ? 'accent' : 'neutral'" />

    <Button as-child :variant="highlighted ? 'default' : 'surface'" class="w-full">
      <Link :href="ctaHref">{{ ctaLabel }}</Link>
    </Button>
  </article>
</template>
