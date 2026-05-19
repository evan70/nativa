import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig(() => {
  return {
    base: '/dist/',
    publicDir: 'static',
    appType: 'custom',
    server: {
      allowedHosts: true,
      port: 5173,
    },
    preview: {
      allowedHosts: true,
      port: 4173,
    },
    build: {
      target: 'es2015',
      outDir: '../public/dist',
      emptyOutDir: true,
      manifest: 'manifest.json',
      rollupOptions: {
        input: {
          // Always loaded
          init: resolve(__dirname, 'src/init.ts'),
          core: resolve(__dirname, 'src/core.ts'),

          // Page-specific
          'page-home': resolve(__dirname, 'src/pages/home/home.ts'),
          'page-portfolio': resolve(__dirname, 'src/pages/portfolio/portfolio.ts'),
          'page-articles': resolve(__dirname, 'src/pages/articles/articles.ts'),
          'page-mark': resolve(__dirname, 'src/pages/mark/mark.ts'),
          'page-auth': resolve(__dirname, 'src/pages/auth/auth.ts'),
          'page-errors': resolve(__dirname, 'src/pages/errors/errors.ts'),
          'page-fire-show-demo': resolve(__dirname, 'src/pages/fire-show-demo.ts'),
        },
        output: {
          entryFileNames: 'assets/[name]-[hash].js',
          chunkFileNames: 'assets/[name]-[hash].js',
          assetFileNames: (assetInfo) => {
            return assetInfo.name?.endsWith('.css')
              ? 'assets/[name]-[hash].css'
              : 'assets/[name]-[hash][extname]';
          },
        },
      },
    },
  };
});