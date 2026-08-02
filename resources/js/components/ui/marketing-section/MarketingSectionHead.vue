<script setup lang="ts">
withDefaults(
  defineProps<{
    kicker?: string
    heading?: string
    /** Right-hand paragraph (source `.sec-p`); omitted when there's nothing to say. */
    lede?: string
  }>(),
  {},
)
</script>

<template>
  <div
    class="mb-11 grid grid-cols-1 items-start gap-4 min-[820px]:grid-cols-2 min-[820px]:items-end min-[820px]:gap-[clamp(24px,4vw,56px)]"
  >
    <div>
      <span v-if="kicker || $slots.kicker" class="kicker"><slot name="kicker">{{ kicker }}</slot></span>
      <h2 class="mt-4 max-w-[22ch] text-section font-semibold tracking-[-0.04em] text-balance">
        <slot name="heading">{{ heading }}</slot>
      </h2>
    </div>
    <p v-if="lede || $slots.lede" class="mt-3.5 max-w-[56ch] text-sec-p text-text-2 text-pretty">
      <slot name="lede">{{ lede }}</slot>
    </p>
  </div>
</template>
