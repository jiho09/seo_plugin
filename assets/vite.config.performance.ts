import { defineConfig } from 'vite';

export default defineConfig({
  define: {
    'process.env.NODE_ENV': '"production"'
  },
  build: {
    outDir: '../dist',
    lib: {
      entry: 'src/performance-collector.js',
      name: 'WpSeoCheckPerformance',
      formats: ['iife'],
      fileName: () => 'performance-collector.js'
    },
  }
});