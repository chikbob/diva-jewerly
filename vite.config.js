import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: process.env.VITE_HOST || '0.0.0.0',
        port: Number(process.env.VITE_PORT || 5173),
        strictPort: true,
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
            port: Number(process.env.VITE_PORT || 5173),
        },
        watch: {
            usePolling: true,
        },
    },
});
