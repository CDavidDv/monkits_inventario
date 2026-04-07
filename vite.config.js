import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    base: process.env.NODE_ENV === 'production' ? '/inventario/' : '/',
    server: {
        host: '0.0.0.0', // Escucha en todas las interfaces
        port: 5173, // Puerto que quieres usar, 5173 es el predeterminado
        hmr: {
          host: 'localhost', // Usa localhost para desarrollo local
          port: 5173,
        },
      },
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
});
