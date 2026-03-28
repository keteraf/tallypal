<template>
    <section
        id="app-info"
        class="relative flex h-screen snap-start items-center justify-center overflow-hidden px-6 pt-28 pb-36 md:pt-20 lg:px-8 lg:py-20"
    >
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_75%,hsl(var(--accent)/0.28),transparent_55%)]" />
        <div class="w-full max-w-5xl rounded-3xl border border-border/70 bg-card/75 p-8 shadow-xl backdrop-blur sm:p-10">
            <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $t('welcome.info.title') }}</h2>
            <p class="mt-3 max-w-2xl text-muted-foreground">
                {{ $t('welcome.info.description') }}
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-border/70 bg-background/70 p-5">
                    <h3 class="text-sm font-bold tracking-wider text-foreground/80 uppercase">
                        {{ $t('welcome.info.cards.setup.title') }}
                    </h3>
                    <p class="mt-2 text-sm text-muted-foreground">{{ $t('welcome.info.cards.setup.description') }}</p>
                </article>
                <article class="rounded-2xl border border-border/70 bg-background/70 p-5">
                    <h3 class="text-sm font-bold tracking-wider text-foreground/80 uppercase">
                        {{ $t('welcome.info.cards.clear.title') }}
                    </h3>
                    <p class="mt-2 text-sm text-muted-foreground">{{ $t('welcome.info.cards.clear.description') }}</p>
                </article>
                <article class="rounded-2xl border border-border/70 bg-background/70 p-5">
                    <h3 class="text-sm font-bold tracking-wider text-foreground/80 uppercase">
                        {{ $t('welcome.info.cards.trust.title') }}
                    </h3>
                    <p class="mt-2 text-sm text-muted-foreground">{{ $t('welcome.info.cards.trust.description') }}</p>
                </article>
            </div>
        </div>
    </section>
</template>
