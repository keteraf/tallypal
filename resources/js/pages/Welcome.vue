<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    auth: {
        user: any;
    };
}>();

const isSuperAdmin = props.auth.user?.roles?.includes('Super Admin');
</script>

<template>
    <Head :title="$t('welcome.title')" />

    <div
        class="relative flex min-h-screen flex-col items-center justify-center bg-background p-6 text-foreground transition-colors duration-500 lg:p-8"
    >
        <!-- Navigation -->
        <header class="absolute top-0 right-0 p-6">
            <nav class="flex items-center gap-4">
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
        </header>

        <!-- Main Content -->
        <main class="flex flex-col items-center justify-center">
            <h1
                class="animate-in text-center text-7xl font-black tracking-tighter duration-1000 fade-in slide-in-from-bottom-8 md:text-8xl lg:text-9xl"
            >
                {{ $t('welcome.app_name') }}
            </h1>
        </main>

        <!-- Footer / Bottom spacing -->
        <footer class="absolute bottom-0 p-6 text-sm text-muted-foreground">&copy; {{ new Date().getFullYear() }} Tallypal</footer>
    </div>
</template>
