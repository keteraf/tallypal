<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { LoaderCircle, Send } from 'lucide-vue-next';

defineProps<{
    isContactSubmitting: boolean;
    isContactSubmitted: boolean;
}>();

const contactForm = defineModel<{
    name: string;
    email: string;
    message: string;
}>('contactForm', {
    required: true,
});

const emit = defineEmits<{
    submit: [];
    contactScroll: [progress: number];
}>();

const onContactScroll = (event: Event) => {
    const element = event.currentTarget as HTMLElement | null;

    if (!element) {
        return;
    }

    const maxScroll = element.scrollHeight - element.clientHeight;
    const progress = maxScroll <= 0 ? 1 : element.scrollTop / maxScroll;

    emit('contactScroll', Math.max(0, Math.min(1, progress)));
};
</script>

<template>
    <section
        id="app-contact"
        class="relative flex h-screen snap-start items-start justify-center overflow-x-hidden overflow-y-auto px-6 pt-20 pb-10 lg:items-center lg:overflow-hidden lg:px-8 lg:py-20"
        @scroll.passive="onContactScroll"
    >
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_30%_75%,hsl(var(--accent)/0.3),transparent_55%)]" />

        <div class="w-full max-w-5xl rounded-3xl border border-border/70 bg-card/75 p-8 shadow-xl backdrop-blur sm:p-10">
            <div class="grid gap-6 lg:grid-cols-[1fr_1.15fr] lg:items-start">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight">{{ $t('welcome.contact.title') }}</h3>
                    <p class="mt-2 max-w-md text-muted-foreground">{{ $t('welcome.contact.description') }}</p>
                    <div
                        class="mt-4 inline-flex items-center rounded-full border border-border/70 bg-background/80 px-4 py-1 text-xs font-semibold tracking-wide text-foreground/70"
                    >
                        {{ $t('welcome.contact.tagline') }}
                    </div>
                </div>

                <Card class="border-border/70 bg-background/75 shadow-lg">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-xl">{{ $t('welcome.contact.form_title') }}</CardTitle>
                        <CardDescription>{{ $t('welcome.contact.form_description') }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form class="grid gap-4" @submit.prevent="emit('submit')">
                            <div class="grid gap-2">
                                <Label for="contact-name">{{ $t('welcome.contact.fields.name') }}</Label>
                                <Input
                                    id="contact-name"
                                    v-model="contactForm.name"
                                    type="text"
                                    required
                                    :placeholder="$t('welcome.contact.placeholders.name')"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="contact-email">{{ $t('welcome.contact.fields.email') }}</Label>
                                <Input
                                    id="contact-email"
                                    v-model="contactForm.email"
                                    type="email"
                                    required
                                    :placeholder="$t('welcome.contact.placeholders.email')"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="contact-message">{{ $t('welcome.contact.fields.message') }}</Label>
                                <Textarea
                                    id="contact-message"
                                    v-model="contactForm.message"
                                    class="min-h-28"
                                    required
                                    :placeholder="$t('welcome.contact.placeholders.message')"
                                />
                            </div>
                            <Button type="submit" class="mt-1 w-full" :disabled="isContactSubmitting">
                                <LoaderCircle v-if="isContactSubmitting" class="h-4 w-4 animate-spin" />
                                <Send v-else class="h-4 w-4" />
                                {{ $t('welcome.contact.submit') }}
                            </Button>
                        </form>
                    </CardContent>
                    <CardFooter>
                        <p
                            class="text-sm transition-opacity duration-300"
                            :class="isContactSubmitted ? 'text-emerald-500 opacity-100' : 'text-muted-foreground opacity-80'"
                        >
                            {{ isContactSubmitted ? $t('welcome.contact.success') : $t('welcome.contact.privacy') }}
                        </p>
                    </CardFooter>
                </Card>
            </div>
            <Separator class="my-8 bg-border/70" />
            <footer class="text-center text-sm text-muted-foreground">&copy; {{ new Date().getFullYear() }} Tallypal</footer>
        </div>
    </section>
</template>
