import { defineConfig } from 'vitest/config';
import { resolve } from 'path';

/**
 * Vitest configuration for @nativa/cardboard package
 */
export default defineConfig({
  test: {
    // Test environment
    environment: 'jsdom',
    // Global setup
    globals: true,
    // Include test files
    include: ['tests/**/*.test.ts', 'tests/**/*.spec.ts'],
    // Coverage configuration
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['src/**/*.ts'],
      exclude: ['src/**/*.d.ts'],
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
});