import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import dotenv from 'dotenv';

dotenv.config();
const backend = new URL(process.env.APP_URL);
// Set the Laravel host and the port

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        https: {
            key: fs.readFileSync("./certs/localhost+1-key.pem"),
            cert: fs.readFileSync("./certs/localhost+1.pem")
        }, // Enable HTTPS
        host: 'localhost',
        port: 5174, // Frontend server port
        cors: {
            origin: 'https://localhost:5173', // Allow requests from your frontend
            methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], // Allowed HTTP methods
            allowedHeaders: ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Authorization'], // Allowed headers
            credentials: true, // Allow cookies and credentials
        },
        proxy: {
            // Proxy API requests to Laravel backend
            '^(?!(\/\@vite|\/resources|\/node_modules))': {
                target: backend.origin, // Laravel backend
                changeOrigin: true, // Update the origin header to match the target
                secure: false, // Disable SSL verification for local development
            },
        },
    },
});
