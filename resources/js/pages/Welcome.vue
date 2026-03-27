<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';
import { Head, Link } from '@inertiajs/vue3';
import { LoaderCircle, Menu, Send } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{
    auth: {
        user: any;
    };
}>();

const isSuperAdmin = props.auth.user?.roles?.includes('Super Admin');

const scrollerRef = ref<HTMLElement | null>(null);
const heroSectionRef = ref<HTMLElement | null>(null);
const infoSectionRef = ref<HTMLElement | null>(null);
const contactSectionRef = ref<HTMLElement | null>(null);
const activeSection = ref(0);
const isContactSubmitting = ref(false);
const isContactSubmitted = ref(false);
const contactForm = ref({
    name: '',
    email: '',
    message: '',
});

let submitFeedbackTimer: ReturnType<typeof setTimeout> | null = null;

const isLastSection = computed(() => activeSection.value === 2);

const ctaLabel = computed(() => {
    if (activeSection.value === 0) {
        return 'welcome.discover_more';
    }

    if (activeSection.value === 1) {
        return 'welcome.contact_cta';
    }

    return 'welcome.back';
});

const ctaArrow = computed(() => (isLastSection.value ? '↑' : '↓'));

const goToSection = (index: number) => {
    const sections = [heroSectionRef.value, infoSectionRef.value, contactSectionRef.value];
    sections[index]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const toggleSection = () => {
    if (isLastSection.value) {
        goToSection(1);
        return;
    }

    goToSection(Math.min(activeSection.value + 1, 2));
};

const updateActiveSection = () => {
    if (!scrollerRef.value) {
        return;
    }

    const sectionHeight = scrollerRef.value.clientHeight;

    if (sectionHeight === 0) {
        return;
    }

    activeSection.value = Math.max(0, Math.min(2, Math.round(scrollerRef.value.scrollTop / sectionHeight)));
};

const submitContactForm = async () => {
    if (isContactSubmitting.value) {
        return;
    }

    isContactSubmitting.value = true;

    await new Promise((resolve) => setTimeout(resolve, 700));

    isContactSubmitting.value = false;
    isContactSubmitted.value = true;
    contactForm.value = { name: '', email: '', message: '' };

    if (submitFeedbackTimer) {
        clearTimeout(submitFeedbackTimer);
    }

    submitFeedbackTimer = setTimeout(() => {
        isContactSubmitted.value = false;
    }, 2800);
};

onMounted(() => {
    if (!scrollerRef.value) {
        return;
    }

    updateActiveSection();
    window.addEventListener('resize', updateActiveSection);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateActiveSection);
    if (submitFeedbackTimer) {
        clearTimeout(submitFeedbackTimer);
    }
});
</script>

