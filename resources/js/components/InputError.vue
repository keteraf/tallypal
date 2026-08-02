<script setup lang="ts">
defineProps<{
    message?: string;
    id?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p :id="id" class="text-sm text-neg">
            {{ message }}
        </p>
    </div>
</template>
