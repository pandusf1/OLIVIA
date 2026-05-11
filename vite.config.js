import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
    plugins: [

        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),

        VitePWA({

            registerType: 'autoUpdate',

            includeAssets: [
                'favicon.ico',
                'apple-touch-icon.png',
            ],

            manifest: {

                name: 'SuraRa',
                short_name: 'SuraRa',

                description:
                    'Platform bantuan darurat hukum & keselamatan',

                theme_color: '#991b1b',

                background_color: '#faf9f7',

                display: 'standalone',

                start_url: '/',

                scope: '/',

                orientation: 'portrait',

                icons: [

                    {
                        src: '/192.jpg',
                        sizes: '192x192',
                        type: 'image/jpg',
                    },

                    {
                        src: '/512.jpg',
                        sizes: '512x512',
                        type: 'image/jpg',
                    },

                    {
                        src: '/512.jpg',
                        sizes: '512x512',
                        type: 'image/jpg',
                        purpose: 'maskable',
                    },

                ],

            },

        }),

    ],
})