<template>
    <Head :title="$t('welcome.title')" />

    <div
        ref="scrollerRef"
        class="relative h-screen snap-y snap-mandatory overflow-y-auto bg-background text-foreground transition-colors duration-500"
        @scroll.passive="updateActiveSection"
    >
        <!-- Navigation -->
        <header class="fixed top-0 right-0 z-30 p-4 md:p-6">
            <nav class="hidden items-center gap-4 md:flex">
                <template v-if="auth.user">
                    <Link
                        v-if="isSuperAdmin"
                        :href="route('dashboard')"
                        class="rounded-full border border-border px-6 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                    >
                        {{ $t('common.dashboard') }}
                    </Link>
                    <Link
                        v-else
                        :href="route('tallies.index')"
                        class="rounded-full border border-border px-6 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                    >
                        {{ $t('welcome.my_tallies') }}
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-full px-6 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                    >
                        {{ $t('common.logout') }}
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="rounded-full px-6 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                    >
                        {{ $t('common.login') }}
                    </Link>
                    <Link
                        :href="route('register')"
                        class="rounded-full border border-border px-6 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                    >
                        {{ $t('common.register') }}
                    </Link>
                </template>
            </nav>

            <div class="md:hidden">
                <Sheet>
                    <SheetTrigger :as-child="true">
                        <Button variant="outline" size="icon" class="h-10 w-10 rounded-full border-border/80 bg-background/80 backdrop-blur">
                            <Menu class="h-5 w-5" />
                            <span class="sr-only">{{ $t('common.navigation_menu') }}</span>
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="right" class="w-[280px] border-border/80 bg-background/95 p-6 backdrop-blur">
                        <SheetHeader>
                            <SheetTitle>{{ $t('common.navigation_menu') }}</SheetTitle>
                        </SheetHeader>
                        <div class="mt-8 grid gap-3">
                            <template v-if="auth.user">
                                <Link
                                    v-if="isSuperAdmin"
                                    :href="route('dashboard')"
                                    class="rounded-full border border-border px-5 py-2 text-center text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                                >
                                    {{ $t('common.dashboard') }}
                                </Link>
                                <Link
                                    v-else
                                    :href="route('tallies.index')"
                                    class="rounded-full border border-border px-5 py-2 text-center text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                                >
                                    {{ $t('welcome.my_tallies') }}
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="rounded-full px-5 py-2 text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                                >
                                    {{ $t('common.logout') }}
                                </Link>
                            </template>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="rounded-full px-5 py-2 text-center text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                                >
                                    {{ $t('common.login') }}
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="rounded-full border border-border px-5 py-2 text-center text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground"
                                >
                                    {{ $t('common.register') }}
                                </Link>
                            </template>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </header>

        <button
            type="button"
            class="group fixed left-1/2 z-30 inline-flex -translate-x-1/2 items-center gap-2 rounded-full border border-border/80 bg-background/75 px-5 py-2 text-sm font-semibold text-foreground/90 shadow-lg backdrop-blur transition-all duration-500 hover:scale-105 hover:bg-accent hover:text-accent-foreground"
            :class="isLastSection ? 'top-6 animate-in fade-in slide-in-from-top-4' : 'bottom-8 animate-bounce animate-in fade-in slide-in-from-bottom-4'"
            @click="toggleSection"
        >
            {{ $t(ctaLabel) }}
            <span class="text-base transition-transform group-hover:translate-y-0.5">
                {{ ctaArrow }}
            </span>
        </button>

        <section
            ref="heroSectionRef"
            class="relative flex h-screen snap-start flex-col items-center justify-center gap-5 overflow-hidden p-6 text-center lg:p-8"
        >
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_25%,hsl(var(--accent)/0.35),transparent_50%)]" />

            <h1
                class="animate-in text-center text-7xl font-black tracking-tighter duration-1000 fade-in slide-in-from-bottom-8 md:text-8xl lg:text-9xl"
            >
                {{ $t('welcome.app_name') }}
            </h1>
            <p
                class="animate-in rounded-full border border-border/70 bg-accent/40 px-6 py-2 text-center text-lg font-semibold tracking-wide text-foreground/80 duration-1000 fade-in slide-in-from-bottom-6 md:text-xl"
                style="animation-delay: 180ms"
            >
                {{ $t('welcome.slogan') }}
            </p>
        </section>

        <section
            id="app-info"
            ref="infoSectionRef"
            class="relative flex h-screen snap-start items-center justify-center overflow-hidden px-6 py-20 lg:px-8"
        >
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_75%,hsl(var(--accent)/0.28),transparent_55%)]" />
            <div class="w-full max-w-5xl rounded-3xl border border-border/70 bg-card/75 p-8 shadow-xl backdrop-blur sm:p-10">
                <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $t('welcome.info.title') }}</h2>
                <p class="mt-3 max-w-2xl text-muted-foreground">
                    {{ $t('welcome.info.description') }}
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <article class="rounded-2xl border border-border/70 bg-background/70 p-5">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-foreground/80">
                            {{ $t('welcome.info.cards.setup.title') }}
                        </h3>
                        <p class="mt-2 text-sm text-muted-foreground">{{ $t('welcome.info.cards.setup.description') }}</p>
                    </article>
                    <article class="rounded-2xl border border-border/70 bg-background/70 p-5">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-foreground/80">
                            {{ $t('welcome.info.cards.clear.title') }}
                        </h3>
                        <p class="mt-2 text-sm text-muted-foreground">{{ $t('welcome.info.cards.clear.description') }}</p>
                    </article>
                    <article class="rounded-2xl border border-border/70 bg-background/70 p-5">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-foreground/80">
                            {{ $t('welcome.info.cards.trust.title') }}
                        </h3>
                        <p class="mt-2 text-sm text-muted-foreground">{{ $t('welcome.info.cards.trust.description') }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section
            id="app-contact"
            ref="contactSectionRef"
            class="relative flex h-screen snap-start items-center justify-center overflow-hidden px-6 py-20 lg:px-8"
        >
            <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_30%_75%,hsl(var(--accent)/0.3),transparent_55%)]" />

            <div class="w-full max-w-5xl rounded-3xl border border-border/70 bg-card/75 p-8 shadow-xl backdrop-blur sm:p-10">
                <div class="grid gap-6 lg:grid-cols-[1fr_1.15fr] lg:items-start">
                    <div>
                        <h3 class="text-2xl font-bold tracking-tight">{{ $t('welcome.contact.title') }}</h3>
                        <p class="mt-2 max-w-md text-muted-foreground">{{ $t('welcome.contact.description') }}</p>
                        <div class="mt-4 inline-flex items-center rounded-full border border-border/70 bg-background/80 px-4 py-1 text-xs font-semibold tracking-wide text-foreground/70">
                            {{ $t('welcome.contact.tagline') }}
                        </div>
                    </div>

                    <Card class="border-border/70 bg-background/75 shadow-lg">
                        <CardHeader class="pb-4">
                            <CardTitle class="text-xl">{{ $t('welcome.contact.form_title') }}</CardTitle>
                            <CardDescription>{{ $t('welcome.contact.form_description') }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form class="grid gap-4" @submit.prevent="submitContactForm">
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
    </div>
</template>
