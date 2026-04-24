import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: 'localhost',
        port: 5173,
        cors: {
            origin: [
                'http://localhost',
                'http://localhost:5173',
                'http://ecommerce.com',
            ],
            credentials: false,
        },
    },

    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/site/layout.css', // ← AGREGAR ESTE
                'resources/js/app.js',
                'resources/js/admin.js',
                'resources/js/site.js',
            ],
            refresh: true,
        }),
    ],
});
