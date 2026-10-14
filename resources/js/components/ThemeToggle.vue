<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    class?: string;
}

defineProps<Props>();

const { appearance, updateAppearance } = useAppearance();

const isDark = computed(() => {
    if (appearance.value === 'dark') {
        return true;
    }

    if (appearance.value === 'light') {
        return false;
    }

    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
});

const toggle = () => {
    updateAppearance(isDark.value ? 'light' : 'dark');
};
</script>

<template>
    <button
        type="button"
        :aria-label="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
        :class="[
            'inline-flex size-9 items-center justify-center rounded-full border border-line text-muted-foreground transition-colors hover:border-foreground hover:text-foreground',
            $props.class,
        ]"
        @click="toggle"
    >
        <Sun v-if="isDark" class="size-4" />
        <Moon v-else class="size-4" />
    </button>
</template>
