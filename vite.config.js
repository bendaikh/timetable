import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/boxes-management.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Optimize for production builds with limited resources
        minify: 'esbuild',
        // Reduce chunk size to avoid memory issues
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
    esbuild: {
        // Limit concurrent operations
        target: 'es2020',
    },
    // Reduce worker threads
    worker: {
        format: 'es',
    },
});
