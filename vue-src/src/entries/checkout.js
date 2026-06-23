import { createApp } from 'vue'
import CheckoutForm from '../components/CheckoutForm.vue'

const el = document.getElementById('checkout-form')
if (el) {
  const items    = JSON.parse(el.dataset.items    || '[]')
  const subtotal = parseFloat(el.dataset.subtotal || '0')
  const shipping = parseFloat(el.dataset.shipping || '10')

  createApp(CheckoutForm, {
    csrfName:  el.dataset.csrfName,
    csrfHash:  el.dataset.csrfHash,
    items,
    subtotal,
    shipping,
    stripeKey: el.dataset.stripeKey,
  }).mount(el)
}
