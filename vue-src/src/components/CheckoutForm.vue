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

// GST is included in displayed prices — show component for transparency
const gstIncluded = computed(() =>
  Math.round(total.value * 9 / 109 * 100) / 100
)

const form = ref({
  full_name:    '',
  phone:        '',
  address_line: '',
  unit_no:      '',
  postcode:     '',
  city:         'Singapore',
  state:        'Singapore',
})

const stripe     = ref(null)
const cardEl     = ref(null)
const cardWidget = ref(null)
const paying     = ref(false)
const payError   = ref('')

onMounted(() => {
  if (typeof Stripe === 'undefined' || !props.stripeKey) return
  stripe.value = Stripe(props.stripeKey)
  const elements = stripe.value.elements()
  cardWidget.value = elements.create('card', {
    hidePostalCode: true,
    style: {
      base: {
        fontSize: '15px',
        fontFamily: "'Nunito', sans-serif",
        color: '#2d3748',
        iconColor: '#EF6C23',
        '::placeholder': { color: '#a0aec0' },
      },
      invalid: { color: '#e53e3e' },
    }
  })
  cardWidget.value.mount(cardEl.value)
})

async function applyCoupon() {
  couponError.value   = ''
  couponLoading.value = true
  try {
    const body = new URLSearchParams({
      coupon_code: couponCode.value,
      subtotal:    props.subtotal,
      [csrfName]:  csrfHash,
    })
    const res  = await fetch('/api/coupon/apply', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
    })
    const data = await res.json()
    if (data.csrf) { csrfName = data.csrf.name; csrfHash = data.csrf.hash }
    if (data.ok) {
      couponApplied.value  = true
      couponDiscount.value = data.discount
    } else {
      couponError.value = data.error || 'Invalid promo code.'
    }
  } catch {
    couponError.value = 'Network error. Please try again.'
  } finally {
    couponLoading.value = false
  }
}

async function pay() {
  payError.value = ''

  if (!form.value.full_name || !form.value.address_line || !form.value.postcode) {
    payError.value = 'Please fill in all required delivery fields.'
    return
  }

  if (!/^\d{6}$/.test(form.value.postcode)) {
    payError.value = 'Please enter a valid 6-digit Singapore postal code.'
    return
  }

  paying.value = true

  try {
    const body = new URLSearchParams({
      coupon_code: couponApplied.value ? couponCode.value : '',
      [csrfName]:  csrfHash,
    })
    const res  = await fetch('/api/payment/intent', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
    })
    const data = await res.json()
    if (data.csrf) { csrfName = data.csrf.name; csrfHash = data.csrf.hash }
    if (!res.ok || !data.client_secret) {
      payError.value = data.error || 'Could not create payment. Please try again.'
      return
    }

    const saveBody = new URLSearchParams({
      shipping:    JSON.stringify(form.value),
      coupon_code: couponApplied.value ? couponCode.value : '',
      [csrfName]:  csrfHash,
    })
    const saveRes  = await fetch('/cart/save-checkout-session', {
      method:  'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:    saveBody,
    })
    const saveData = await saveRes.json()
    if (saveData.csrf) { csrfName = saveData.csrf.name; csrfHash = saveData.csrf.hash }
    if (!saveData.ok) {
      payError.value = saveData.error || 'Failed to save checkout session.'
      return
    }

    const { error, paymentIntent } = await stripe.value.confirmCardPayment(data.client_secret, {
      payment_method: {
        card:            cardWidget.value,
        billing_details: { name: form.value.full_name },
      },
    })

    if (error) {
      payError.value = error.message
      return
    }

    window.location.href = `/checkout/confirm?payment_intent=${paymentIntent.id}`
  } catch {
    payError.value = 'Network error. Please try again.'
  } finally {
    paying.value = false
  }
}
</script>

