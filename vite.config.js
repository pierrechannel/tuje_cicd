import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
     build: {
        outDir: 'public', // Configurez ici le répertoire de sortie
        emptyOutDir: true, // (Optionnel) Vide le répertoire de sortie avant de construire
    }
});
