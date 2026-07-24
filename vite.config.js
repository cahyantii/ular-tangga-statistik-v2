import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-dashboard.js',
                'resources/js/admin-board-editor.js',
                'resources/js/petak-editor.js',
                'resources/js/konektor-editor.js',
                'resources/js/game-play.js',
                'resources/js/board-visuals.js',
                'resources/js/room-realtime.js',
            ],
            refresh: true,
        }),
    ],
});
