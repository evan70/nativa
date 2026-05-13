import { defineConfig } from 'vite';
import { resolve } from 'path';

/**
 * Vite configuration for @nativa/cardboard package
 * 
 * Target: ES2015 for Android WebView + older mobile browsers compatibility
 * Format: ESM library output
 */
export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'src/index.ts'),
      name: 'Cardboard',
      formats: ['es'],
      fileName: 'index',
    },
    rollupOptions: {
      // External dependencies (peer or external)
      external: ['htmx.org'],
      output: {
        // Preserve dynamic imports for tree-shaking
        preserveModules: true,
        preserveModulesRoot: 'src',
        // Asset handling
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'index.css' || assetInfo.name?.endsWith('.css')) {
            return 'styles.css';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
    // Target ES2015 for Android compatibility
    target: 'es2015',
    // CSS code splitting
    cssCodeSplit: true,
    // Source maps for debugging
    sourcemap: true,
  },
  // ES2015 polyfills not included (add terser for minification if needed)
  esbuild: {
    target: 'es2015',
  },
  // Resolve aliases
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
});