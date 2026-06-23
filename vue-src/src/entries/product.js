import { createApp } from 'vue'
import ProductGallery from '../components/ProductGallery.vue'

const galleryEl = document.getElementById('product-gallery')
if (galleryEl) {
  const images = JSON.parse(galleryEl.dataset.images || '[]')
  createApp(ProductGallery, { images }).mount(galleryEl)
}
