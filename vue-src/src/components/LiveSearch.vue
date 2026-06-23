<script setup>
import { ref, watch } from 'vue'

const query   = ref('')
const results = ref([])
const loading = ref(false)
const open    = ref(false)

let timer = null

watch(query, (val) => {
  clearTimeout(timer)
  if (val.trim().length < 2) { results.value = []; open.value = false; return }
  timer = setTimeout(async () => {
    loading.value = true
    try {
      const res = await fetch(`/api/search?q=${encodeURIComponent(val.trim())}`)
      const data = await res.json()
      results.value = data.results
      open.value = data.results.length > 0
    } finally {
      loading.value = false
    }
  }, 300)
})

function close() {
  setTimeout(() => { open.value = false }, 150)
}

function formatPrice(p, sale) {
  return 'RM ' + Number(sale || p).toFixed(2)
}
</script>

<template>
  <div class="position-relative">
    <input
      v-model="query"
      @blur="close"
      type="search"
      class="form-control form-control-sm"
      placeholder="Search products…"
      style="min-width:220px;"
      autocomplete="off"
    />
    <div v-if="open" class="position-absolute bg-white border rounded shadow-sm"
         style="top:100%;left:0;right:0;z-index:9999;max-height:360px;overflow-y:auto;">
      <a
        v-for="r in results"
        :key="r.id"
        :href="`/product/${r.slug}`"
        class="d-flex align-items-center gap-2 p-2 text-decoration-none text-dark border-bottom"
        style="font-size:.9rem;"
      >
        <img v-if="r.image" :src="r.image" style="width:40px;height:40px;object-fit:cover;" class="rounded" alt="">
        <div v-else style="width:40px;height:40px;" class="bg-light rounded flex-shrink-0"></div>
        <div>
          <div class="fw-semibold">{{ r.name }}</div>
          <div class="text-muted small">{{ formatPrice(r.price, r.sale_price) }}</div>
        </div>
      </a>
    </div>
    <div v-if="loading" class="position-absolute" style="top:8px;right:8px;">
      <span class="spinner-border spinner-border-sm text-secondary"></span>
    </div>
  </div>
</template>
