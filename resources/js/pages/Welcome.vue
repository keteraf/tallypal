<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import WelcomeContactSection from '@/pages/welcome/sections/WelcomeContactSection.vue';
import WelcomeHeroSection from '@/pages/welcome/sections/WelcomeHeroSection.vue';
import WelcomeInfoSection from '@/pages/welcome/sections/WelcomeInfoSection.vue';
import WelcomePricingSection from '@/pages/welcome/sections/WelcomePricingSection.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{
    auth: {
        user: any;
    };
}>();

const sectionIds = ['app-hero', 'app-info', 'app-pricing', 'app-contact'] as const;
const mobileNextThreshold = 0.65;
const mobileBackThreshold = 0.35;

const isSuperAdmin = props.auth.user?.roles?.includes('Super Admin');

const scrollerRef = ref<HTMLElement | null>(null);
const activeSection = ref(0);
const outerSectionProgress = ref(0);
const pricingInnerProgress = ref(0);
const contactInnerProgress = ref(0);
const isDesktop = ref(false);
const isContactSubmitting = ref(false);
const isContactSubmitted = ref(false);
const contactForm = ref({
    name: '',
    email: '',
    message: '',
});

let submitFeedbackTimer: ReturnType<typeof setTimeout> | null = null;

const isFirstSection = computed(() => activeSection.value === 0);
const isLastSection = computed(() => activeSection.value === sectionIds.length - 1);

const nextCtaLabel = computed(() => {
    if (activeSection.value === 0) {
        return 'welcome.discover_more';
    }

    if (activeSection.value === 1) {
        return 'welcome.pricing_cta';
    }

    if (activeSection.value === 2) {
        return 'welcome.contact_cta';
    }

    return 'welcome.contact_cta';
});

const sectionProgress = computed(() => {
    if (activeSection.value === 2) {
        return pricingInnerProgress.value;
    }

    if (activeSection.value === 3) {
        return contactInnerProgress.value;
    }

    return outerSectionProgress.value;
});

const isAlwaysVisibleMobileNextSection = computed(() => activeSection.value <= 1);

const showNextCta = computed(() => {
    if (isLastSection.value) {
        return false;
    }

    if (isDesktop.value) {
        return true;
    }

    if (isAlwaysVisibleMobileNextSection.value) {
        return true;
    }

    return sectionProgress.value >= mobileNextThreshold;
});

const showBackCta = computed(() => {
    if (isFirstSection.value) {
        return false;
    }

    if (isDesktop.value) {
        return true;
    }

    return sectionProgress.value <= mobileBackThreshold;
});

const nextCtaPositionClass = computed(() => {
    if (showNextCta.value) {
        return 'bottom-[calc(env(safe-area-inset-bottom)+1rem)] animate-in fade-in slide-in-from-bottom-4 md:bottom-8';
    }

    return 'pointer-events-none bottom-[calc(env(safe-area-inset-bottom)+1rem)] opacity-0 translate-y-6 md:bottom-8';
});

const backCtaPositionClass = computed(() => {
    if (showBackCta.value) {
        return 'top-6 animate-in fade-in slide-in-from-top-4';
    }

    return 'pointer-events-none top-6 opacity-0 -translate-y-4';
});

const goToSection = (index: number) => {
    const sectionId = sectionIds[index];

    if (!sectionId) {
        return;
    }

    document.getElementById(sectionId)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const goToNextSection = () => {
    if (isLastSection.value) {
        return;
    }

    goToSection(Math.min(activeSection.value + 1, 3));
};

const goToPreviousSection = () => {
    if (isFirstSection.value) {
        return;
    }

    goToSection(Math.max(activeSection.value - 1, 0));
};

const updateActiveSection = () => {
    if (!scrollerRef.value) {
        return;
    }

    const { clientHeight, scrollTop } = scrollerRef.value;

    if (clientHeight === 0) {
        return;
    }

    const normalizedScroll = scrollTop / clientHeight;

    activeSection.value = Math.max(0, Math.min(3, Math.round(normalizedScroll)));
    outerSectionProgress.value = Math.max(0, Math.min(1, normalizedScroll - Math.floor(normalizedScroll)));
};

const updateViewportMode = () => {
    isDesktop.value = window.innerWidth >= 768;
};

const updatePricingScrollProgress = (progress: number) => {
    pricingInnerProgress.value = progress;
};

const updateContactScrollProgress = (progress: number) => {
    contactInnerProgress.value = progress;
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

    updateViewportMode();
    updateActiveSection();
    window.addEventListener('resize', updateViewportMode);
    window.addEventListener('resize', updateActiveSection);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateViewportMode);
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
                    <SheetContent side="right" class="w-70 border-border/80 bg-background/95 p-6 backdrop-blur">
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
            class="group fixed left-1/2 z-30 inline-flex -translate-x-1/2 items-center gap-2 rounded-full border border-border/80 bg-background/75 px-5 py-2 text-sm font-semibold text-foreground/90 shadow-lg backdrop-blur transition-all duration-300 hover:scale-105 hover:bg-accent hover:text-accent-foreground"
            :class="nextCtaPositionClass"
            @click="goToNextSection"
        >
            {{ $t(nextCtaLabel) }}
            <span class="text-base transition-transform group-hover:translate-y-0.5">↓</span>
        </button>

        <button
            type="button"
            class="group fixed left-1/2 z-30 inline-flex -translate-x-1/2 items-center gap-2 rounded-full border border-border/80 bg-background/75 px-5 py-2 text-sm font-semibold text-foreground/90 shadow-lg backdrop-blur transition-all duration-300 hover:scale-105 hover:bg-accent hover:text-accent-foreground"
            :class="backCtaPositionClass"
            @click="goToPreviousSection"
        >
            {{ $t('welcome.back') }}
            <span class="text-base transition-transform group-hover:-translate-y-0.5">↑</span>
        </button>

        <WelcomeHeroSection />
        <WelcomeInfoSection />
        <WelcomePricingSection @pricing-scroll="updatePricingScrollProgress" />
        <WelcomeContactSection
            v-model:contact-form="contactForm"
            :is-contact-submitting="isContactSubmitting"
            :is-contact-submitted="isContactSubmitted"
            @contact-scroll="updateContactScrollProgress"
            @submit="submitContactForm"
        />
    </div>
</template>
