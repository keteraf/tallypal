<script setup lang="ts">
import { Check } from 'lucide-vue-next';

interface Step {
    label: string;
}

defineProps<{
    steps: Step[];
    modelValue: number;
}>();
</script>

<template>
    <div class="flex w-full items-start">
        <template v-for="(step, index) in steps" :key="index">
            <div class="flex shrink-0 flex-col items-center gap-2">
                <div
                    class="flex size-8 items-center justify-center rounded-full border font-mono text-sm font-medium transition-colors"
                    :class="
                        index + 1 <= modelValue
                            ? 'border-brand bg-brand text-white'
                            : 'border-rule bg-transparent text-muted-foreground'
                    "
                >
                    <Check v-if="index + 1 < modelValue" class="size-3.5" />
                    <span v-else>{{ index + 1 }}</span>
                </div>
                <span
                    class="eyebrow text-center"
                    :class="index + 1 === modelValue ? 'text-foreground' : 'text-muted-foreground'"
                >
                    {{ step.label }}
                </span>
            </div>
            <div
                v-if="index < steps.length - 1"
                class="mt-4 h-px flex-1 transition-colors"
                :class="index + 1 < modelValue ? 'bg-brand' : 'bg-rule'"
            />
        </template>
    </div>
</template>
