<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';

const emit = defineEmits<{
    pricingScroll: [progress: number];
}>();

const onPricingScroll = (event: Event) => {
    const element = event.currentTarget as HTMLElement | null;

    if (!element) {
        return;
    }

    const maxScroll = element.scrollHeight - element.clientHeight;
    const progress = maxScroll <= 0 ? 1 : element.scrollTop / maxScroll;

    emit('pricingScroll', Math.max(0, Math.min(1, progress)));
};
</script>

<template>
    <section
        id="app-pricing"
        class="relative flex h-screen snap-start items-start justify-center overflow-x-hidden overflow-y-auto px-6 pt-20 pb-36 lg:items-center lg:overflow-hidden lg:px-8 lg:py-20"
        @scroll.passive="onPricingScroll"
    >
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_30%,hsl(var(--accent)/0.22),transparent_58%)]" />
        <div class="w-full max-w-5xl rounded-3xl border border-border/70 bg-card/75 p-8 shadow-xl backdrop-blur sm:p-10">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $t('welcome.pricing.title') }}</h2>
                <p class="mt-3 text-muted-foreground">{{ $t('welcome.pricing.description') }}</p>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                <Card class="border-border/70 bg-background/75 shadow-lg">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-2xl">{{ $t('welcome.pricing.tiers.free.name') }}</CardTitle>
                        <CardDescription>{{ $t('welcome.pricing.tiers.free.summary') }}</CardDescription>
                        <p class="pt-2 text-3xl font-extrabold tracking-tight">{{ $t('welcome.pricing.tiers.free.price') }}</p>
                    </CardHeader>
                    <CardContent>
                        <ul class="grid gap-2 text-sm text-foreground/90">
                            <li class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 text-emerald-500" />
                                <span>{{ $t('welcome.pricing.tiers.free.features.create_tallies') }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 text-emerald-500" />
                                <span>{{ $t('welcome.pricing.tiers.free.features.share_tallies') }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 text-emerald-500" />
                                <span>{{ $t('welcome.pricing.tiers.free.features.custom_share_percentage') }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 text-emerald-500" />
                                <span>{{ $t('welcome.pricing.tiers.free.features.add_remove_transactions') }}</span>
                            </li>
                        </ul>
                    </CardContent>
                    <CardFooter>
                        <Button as-child class="w-full">
                            <Link :href="route('register')">{{ $t('welcome.pricing.tiers.free.cta') }}</Link>
                        </Button>
                    </CardFooter>
                </Card>

                <Card class="border-border bg-primary/5 shadow-lg">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-2xl">{{ $t('welcome.pricing.tiers.pro.name') }}</CardTitle>
                        <CardDescription>{{ $t('welcome.pricing.tiers.pro.summary') }}</CardDescription>
                        <p class="pt-2 text-3xl font-extrabold tracking-tight">{{ $t('welcome.pricing.tiers.pro.price') }}</p>
                    </CardHeader>
                    <CardContent>
                        <ul class="grid gap-2 text-sm text-foreground/90">
                            <li class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 text-emerald-500" />
                                <span>{{ $t('welcome.pricing.tiers.pro.features.everything_in_free') }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 text-emerald-500" />
                                <span>{{ $t('welcome.pricing.tiers.pro.features.stats_access') }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 text-emerald-500" />
                                <span>{{ $t('welcome.pricing.tiers.pro.features.import_export_file') }}</span>
                            </li>
                        </ul>
                    </CardContent>
                    <CardFooter>
                        <Button as-child class="w-full" variant="secondary">
                            <Link :href="route('register')">{{ $t('welcome.pricing.tiers.pro.cta') }}</Link>
                        </Button>
                    </CardFooter>
                </Card>
            </div>
            <Separator class="mt-8 bg-border/70" />
            <p class="mt-4 text-center text-xs text-muted-foreground">{{ $t('welcome.pricing.footnote') }}</p>
        </div>
    </section>
</template>
