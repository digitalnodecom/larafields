import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';
import { fileURLToPath } from 'url';
import tailwindcss from '@tailwindcss/vite';

const __dirname = fileURLToPath(new URL('.', import.meta.url));

export default defineConfig({
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        'resources/css/app.css', // This is mapped to larafields.css in AssetsController
        'resources/js/app.js', // This is mapped to larafields.js in AssetsController
      ],
      refresh: true,
    }),
  ],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources'),
      '@scripts': resolve(__dirname, 'resources/js'),
      '@styles': resolve(__dirname, 'resources/css'),
    },
  },
});