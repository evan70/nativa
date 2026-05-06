import { defineConfig } from 'vite';
import { resolve } from 'path';
import fs from 'fs';

function getFrontendInputs(baseDir: string): Record<string, string> {
  const inputs: Record<string, string> = {};
  const frontendDir = resolve(baseDir, 'src/frontend');

  if (!fs.existsSync(frontendDir)) return inputs;

  const walk = (dir: string, prefix = '') => {
    for (const file of fs.readdirSync(dir, { withFileTypes: true })) {
      const prefixedName = prefix ? `${prefix}/${file.name}` : file.name;
      if (file.isDirectory()) {
        walk(resolve(dir, file.name), prefixedName);
      } else if (file.name.endsWith('.ts') || file.name.endsWith('.css')) {
        const name = prefixedName.replace(/\.(ts|css)$/, '').replace(/\\/g, '/');
        inputs[file.name.endsWith('.css') ? `${name}-style` : name] = resolve(dir, file.name);
      }
    }
  };

  walk(frontendDir);
  return inputs;
}

export default defineConfig(() => {
  const frontendInputs = getFrontendInputs(__dirname);

  return {
    base: '/dist/',
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
      target: 'es2020',
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
          'page-dash': resolve(__dirname, 'src/pages/dash/dash.ts'),
          'page-auth': resolve(__dirname, 'src/pages/auth/auth.ts'),

          // Auto-discovered frontend sections
          ...frontendInputs,
        },
        output: {
          entryFileNames: 'assets/[name]-[hash].js',
          chunkFileNames: 'assets/[name]-[hash].js',
          assetFileNames: (assetInfo) => {
            return assetInfo.name?.endsWith('.css')
              ? 'assets/[name]-[hash].css'
              : 'assets/[name]-[hash][extname]';
          },
          manualChunks(id) {
            if (id.includes('node_modules/chart.js')) {
              return 'vendor';
            }
          },
        },
      },
    },
  };
});
