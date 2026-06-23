import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  build: {
    outDir: resolve(__dirname, '../assets/js'),
    emptyOutDir: false,
    rollupOptions: {
      input: {
        search:   resolve(__dirname, 'src/entries/search.js'),
        product:  resolve(__dirname, 'src/entries/product.js'),
        checkout: resolve(__dirname, 'src/entries/checkout.js'),
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames:  '[name].js',
        assetFileNames:  '[name].[ext]',
        format: 'es',
        manualChunks: undefined,
      },
    },
  },
})
