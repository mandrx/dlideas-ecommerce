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
      const res  = await fetch(`/api/search?q=${encodeURIComponent(val.trim())}`)
      const data = await res.json()
      results.value = data.results
      open.value    = data.results.length > 0
    } finally {
      loading.value = false
    }
  }, 300)
})

function close() {
  setTimeout(() => { open.value = false }, 150)
}

function formatPrice(p, sale) {
  return 'S$ ' + Number(sale || p).toFixed(2)
}
</script>

<template>
  <div class="dl-search" style="position:relative;">
    <input
      v-model="query"
      @blur="close"
      type="search"
      placeholder="Search for toys, games, electronics…"
      autocomplete="off"
    />
    <button @click="open = results.length > 0" type="button">Search</button>

    <!-- Dropdown -->
    <div v-if="open"
         style="position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:9999;
                background:#FFF;border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,0.12);
                overflow:hidden;max-height:380px;overflow-y:auto;">
      <a
        v-for="r in results"
        :key="r.id"
        :href="`/product/${r.slug}`"
        style="display:flex;align-items:center;gap:12px;padding:10px 16px;
               text-decoration:none;color:#2C3E50;border-bottom:1px solid #EEE;
               font-family:'Nunito',sans-serif;font-size:.92rem;transition:background .15s;"
        @mouseenter="$event.currentTarget.style.background='#FFF0E8'"
        @mouseleave="$event.currentTarget.style.background=''"
      >
        <img v-if="r.image" :src="r.image"
             style="width:44px;height:44px;object-fit:cover;border-radius:8px;flex-shrink:0;" alt="">
        <div v-else style="width:44px;height:44px;background:#F5F7FA;border-radius:8px;flex-shrink:0;"></div>
        <div>
          <div style="font-weight:700;">{{ r.name }}</div>
          <div style="color:#EF6C23;font-weight:800;font-size:.85rem;">{{ formatPrice(r.price, r.sale_price) }}</div>
        </div>
      </a>
    </div>

    <div v-if="loading"
         style="position:absolute;top:50%;right:12px;transform:translateY(-50%);">
      <span class="spinner-border spinner-border-sm" style="color:#EF6C23;width:18px;height:18px;"></span>
    </div>
  </div>
</template>
