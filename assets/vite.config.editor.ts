import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  define: {
    'process.env.NODE_ENV': '"production"'
  },
  build: {
    outDir: '../dist',
    lib: {
      entry: 'src/editor.tsx',
      name: 'WpSeoCheckEditor',
      formats: ['iife'],
      fileName: () => 'editor.js'
    },
    rollupOptions: {
      external: ['react', 'react-dom', '@wordpress/api-fetch'],
      output: {
        globals: {
          react: 'React',
          'react-dom': 'ReactDOM',
          '@wordpress/api-fetch': 'wp.apiFetch',
        },
      }
    }
  }
});
