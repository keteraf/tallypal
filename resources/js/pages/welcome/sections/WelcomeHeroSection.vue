<template>
    <section
        id="app-hero"
        class="relative flex h-screen snap-start flex-col items-center justify-center gap-5 overflow-hidden p-6 text-center lg:p-8"
    >
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_25%,hsl(var(--accent)/0.35),transparent_50%)]" />

        <h1 class="animate-in text-center text-7xl font-black tracking-tighter duration-1000 fade-in slide-in-from-bottom-8 md:text-8xl lg:text-9xl">
            {{ $t('welcome.app_name') }}
        </h1>
        <p
            class="animate-in rounded-full border border-border/70 bg-accent/40 px-6 py-2 text-center text-lg font-semibold tracking-wide text-foreground/80 duration-1000 fade-in slide-in-from-bottom-6 md:text-xl"
            style="animation-delay: 180ms"
        >
            {{ $t('welcome.slogan') }}
        </p>
    </section>
</template>
