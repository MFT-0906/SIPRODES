import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

// Font di-host sebagai berkas di repo (resources/fonts) agar `npm run build` dan situs tidak butuh internet.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                local('Plus Jakarta Sans', {
                    variants: [
                        { src: 'resources/fonts/plus-jakarta-sans-600.woff2', weight: 600 },
                        { src: 'resources/fonts/plus-jakarta-sans-700.woff2', weight: 700 },
                    ],
                    display: 'swap',
                    alias: 'heading',
                }),
                local('Inter', {
                    variants: [
                        { src: 'resources/fonts/inter-400.woff2', weight: 400 },
                        { src: 'resources/fonts/inter-500.woff2', weight: 500 },
                        { src: 'resources/fonts/inter-600.woff2', weight: 600 },
                    ],
                    display: 'swap',
                    alias: 'body',
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
