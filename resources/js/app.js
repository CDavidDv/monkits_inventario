import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';

// Limpiar localStorage corrupto al iniciar la aplicación
try {
    const keys = Object.keys(localStorage);
    keys.forEach(key => {
        try {
            if (key.includes('inertia') || key.includes('scroll')) {
                const value = localStorage.getItem(key);
                // Verificar si el valor puede ser parseado como JSON
                if (value && value !== 'undefined' && value !== 'null') {
                    JSON.parse(value);
                }
            }
        } catch (e) {
            console.warn(`Removing corrupted localStorage item: ${key}`);
            localStorage.removeItem(key);
        }
    });
} catch (error) {
    console.warn('Error cleaning localStorage on app startup:', error);
}


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia);
            
        // Configurar el error handler
        app.config.errorHandler = (err, instance, info) => {
            // Capturar errores de DataCloneError y otros errores similares
            if (err.name === 'DataCloneError' || err.message.includes('could not be cloned')) {
                console.warn('DataClone error intercepted, clearing problematic data:', err);
                // Limpiar localStorage problemático
                try {
                    const keys = Object.keys(localStorage);
                    keys.forEach(key => {
                        if (key.includes('inertia') || key.includes('scroll')) {
                            localStorage.removeItem(key);
                        }
                    });
                    // Recargar la página para limpiar el estado
                    setTimeout(() => {
                        window.location.reload();
                    }, 100);
                } catch (cleanupError) {
                    console.error('Error during cleanup:', cleanupError);
                }
                return; // No propagar el error
            }
            console.error('Vue error:', err, info);
        };
            
        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
