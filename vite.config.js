import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // Alpine.js é gerenciado pelo Livewire 4 internamente.
    // Excluí-lo do bundle evita instâncias duplicadas que quebram wire:click.
    build: {
        rollupOptions: {
            external: ['alpinejs'],
        },
    },
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
