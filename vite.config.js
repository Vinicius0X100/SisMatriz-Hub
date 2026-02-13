import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.scss', 'resources/js/app.js', 'resources/js/calendar.tsx', 'resources/js/calendar-matrimonio.tsx'],
            refresh: true,
        }),
        react(),
    ],
});
