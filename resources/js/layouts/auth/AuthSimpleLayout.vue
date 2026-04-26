<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-7 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-9">
                <div class="flex flex-col items-center gap-6">
                    <Link :href="route('home')" class="inline-flex items-center">
                        <AppLogo size="md" />
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="font-serif text-3xl leading-tight tracking-[-0.015em] text-foreground">{{ title }}</h1>
                        <p v-if="description" class="mx-auto max-w-[34ch] text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