<template>
  <div class="co-wrap">

    <!-- Progress breadcrumb -->
    <nav class="co-steps" aria-label="Checkout steps">
      <span class="co-step co-step--done">Cart</span>
      <span class="co-step-sep" aria-hidden="true"></span>
      <span class="co-step co-step--active">Delivery</span>
      <span class="co-step-sep" aria-hidden="true"></span>
      <span class="co-step co-step--active">Payment</span>
      <span class="co-step-sep" aria-hidden="true"></span>
      <span class="co-step co-step--future">Done</span>
    </nav>

    <div class="co-layout">

      <!-- ── Left: forms ── -->
      <div class="co-forms">

        <!-- Section 1: Delivery -->
        <section class="co-section">
          <h2 class="co-section-title">
            <span class="co-num">1</span>
            Delivery Details
          </h2>

          <div class="co-field-grid">
            <div class="co-field co-field--half">
              <label class="co-label">Full Name <abbr title="required">*</abbr></label>
              <input
                v-model="form.full_name"
                type="text"
                class="co-input"
                placeholder="Your full name"
                autocomplete="name"
                required
              />
            </div>
            <div class="co-field co-field--half">
              <label class="co-label">Mobile Number <abbr title="required">*</abbr></label>
              <div class="co-phone-wrap">
                <span class="co-phone-prefix">+65</span>
                <input
                  v-model="form.phone"
                  type="tel"
                  class="co-input co-input--phone"
                  placeholder="8123 4567"
                  autocomplete="tel"
                  maxlength="8"
                />
              </div>
            </div>
            <div class="co-field co-field--full">
              <label class="co-label">Block / Street Address <abbr title="required">*</abbr></label>
              <input
                v-model="form.address_line"
                type="text"
                class="co-input"
                placeholder="e.g. 123 Clementi Road"
                autocomplete="address-line1"
                required
              />
            </div>
            <div class="co-field co-field--half">
              <label class="co-label">Unit Number <span class="co-optional">(optional)</span></label>
              <input
                v-model="form.unit_no"
                type="text"
                class="co-input"
                placeholder="#12-34"
                autocomplete="address-line2"
              />
            </div>
            <div class="co-field co-field--quarter">
              <label class="co-label">Postal Code <abbr title="required">*</abbr></label>
              <input
                v-model="form.postcode"
                type="text"
                class="co-input"
                placeholder="6-digit code"
                autocomplete="postal-code"
                maxlength="6"
                inputmode="numeric"
                pattern="\d{6}"
                required
              />
            </div>
            <div class="co-field co-field--quarter">
              <label class="co-label">Country</label>
              <div class="co-input co-input--fixed">
                Singapore
              </div>
            </div>
          </div>
        </section>

        <!-- Section 2: Promo code -->
        <section class="co-section">
          <h2 class="co-section-title">
            <span class="co-num">2</span>
            Promo Code
          </h2>

          <div v-if="couponApplied" class="co-promo-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Promo applied — you save <strong>S${{ couponDiscount.toFixed(2) }}</strong>
          </div>
          <div v-else class="co-promo-row">
            <input
              v-model="couponCode"
              type="text"
              class="co-input co-input--promo"
              placeholder="Enter promo code"
              :disabled="couponLoading"
              @keyup.enter="applyCoupon"
            />
            <button
              @click="applyCoupon"
              class="co-btn co-btn--ghost"
              :disabled="couponLoading || !couponCode"
            >
              {{ couponLoading ? '…' : 'Apply' }}
            </button>
          </div>
          <p v-if="couponError" class="co-field-error">{{ couponError }}</p>
        </section>

        <!-- Section 3: Payment -->
        <section class="co-section">
          <h2 class="co-section-title">
            <span class="co-num">3</span>
            Payment
          </h2>

          <p class="co-demo-hint">
            Demo: use card <code>4242 4242 4242 4242</code>, any future expiry, any CVC.
          </p>

          <div class="co-card-accepted">
            <span class="co-card-badge">VISA</span>
            <span class="co-card-badge">MC</span>
            <span class="co-card-badge co-card-badge--amex">AMEX</span>
          </div>

          <div ref="cardEl" class="co-stripe-el"></div>

          <div v-if="payError" class="co-pay-error">{{ payError }}</div>

          <button @click="pay" :disabled="paying" class="co-btn co-btn--pay">
            <svg v-if="paying" class="co-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            <span>{{ paying ? 'Processing…' : `Pay S$${total.toFixed(2)}` }}</span>
          </button>

          <div class="co-trust-row">
            <span class="co-trust-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Secured by Stripe
            </span>
            <span class="co-trust-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              256-bit SSL
            </span>
            <span class="co-trust-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
              Easy Returns
            </span>
          </div>
        </section>
      </div>

      <!-- ── Right: order summary ── -->
      <aside class="co-summary">
        <div class="co-summary-inner">
          <h3 class="co-summary-title">Your Order</h3>

          <ul class="co-items">
            <li v-for="item in items" :key="item.id" class="co-item">
              <div class="co-item-info">
                <span class="co-item-qty">{{ item.quantity }}×</span>
                <div>
                  <div class="co-item-name">{{ item.name }}</div>
                  <div class="co-item-seller">{{ item.store_name }}</div>
                </div>
              </div>
              <span class="co-item-price">S${{ item.line_total.toFixed(2) }}</span>
            </li>
          </ul>

          <div class="co-price-breakdown">
            <div class="co-price-row">
              <span>Subtotal</span>
              <span>S${{ subtotal.toFixed(2) }}</span>
            </div>
            <div class="co-price-row co-price-row--muted">
              <span>Shipping</span>
              <span>S${{ shipping.toFixed(2) }}</span>
            </div>
            <div v-if="couponDiscount > 0" class="co-price-row co-price-row--saving">
              <span>Promo discount</span>
              <span>−S${{ couponDiscount.toFixed(2) }}</span>
            </div>
            <div class="co-price-row co-price-row--gst">
              <span>GST (9% incl.)</span>
              <span>S${{ gstIncluded.toFixed(2) }}</span>
            </div>
            <div class="co-price-total">
              <span>Total</span>
              <span>S${{ total.toFixed(2) }}</span>
            </div>
          </div>

          <div class="co-sg-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            All prices in SGD. GST registered under IRAS.
          </div>
        </div>
      </aside>

    </div>
  </div>
