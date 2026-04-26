<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const faqs = () =>
    Array.from({ length: 6 }, (_, i) => ({
        q: t(`welcome.faq.items.${i}.q`),
        a: t(`welcome.faq.items.${i}.a`),
    }));
</script>

<template>
    <section id="faq" class="border-b border-rule-2">
        <div class="mx-auto w-full max-w-[1320px] px-6 py-20 md:px-8 md:py-24">
            <div class="mb-16 grid gap-10 md:grid-cols-[1fr_1.2fr] md:items-end md:gap-16">
                <div>
                    <div class="eyebrow">{{ $t('welcome.faq.eyebrow') }}</div>
                    <h2 class="m-0 mt-4 font-serif text-[clamp(40px,5vw,68px)] leading-none tracking-[-0.02em] text-balance">
                        {{ $t('welcome.faq.title_lead') }}
                        <em class="not-italic-[0]" style="font-style: italic; color: var(--brand)">{{ $t('welcome.faq.title_em') }}</em>
                    </h2>
                </div>
                <p class="m-0 max-w-[460px] text-[17px] text-muted-foreground">{{ $t('welcome.faq.lede') }}</p>
            </div>

            <div class="max-w-[780px]">
                <details
                    v-for="(faq, index) in faqs()"
                    :key="index"
                    class="group border-t border-rule-2 py-5 last:border-b last:border-rule-2"
                    :open="index === 0"
                >
                    <summary
                        class="flex cursor-pointer list-none items-baseline justify-between gap-6 font-serif text-[22px] leading-[1.3] tracking-[-0.01em]"
                    >
                        {{ faq.q }}
                        <span class="font-mono text-[22px] text-muted-foreground transition-transform">
                            <span class="group-open:hidden">+</span>
                            <span class="hidden group-open:inline">−</span>
                        </span>
                    </summary>
                    <p class="mt-3.5 max-w-[60ch] text-[15.5px] text-muted-foreground">{{ faq.a }}</p>
                </details>
            </div>
        </div>
    </section>
</template>
