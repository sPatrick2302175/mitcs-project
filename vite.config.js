import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            
        }),
    ],
 server: {
        host: '0.0.0.0', // Lets your network see it
        port: 5173,      // Vite's default port
        cors: true,      // 👈 ADD THIS LINE TO FIX THE CORS ERROR
        hmr: {
            host: '10.165.144.48', // Your specific local IP
        },
    },


    
});