</template>

<style scoped>
/* ── Layout ── */
.co-wrap {
  font-family: 'Nunito', sans-serif;
  color: var(--text-dark);
}

.co-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 2rem;
  align-items: start;
}

@media (max-width: 900px) {
  .co-layout {
    grid-template-columns: 1fr;
  }
  .co-summary {
    order: -1;
  }
}

/* ── Progress steps ── */
.co-steps {
  display: flex;
  align-items: center;
  gap: 0;
  margin-bottom: 2rem;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.co-step {
  padding: 0.25rem 0.5rem;
  color: var(--text-light);
}

.co-step--done {
  color: var(--secondary);
}

.co-step--active {
  color: var(--primary);
}

.co-step--future {
  color: var(--text-light);
}

.co-step-sep {
  display: inline-block;
  width: 24px;
  height: 1px;
  background: var(--border);
  margin: 0 2px;
  vertical-align: middle;
}

/* ── Section ── */
.co-section {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--shadow-card);
}

.co-section-title {
  font-family: 'Baloo 2', sans-serif;
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--text-dark);
  margin: 0 0 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.co-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  background: var(--primary);
  color: var(--text-on-primary);
  border-radius: 50%;
  font-size: 0.8rem;
  font-weight: 800;
  font-family: 'Nunito', sans-serif;
  flex-shrink: 0;
}

/* ── Field grid ── */
.co-field-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.co-field--full  { grid-column: 1 / -1; }
.co-field--half  { grid-column: span 1; }
.co-field--quarter { grid-column: span 1; }

@media (max-width: 560px) {
  .co-field-grid {
    grid-template-columns: 1fr;
  }
  .co-field--half,
  .co-field--quarter { grid-column: 1 / -1; }
}

.co-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 0.35rem;
}

.co-label abbr {
  text-decoration: none;
  color: var(--primary);
  margin-left: 2px;
}

.co-optional {
  font-weight: 400;
  text-transform: none;
  letter-spacing: 0;
  color: var(--text-light);
  font-size: 0.78rem;
}

.co-input {
  width: 100%;
  padding: 0.6rem 0.85rem;
  border: 1.5px solid var(--border-strong);
  border-radius: 10px;
  font-family: 'Nunito', sans-serif;
  font-size: 0.95rem;
  color: var(--text-dark);
  background: var(--bg-subtle);
  transition: border-color 0.15s, box-shadow 0.15s;
  box-sizing: border-box;
  outline: none;
}

.co-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px oklch(62% 0.18 33 / 0.12);
  background: var(--bg-card);
}

.co-input--fixed {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: var(--text-muted);
  cursor: default;
  user-select: none;
}

/* Phone field */
.co-phone-wrap {
  display: flex;
  align-items: stretch;
}

.co-phone-prefix {
  display: flex;
  align-items: center;
  padding: 0 0.75rem;
  background: var(--primary-muted);
  border: 1.5px solid var(--border-strong);
  border-right: none;
  border-radius: 10px 0 0 10px;
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--primary);
  white-space: nowrap;
}

.co-input--phone {
  border-radius: 0 10px 10px 0;
  flex: 1;
}

/* ── Promo ── */
.co-promo-row {
  display: flex;
  gap: 0.6rem;
}

.co-input--promo {
  flex: 1;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 700;
}

.co-promo-success {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1rem;
  background: var(--success-light);
  color: var(--success);
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.92rem;
}

