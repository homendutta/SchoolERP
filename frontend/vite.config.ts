import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'node:path';

// Asylinx School ERP — Web client build config.
// The ERP is served under the school's single domain (ERP paths), consuming the
// one Laravel API. The public website remains the existing site at the root.
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
      '@app': path.resolve(__dirname, './src/app'),
      '@core': path.resolve(__dirname, './src/core'),
      '@ui': path.resolve(__dirname, './src/ui'),
      '@features': path.resolve(__dirname, './src/features'),
    },
  },
  server: {
    port: 5173,
    // Proxy API calls to the Laravel dev server so web + API share one origin
    // in development (mirrors the single-domain production model).
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: 'dist',
    sourcemap: true,
  },
});
