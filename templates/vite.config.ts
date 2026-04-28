import { defineConfig } from 'vite';
import { resolve, join } from 'path';
import fs from 'fs';

function getFrontendInputs(baseDir: string) {
  const inputs: Record<string, string> = {};
  const frontendDir = resolve(baseDir, 'src/frontend');
  
  if (!fs.existsSync(frontendDir)) return inputs;

  const walk = (dir: string, prefix = '') => {
    const files = fs.readdirSync(dir, { withFileTypes: true });
    for (const file of files) {
      if (file.isDirectory()) {
        walk(join(dir, file.name), join(prefix, file.name));
      } else if (file.name.endsWith('.ts') || file.name.endsWith('.css')) {
        const name = join(prefix, file.name.replace(/\.(ts|css)$/, '')).replace(/\\/g, '/');
        const inputKey = file.name.endsWith('.css') ? `${name}-style` : name;
        inputs[inputKey] = resolve(dir, file.name);
      }
    }
  };

  walk(frontendDir);
  return inputs;
}

export default defineConfig(({ mode }) => {
  const frontendInputs = getFrontendInputs(__dirname);

  return {
    base: '/mark/',
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
      outDir: '../public/mark',
      emptyOutDir: true,
      manifest: 'vanilla-cards-manifest.json',
      rollupOptions: {
        input: {
          init: resolve(__dirname, 'src/init.ts'),
          'core-app': resolve(__dirname, 'src/app.ts'),
          'core-css': resolve(__dirname, 'src/css.ts'),
          'theme-switcher': resolve(__dirname, 'src/dev/theme-switcher.ts'),
          'auth-app': resolve(__dirname, 'src/auth/app.ts'),
          'auth-style': resolve(__dirname, 'src/auth/styles.css'),
          // Cardboard admin page-specific
          'cardboard-app': resolve(__dirname, 'src/cardboard/app.ts'),
          'cardboard-style': resolve(__dirname, 'src/cardboard/styles/cardboard.css'),
          ...frontendInputs
        },
        output: {
          entryFileNames: `[name]-[hash].js`,
          chunkFileNames: `[name]-[hash].js`,
          assetFileNames: (assetInfo) => {
            const name = assetInfo.name || '';
            if (name.endsWith('.css')) {
              return `[name]-[hash].css`;
            }
            return `[name]-[hash][extname]`;
          }
        }
      }
    }
  };
});
