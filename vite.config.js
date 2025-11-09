import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ command }) => ({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    tailwindcss(),
  ],
  base: command === 'build' ? '/build/' : '',
  build: {
    outDir: 'public/build',
    manifest: true,
    emptyOutDir: true,
  },
}));
