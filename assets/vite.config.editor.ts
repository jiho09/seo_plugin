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
      external: ['react', 'react-dom', '@wordpress/api-fetch', '@wordpress/element', '@wordpress/components', '@wordpress/i18n'],
      output: {
        globals: {
          react: 'React',
          'react-dom': 'ReactDOM',
          '@wordpress/api-fetch': 'wp.apiFetch',
          '@wordpress/element': 'wp.element',
          '@wordpress/components': 'wp.components',
          '@wordpress/i18n': 'wp.i18n',
        },
      }
    }
  }
});
