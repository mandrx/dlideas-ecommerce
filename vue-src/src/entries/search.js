import { createApp } from 'vue'
import LiveSearch from '../components/LiveSearch.vue'

const el = document.getElementById('live-search')
if (el) createApp(LiveSearch).mount(el)
