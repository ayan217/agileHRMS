import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/work-timer.css', 'resources/css/holiday-calendar.css', 'resources/css/admin-attendance.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
