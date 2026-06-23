<script setup>
import { ref, onMounted, computed } from 'vue'

const props = defineProps({
  productId: { type: Number, required: true },
  csrfName:  { type: String, required: true },
  csrfHash:  { type: String, required: true },
})

const reviews     = ref([])
const canReview   = ref(false)
const hasReviewed = ref(false)
const loading     = ref(true)
const submitting  = ref(false)
const error       = ref('')
const success     = ref('')

const form = ref({ rating: 5, body: '' })
let csrfName = props.csrfName
let csrfHash = props.csrfHash

const avgRating = computed(() => {
  if (!reviews.value.length) return 0
  return (reviews.value.reduce((s, r) => s + r.rating, 0) / reviews.value.length).toFixed(1)
})

async function load() {
  loading.value = true
  try {
    const res  = await fetch(`/api/product/${props.productId}/reviews`)
    const data = await res.json()
    reviews.value   = data.reviews
    canReview.value = data.can_review
    hasReviewed.value = data.has_reviewed
  } finally {
    loading.value = false
  }
}

async function submit() {
  error.value   = ''
  success.value = ''
  submitting.value = true
  try {
    const body = new URLSearchParams({
      rating: form.value.rating,
      body:   form.value.body,
      [csrfName]: csrfHash,
    })
    const res  = await fetch(`/api/product/${props.productId}/reviews/submit`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
    })
    const data = await res.json()
    if (data.csrf) { csrfName = data.csrf.name; csrfHash = data.csrf.hash }
    if (!res.ok) { error.value = data.error; return }
    success.value   = data.message
    canReview.value = false
    hasReviewed.value = true
    form.value = { rating: 5, body: '' }
    await load()
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <h4 class="mb-3">
      Customer Reviews
      <span v-if="reviews.length" class="fs-6 text-muted ms-2">
        {{ avgRating }} / 5 ({{ reviews.length }})
      </span>
    </h4>

    <div v-if="loading" class="text-muted">Loading reviews…</div>

    <div v-else>
      <div v-if="!reviews.length" class="text-muted mb-3">No reviews yet. Be the first!</div>
      <div v-for="r in reviews" :key="r.id" class="border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <strong>{{ r.reviewer_name }}</strong>
          <span class="text-warning">{{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}</span>
        </div>
        <p class="mb-1">{{ r.body }}</p>
        <small class="text-muted">{{ r.created_at }}</small>
      </div>

      <div v-if="hasReviewed" class="alert alert-info">You have already reviewed this product.</div>

      <div v-else-if="canReview" class="card mt-3">
        <div class="card-header fw-semibold">Write a Review</div>
        <div class="card-body">
          <div v-if="error"   class="alert alert-danger py-2">{{ error }}</div>
          <div v-if="success" class="alert alert-success py-2">{{ success }}</div>
          <div class="mb-3">
            <label class="form-label">Rating</label>
            <select v-model="form.rating" class="form-select">
              <option :value="5">5 — Excellent</option>
              <option :value="4">4 — Good</option>
              <option :value="3">3 — Average</option>
              <option :value="2">2 — Poor</option>
              <option :value="1">1 — Terrible</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Your Review</label>
            <textarea v-model="form.body" class="form-control" rows="3" placeholder="Share your experience…"></textarea>
          </div>
          <button @click="submit" :disabled="submitting" class="btn btn-primary">
            {{ submitting ? 'Submitting…' : 'Submit Review' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
