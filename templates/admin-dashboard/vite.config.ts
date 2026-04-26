import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig(({ mode }) => {
  return {
    base: './',
    publicDir: 'static',
    server: {
      allowedHosts: true,
      port: 5173,
    },
    preview: {
      allowedHosts: true,
      port: 4173,
    },
    build: {
      target: ['chrome67', 'es2015'],
      outDir: 'docs',
      emptyOutDir: true,
      manifest: 'vanilla-cards-manifest.json',
      rollupOptions: {
        input: {
          index: resolve(__dirname, 'index.html'),
          init: resolve(__dirname, 'src/init.ts'),
          'core-app': resolve(__dirname, 'src/app.ts'),
          'core-css': resolve(__dirname, 'src/css.ts'),
          home: resolve(__dirname, 'src/frontend/home/home.ts'),
          'theme-switcher': resolve(__dirname, 'src/dev/theme-switcher.ts')
        },
        output: {
          entryFileNames: `[name].js`,
          chunkFileNames: `[name].js`,
          assetFileNames: (assetInfo) => {
            const name = assetInfo.name || '';
            if (name.endsWith('.css')) {
              return `[name].css`;
            }
            return `[name][extname]`;
          }
        }
      }
    }
  };
});
