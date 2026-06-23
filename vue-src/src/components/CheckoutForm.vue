<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  csrfName:  { type: String, required: true },
  csrfHash:  { type: String, required: true },
  items:     { type: Array,  required: true },
  subtotal:  { type: Number, required: true },
  shipping:  { type: Number, default: 10 },
  stripeKey: { type: String, required: true },
})

let csrfName = props.csrfName
let csrfHash = props.csrfHash

const couponCode     = ref('')
const couponApplied  = ref(false)
const couponDiscount = ref(0)
const couponError    = ref('')
const couponLoading  = ref(false)

const total = computed(() =>
  Math.max(0, props.subtotal + props.shipping - couponDiscount.value)
)

const form = ref({
  full_name:    '',
  phone:        '',
  address_line: '',
  city:         '',
  postcode:     '',
  state:        '',
})

const states = [
  'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan',
  'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak',
  'Selangor', 'Terengganu', 'Kuala Lumpur', 'Labuan', 'Putrajaya',
]

const stripe     = ref(null)
const cardEl     = ref(null)
const cardWidget = ref(null)
const paying     = ref(false)
const payError   = ref('')

onMounted(() => {
  if (typeof Stripe === 'undefined' || !props.stripeKey) return
  stripe.value = Stripe(props.stripeKey)
  const elements = stripe.value.elements()
  cardWidget.value = elements.create('card', { style: { base: { fontSize: '16px' } } })
  cardWidget.value.mount(cardEl.value)
})

async function applyCoupon() {
  couponError.value  = ''
  couponLoading.value = true
  try {
    const body = new URLSearchParams({
      coupon_code: couponCode.value,
      subtotal:    props.subtotal,
      [csrfName]:  csrfHash,
    })
    const res  = await fetch('/api/coupon/apply', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
    })
    const data = await res.json()
    if (data.csrf) { csrfName = data.csrf.name; csrfHash = data.csrf.hash }
    if (data.ok) {
      couponApplied.value  = true
      couponDiscount.value = data.discount
    } else {
      couponError.value = data.error || 'Invalid coupon.'
    }
  } catch (e) {
    couponError.value = 'Network error. Please try again.'
  } finally {
    couponLoading.value = false
  }
}

async function pay() {
  payError.value = ''
  paying.value   = true

  try {
    const body = new URLSearchParams({
      coupon_code: couponApplied.value ? couponCode.value : '',
      [csrfName]:  csrfHash,
    })
    const res  = await fetch('/api/payment/intent', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
    })
    const data = await res.json()
    if (data.csrf) { csrfName = data.csrf.name; csrfHash = data.csrf.hash }
    if (!res.ok || !data.client_secret) {
      payError.value = data.error || 'Could not create payment. Please try again.'
      return
    }

    const { error, paymentIntent } = await stripe.value.confirmCardPayment(data.client_secret, {
      payment_method: {
        card: cardWidget.value,
        billing_details: { name: form.value.full_name },
      },
    })

    if (error) {
      payError.value = error.message
      return
    }

    sessionStorage.setItem('checkout_shipping', JSON.stringify(form.value))
    sessionStorage.setItem('checkout_coupon', couponApplied.value ? couponCode.value : '')
    window.location.href = `/checkout/confirm?payment_intent=${paymentIntent.id}`
  } catch (e) {
    payError.value = 'Network error. Please try again.'
  } finally {
    paying.value = false
  }
}
</script>

<template>
  <div class="row g-4">
    <!-- Shipping Form -->
    <div class="col-lg-7">
      <div class="card mb-4">
        <div class="card-header fw-semibold">Shipping Details</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label">Full Name *</label>
              <input v-model="form.full_name" type="text" class="form-control" required />
            </div>
            <div class="col-sm-6">
              <label class="form-label">Phone *</label>
              <input v-model="form.phone" type="text" class="form-control" required />
            </div>
            <div class="col-12">
              <label class="form-label">Address *</label>
              <input v-model="form.address_line" type="text" class="form-control" placeholder="Street, unit" required />
            </div>
            <div class="col-sm-4">
              <label class="form-label">Postcode *</label>
              <input v-model="form.postcode" type="text" class="form-control" required />
            </div>
            <div class="col-sm-4">
              <label class="form-label">City *</label>
              <input v-model="form.city" type="text" class="form-control" required />
            </div>
            <div class="col-sm-4">
              <label class="form-label">State *</label>
              <select v-model="form.state" class="form-select" required>
                <option value="">— State —</option>
                <option v-for="s in states" :key="s" :value="s">{{ s }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Coupon -->
      <div class="card mb-4">
        <div class="card-header fw-semibold">Coupon Code</div>
        <div class="card-body">
          <div v-if="couponApplied" class="alert alert-success py-2 mb-0">
            Coupon applied! You save RM {{ couponDiscount.toFixed(2) }}
          </div>
          <div v-else>
            <div class="input-group">
              <input
                v-model="couponCode"
                type="text"
                class="form-control"
                placeholder="Enter code"
                :disabled="couponLoading"
              />
              <button
                @click="applyCoupon"
                class="btn btn-outline-secondary"
                :disabled="couponLoading || !couponCode"
              >
                {{ couponLoading ? '…' : 'Apply' }}
              </button>
            </div>
            <div v-if="couponError" class="text-danger small mt-1">{{ couponError }}</div>
          </div>
        </div>
      </div>

      <!-- Payment -->
      <div class="card">
        <div class="card-header fw-semibold">Payment</div>
        <div class="card-body">
          <div ref="cardEl" class="border rounded p-3 mb-3"></div>
          <div v-if="payError" class="alert alert-danger py-2">{{ payError }}</div>
          <button @click="pay" :disabled="paying" class="btn btn-primary btn-lg w-100">
            {{ paying ? 'Processing…' : `Pay RM ${total.toFixed(2)}` }}
          </button>
        </div>
      </div>
    </div>

    <!-- Order Summary -->
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header fw-semibold">Order Summary</div>
        <ul class="list-group list-group-flush">
          <li
            v-for="item in items"
            :key="item.id"
            class="list-group-item d-flex justify-content-between align-items-start"
          >
            <div>
              <div class="fw-semibold">{{ item.name }}</div>
              <small class="text-muted">x{{ item.quantity }} — {{ item.store_name }}</small>
            </div>
            <span>RM {{ item.line_total.toFixed(2) }}</span>
          </li>
        </ul>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-1">
            <span>Subtotal</span><span>RM {{ subtotal.toFixed(2) }}</span>
          </div>
          <div class="d-flex justify-content-between mb-1 text-muted">
            <span>Shipping</span><span>RM {{ shipping.toFixed(2) }}</span>
          </div>
          <div v-if="couponDiscount > 0" class="d-flex justify-content-between mb-1 text-success">
            <span>Coupon</span><span>–RM {{ couponDiscount.toFixed(2) }}</span>
          </div>
          <hr />
          <div class="d-flex justify-content-between fw-bold fs-5">
            <span>Total</span><span>RM {{ total.toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
