import { createApp } from 'vue'
import ProductGallery from '../components/ProductGallery.vue'
import ReviewList     from '../components/ReviewList.vue'

const galleryEl = document.getElementById('product-gallery')
if (galleryEl) {
  const images = JSON.parse(galleryEl.dataset.images || '[]')
  createApp(ProductGallery, { images }).mount(galleryEl)
}

const reviewEl = document.getElementById('review-list')
if (reviewEl) {
  createApp(ReviewList, {
    productId: Number(reviewEl.dataset.productId),
    csrfName:  reviewEl.dataset.csrfName,
    csrfHash:  reviewEl.dataset.csrfHash,
  }).mount(reviewEl)
}
