import vue from '@vitejs/plugin-vue';
import path from 'path';
import { defineConfig } from 'vitest/config';

/**
 * Front-end unit tests: pure TypeScript logic and logic-heavy Vue components. Kept apart from
 * `vite.config.ts` so the Laravel and Wayfinder plugins (which shell out to artisan) never run here.
 */
export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    test: {
        environment: 'happy-dom',
        include: ['resources/js/**/*.test.ts'],
    },
});
