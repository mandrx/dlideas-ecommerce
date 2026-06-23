<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  images: { type: Array, required: true }
})

const active = ref(props.images.findIndex(i => i.primary) ?? 0)
const current = computed(() => props.images[active.value] ?? props.images[0])
</script>

<template>
  <div>
    <div class="mb-3 text-center" style="height:420px;overflow:hidden;background:#f8f9fa;border-radius:.5rem;">
      <img
        :src="current.path"
        :alt="'Product image ' + (active + 1)"
        style="max-height:420px;max-width:100%;object-fit:contain;"
      />
    </div>
    <div v-if="images.length > 1" class="d-flex gap-2 flex-wrap">
      <img
        v-for="(img, i) in images"
        :key="i"
        :src="img.path"
        :class="['rounded', 'border', i === active ? 'border-primary border-2' : '']"
        style="width:70px;height:70px;object-fit:cover;cursor:pointer;"
        @click="active = i"
        :alt="'Thumbnail ' + (i + 1)"
      />
    </div>
  </div>
</template>