.co-field-error {
  margin: 0.4rem 0 0;
  font-size: 0.82rem;
  color: var(--danger);
  font-weight: 600;
}

/* ── Buttons ── */
.co-btn {
  font-family: 'Nunito', sans-serif;
  font-weight: 800;
  border: none;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s, opacity 0.15s;
  border-radius: 10px;
}

.co-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.co-btn--ghost {
  padding: 0.6rem 1.1rem;
  background: transparent;
  border: 1.5px solid var(--primary);
  color: var(--primary);
  font-size: 0.9rem;
  white-space: nowrap;
}

.co-btn--ghost:hover:not(:disabled) {
  background: var(--primary-light);
}

.co-btn--pay {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.9rem 1rem;
  background: var(--primary);
  color: var(--text-on-primary);
  font-size: 1.05rem;
  border-radius: 12px;
  margin-top: 1rem;
  box-shadow: 0 4px 16px oklch(62% 0.18 33 / 0.3);
}

.co-btn--pay:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px oklch(62% 0.18 33 / 0.38);
}

.co-btn--pay:active:not(:disabled) {
  transform: translateY(0);
}

/* ── Stripe element ── */
.co-demo-hint {
  font-size: 0.8rem;
  color: var(--text-muted);
  background: var(--bg-subtle);
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
  margin-bottom: 1rem;
}

.co-demo-hint code {
  font-weight: 700;
  color: var(--primary);
  letter-spacing: 0.05em;
}

.co-card-accepted {
  display: flex;
  gap: 0.4rem;
  margin-bottom: 0.75rem;
}

.co-card-badge {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  padding: 0.2rem 0.5rem;
  border-radius: 5px;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  color: var(--text-muted);
}

.co-card-badge--amex { color: oklch(45% 0.12 245); }

.co-stripe-el {
  padding: 0.75rem 0.85rem;
  border: 1.5px solid var(--border-strong);
  border-radius: 10px;
  background: var(--bg-subtle);
  transition: border-color 0.15s, box-shadow 0.15s;
}

.co-stripe-el.StripeElement--focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px oklch(62% 0.18 33 / 0.12);
}

.co-pay-error {
  margin-top: 0.75rem;
  padding: 0.6rem 0.85rem;
  background: var(--danger-light);
  color: var(--danger);
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 600;
}

.co-trust-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.25rem;
  margin-top: 0.9rem;
  flex-wrap: wrap;
}

.co-trust-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

/* ── Spinner ── */
.co-spinner {
  width: 18px;
  height: 18px;
  animation: co-spin 0.8s linear infinite;
  flex-shrink: 0;
}

@keyframes co-spin {
  to { transform: rotate(360deg); }
}

/* ── Order summary ── */
.co-summary-inner {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: var(--shadow-card);
  position: sticky;
  top: 1.5rem;
}

.co-summary-title {
  font-family: 'Baloo 2', sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0 0 1.1rem;
  color: var(--text-dark);
}

.co-items {
  list-style: none;
  margin: 0 0 1.1rem;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.co-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--border);
}

.co-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.co-item-info {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  flex: 1;
  min-width: 0;
}

.co-item-qty {
  flex-shrink: 0;
  font-size: 0.78rem;
  font-weight: 800;
  color: var(--text-on-primary);
  background: var(--primary);
  border-radius: 5px;
  padding: 0.1rem 0.4rem;
  margin-top: 1px;
}

.co-item-name {
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--text-dark);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.co-item-seller {
  font-size: 0.75rem;
  color: var(--text-light);
  margin-top: 1px;
}

.co-item-price {
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--text-dark);
  white-space: nowrap;
  flex-shrink: 0;
}

/* Price breakdown */
.co-price-breakdown {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem 0;
  border-top: 1px solid var(--border);
}

.co-price-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--text-dark);
}

.co-price-row--muted {
  color: var(--text-muted);
}

.co-price-row--saving {
  color: var(--success);
  font-weight: 700;
}

.co-price-row--gst {
  color: var(--text-light);
  font-size: 0.8rem;
  font-style: italic;
}

.co-price-total {
  display: flex;
  justify-content: space-between;
  font-family: 'Baloo 2', sans-serif;
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--text-dark);
  padding-top: 0.75rem;
  border-top: 2px solid var(--border-strong);
  margin-top: 0.25rem;
}

.co-sg-note {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: 1rem;
  padding-top: 0.9rem;
  border-top: 1px solid var(--border);
  font-size: 0.74rem;
  color: var(--text-light);
  font-weight: 600;
}
</style>
