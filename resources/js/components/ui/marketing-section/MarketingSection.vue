<script setup lang="ts">
import { MarketingContainer } from "@/components/ui/marketing-container"

withDefaults(
  defineProps<{
    id?: string
    /** Adds the top hairline rule between stacked sections (source `.sec-line`). */
    rule?: boolean
  }>(),
  {
    rule: false,
  },
)
</script>

<template>
  <section :id="id" class="py-section" :class="rule && 'border-t border-line'">
    <MarketingContainer>
      <slot />
    </MarketingContainer>
  </section>
</template>
