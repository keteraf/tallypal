<script setup lang="ts">
import { Button } from '@/components/ui/button';
import tallies from '@/routes/tallies';
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
</script>

<template>
    <div class="mt-20 flex flex-col items-center gap-6 text-center md:mt-32">
        <div>
            <h2 class="font-serif text-3xl tracking-[-0.015em] text-foreground">{{ $t('home.empty_title') }}</h2>
            <p class="mt-2 text-muted-foreground">{{ $t('home.empty_description') }}</p>
        </div>
        <Button size="lg" as-child>
            <Link :href="tallies.create.url()">
                <Plus class="size-4" />
                {{ $t('tallies.create_new') }}
            </Link>
        </Button>
    </div>
</template>
