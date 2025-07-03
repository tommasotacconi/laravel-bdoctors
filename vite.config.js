import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import basicSsl from '@vitejs/plugin-basic-ssl';

// Set the Laravel host and the port
const host = '127.0.0.1';
const port = '8000';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        basicSsl(), // Enable HTTPS for Vite
    ],
    server: {
        https: true, // Enable HTTPS
        host,
        port: 5174, // Frontend server port
        cors: {
            origin: 'http://localhost:5173', // Allow requests from your frontend
            methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], // Allowed HTTP methods
            allowedHeaders: ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Authorization'], // Allowed headers
            credentials: true, // Allow cookies and credentials
        },
        proxy: {
            // Proxy API requests to Laravel backend
            '^(?!(\/\@vite|\/resources|\/node_modules))': {
                target: `http://${host}:${port}`, // Laravel backend
                changeOrigin: true, // Update the origin header to match the target
                secure: false, // Disable SSL verification for local development
            },
        },
        hmr: { host }, // Hot Module Replacement
    },
});
