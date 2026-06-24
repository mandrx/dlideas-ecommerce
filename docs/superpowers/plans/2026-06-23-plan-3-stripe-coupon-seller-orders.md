# Plan 3 — Stripe Checkout + Coupons + Seller Orders + Store Settings

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the plain-form checkout with a Stripe PaymentIntent flow, add coupon validation, implement Seller order management and store settings save.

**Architecture:** A `Payment.php` CI3 library wraps the Stripe PHP SDK. `Api::payment_intent()` creates the intent server-side. A `CheckoutForm.vue` component handles Stripe Elements card input and confirms the payment in the browser. On redirect back, `Cart::confirm()` verifies the intent and creates the order. Coupons are validated server-side in a separate API call before the intent is created. Seller orders query the DB by store_id. Store settings are saved via a POST action.

**Tech Stack:** Stripe PHP SDK (`stripe/stripe-php`), Stripe.js v3, Vue 3, Vite, CodeIgniter 3, PHPUnit

## Global Constraints

- CodeIgniter 3.1.13 — no CI4 APIs
- PHP 7.4+ compatible syntax only
- All DB access through CI3 Query Builder — no raw SQL strings
- Stripe PHP SDK installed via Composer (`composer require stripe/stripe-php`)
- Secret key and webhook secret loaded from environment variables only — never hardcoded
- Vue 3 Composition API (`<script setup>`)
- Bootstrap 5 classes for all HTML
- CSRF token refreshed after every mutating API call

---

## File Map

**Create:**
- `application/config/payment.php`
- `application/libraries/Payment.php`
- `vue-src/src/entries/checkout.js`
- `vue-src/src/components/CheckoutForm.vue`

**Modify:**
- `application/controllers/Api.php` — add `cart_summary()`, `payment_intent()`, `stripe_webhook()`, `apply_coupon()`
- `application/controllers/Cart.php` — rewrite `checkout()` and `confirm()` for Stripe flow
- `application/controllers/Seller.php` — implement `orders()`, `order_detail()`, `save_store_settings()`
- `application/models/Coupon_model.php` — add `validate()` and `redeem()`
- `application/models/Order_model.php` — add `get_for_seller()`, `get_detail_for_seller()`
- `application/views/cart/checkout.php` — replace form with Vue mount + Stripe
- `application/views/seller/orders.php` — real content
- `application/views/seller/order_detail.php` — real content with tracking form
- `application/config/routes.php`
- `composer.json`

---

### Task 1: Stripe PHP SDK + Payment Library

**Files:**
- Modify: `composer.json`
- Create: `application/config/payment.php`
- Create: `application/libraries/Payment.php`
- Modify: `.env`

**Interfaces:**
- Produces: `$this->load->library('payment')` in any controller
- Produces: `Payment::create_payment_intent($amount_cents, $currency, $metadata)` → Stripe PaymentIntent object
- Produces: `Payment::retrieve_payment_intent($id)` → Stripe PaymentIntent object
- Produces: `Payment::construct_webhook_event($payload, $sig_header)` → Stripe Event object

- [ ] **Step 1: Install Stripe PHP SDK**

```bash
composer require stripe/stripe-php
```

Expected: `vendor/stripe/` directory created.

- [ ] **Step 2: Add Stripe keys to `.env`**

Open `.env` and add:
```
STRIPE_SECRET_KEY=sk_test_YOUR_KEY_HERE
STRIPE_PUBLISHABLE_KEY=pk_test_YOUR_KEY_HERE
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET_HERE
```

Replace with your actual Stripe test keys from https://dashboard.stripe.com/test/apikeys

- [ ] **Step 3: Create `application/config/payment.php`**

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['stripe_secret_key']      = getenv('STRIPE_SECRET_KEY');
$config['stripe_publishable_key'] = getenv('STRIPE_PUBLISHABLE_KEY');
$config['stripe_webhook_secret']  = getenv('STRIPE_WEBHOOK_SECRET');
$config['currency']               = 'myr';
```

- [ ] **Step 4: Create `application/libraries/Payment.php`**

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class Payment
{
    private $CI;
    private $webhook_secret;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('payment');

        require_once FCPATH . 'vendor/autoload.php';
        Stripe::setApiKey($this->CI->config->item('stripe_secret_key'));
        $this->webhook_secret = $this->CI->config->item('stripe_webhook_secret');
    }

    public function create_payment_intent($amount_cents, $currency = null, $metadata = [])
    {
        $currency = $currency ?? $this->CI->config->item('currency');
        return PaymentIntent::create([
            'amount'   => $amount_cents,
            'currency' => $currency,
            'metadata' => $metadata,
        ]);
    }

    public function retrieve_payment_intent($id)
    {
        return PaymentIntent::retrieve($id);
    }

    public function construct_webhook_event($payload, $sig_header)
    {
        return Webhook::constructEvent($payload, $sig_header, $this->webhook_secret);
    }

    public function get_publishable_key()
    {
        return $this->CI->config->item('stripe_publishable_key');
    }
}
```

- [ ] **Step 5: Write a smoke test**

Create `tests/models/Payment_library_test.php`:
```php
<?php
require_once __DIR__ . '/../bootstrap.php';

class Payment_library_test extends PHPUnit\Framework\TestCase
{
    public function test_payment_php_file_exists()
    {
        $this->assertFileExists(APPPATH . 'libraries/Payment.php');
    }

    public function test_payment_config_keys_defined()
    {
        $config = [];
        include APPPATH . 'config/payment.php';
        $this->assertArrayHasKey('stripe_secret_key', $config);
        $this->assertArrayHasKey('stripe_publishable_key', $config);
        $this->assertArrayHasKey('stripe_webhook_secret', $config);
    }
}
```

Run: `vendor/bin/phpunit tests/models/Payment_library_test.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add application/config/payment.php application/libraries/Payment.php tests/models/Payment_library_test.php composer.json composer.lock
git commit -m "feat: add Stripe PHP SDK and Payment library"
```

---

### Task 2: Coupon_model::validate() and ::redeem()

**Files:**
- Modify: `application/models/Coupon_model.php`

**Interfaces:**
- Produces: `Coupon_model::validate($code, $user_id, $subtotal)` → `['ok' => true, 'coupon' => ..., 'discount' => 0.00]` or `['ok' => false, 'error' => '...']`
- Produces: `Coupon_model::redeem($coupon_id, $user_id, $order_id)` → void (inserts coupon_use, increments used_count in a transaction)

- [ ] **Step 1: Write PHPUnit test**

Create `tests/models/Coupon_model_test.php`:
```php
<?php
require_once __DIR__ . '/../bootstrap.php';
require_once APPPATH . 'models/Coupon_model.php';

class Coupon_model_test extends PHPUnit\Framework\TestCase
{
    public function test_validate_method_exists()
    {
        $this->assertTrue(method_exists('Coupon_model', 'validate'));
    }

    public function test_redeem_method_exists()
    {
        $this->assertTrue(method_exists('Coupon_model', 'redeem'));
    }

    public function test_apply_discount_percent()
    {
        $model  = $this->getMockBuilder(Coupon_model::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $coupon = (object)['type' => 'percent', 'value' => 10];
        // apply_discount is public — call via reflection or test it directly
        $result = (new ReflectionClass(Coupon_model::class))
                    ->getMethod('apply_discount')
                    ->invoke($model, $coupon, 100.00);
        $this->assertEquals(10.00, $result);
    }

    public function test_apply_discount_fixed()
    {
        $model  = $this->getMockBuilder(Coupon_model::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $coupon = (object)['type' => 'fixed', 'value' => 15];
        $result = (new ReflectionClass(Coupon_model::class))
                    ->getMethod('apply_discount')
                    ->invoke($model, $coupon, 100.00);
        $this->assertEquals(15.00, $result);
    }
}
```

Run: `vendor/bin/phpunit tests/models/Coupon_model_test.php`
Expected: FAIL (methods don't exist yet)

- [ ] **Step 2: Add `validate()` and `redeem()` to `application/models/Coupon_model.php`**

Add after `apply_discount()`:

```php
public function validate($code, $user_id, $subtotal)
{
    $coupon = $this->find_by_code($code);

    if (!$coupon) {
        return ['ok' => false, 'error' => 'Invalid or expired coupon code.'];
    }
    if ($coupon->used_count >= $coupon->max_uses) {
        return ['ok' => false, 'error' => 'This coupon has reached its usage limit.'];
    }
    if ($subtotal < $coupon->min_order) {
        return ['ok' => false, 'error' => 'Minimum order of RM ' . number_format($coupon->min_order, 2) . ' required.'];
    }
    // Check this user hasn't already used it
    $already = $this->db
        ->where('coupon_id', $coupon->id)
        ->where('user_id', $user_id)
        ->count_all_results('coupon_uses');
    if ($already > 0) {
        return ['ok' => false, 'error' => 'You have already used this coupon.'];
    }

    $discount = $this->apply_discount($coupon, $subtotal);
    return ['ok' => true, 'coupon' => $coupon, 'discount' => $discount];
}

public function redeem($coupon_id, $user_id, $order_id)
{
    $this->db->trans_start();
    $this->db->insert('coupon_uses', [
        'coupon_id' => $coupon_id,
        'user_id'   => $user_id,
        'order_id'  => $order_id,
        'used_at'   => date('Y-m-d H:i:s'),
    ]);
    $this->db->set('used_count', 'used_count + 1', FALSE)
             ->where('id', $coupon_id)
             ->update($this->table);
    $this->db->trans_complete();
}
```

- [ ] **Step 3: Run test**

```bash
vendor/bin/phpunit tests/models/Coupon_model_test.php
```
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add application/models/Coupon_model.php tests/models/Coupon_model_test.php
git commit -m "feat: add Coupon_model::validate() and ::redeem()"
```

---

### Task 3: Api Checkout Endpoints (cart_summary, apply_coupon, payment_intent, stripe_webhook)

**Files:**
- Modify: `application/controllers/Api.php`
- Modify: `application/config/routes.php`

**Interfaces:**
- Produces: `GET /api/cart/summary` → `{ items, subtotal, item_count }`
- Produces: `POST /api/coupon/apply` → `{ ok, discount, error? }`
- Produces: `POST /api/payment/intent` → `{ client_secret, publishable_key, order_data }`
- Produces: `POST /api/webhook/stripe` → `200 OK`

- [ ] **Step 1: Add cart_summary() to Api.php**

Add before the `// --- Helpers ---` block:

```php
// GET /api/cart/summary
public function cart_summary()
{
    $this->load->model('cart_model');
    $user_id    = $this->current_user ? $this->current_user->id : null;
    $session_id = $this->session->session_id;
    $cart  = $this->cart_model->get_or_create($session_id, $user_id);
    $items = $this->cart_model->get_items($cart->id);

    $subtotal = 0;
    $out      = [];
    foreach ($items as $item) {
        $unit      = $item->sale_price ?: $item->price;
        $line      = $unit * $item->quantity;
        $subtotal += $line;
        $out[]     = [
            'id'         => (int)$item->id,
            'name'       => $item->name,
            'image'      => $item->image ? base_url($item->image) : null,
            'unit_price' => (float)$unit,
            'quantity'   => (int)$item->quantity,
            'line_total' => (float)$line,
            'store_name' => $item->store_name,
        ];
    }

    $this->_json([
        'items'      => $out,
        'subtotal'   => (float)$subtotal,
        'item_count' => count($out),
    ]);
}

// POST /api/coupon/apply
// Body: { coupon_code, subtotal }
public function apply_coupon()
{
    if (!$this->current_user) {
        $this->_json(['ok' => false, 'error' => 'Login required'], 401);
        return;
    }
    $this->load->model('coupon_model');
    $code     = $this->input->post('coupon_code');
    $subtotal = (float) $this->input->post('subtotal');
    $result   = $this->coupon_model->validate($code, $this->current_user->id, $subtotal);

    $resp = ['ok' => $result['ok'], 'csrf' => $this->_new_csrf()];
    if ($result['ok']) {
        $resp['discount']    = (float)$result['discount'];
        $resp['coupon_code'] = strtoupper($code);
    } else {
        $resp['error'] = $result['error'];
    }
    $this->_json($resp);
}

// POST /api/payment/intent
// Body: { shipping (JSON), coupon_code? }
public function payment_intent()
{
    if (!$this->current_user) {
        $this->_json(['error' => 'Login required'], 401);
        return;
    }

    $this->load->model('cart_model');
    $this->load->model('coupon_model');
    $this->load->library('payment');

    $user_id    = $this->current_user->id;
    $session_id = $this->session->session_id;
    $cart  = $this->cart_model->get_or_create($session_id, $user_id);
    $items = $this->cart_model->get_items($cart->id);

    if (empty($items)) {
        $this->_json(['error' => 'Cart is empty'], 422);
        return;
    }

    $subtotal = 0;
    foreach ($items as $item) {
        $unit      = $item->sale_price ?: $item->price;
        $subtotal += $unit * $item->quantity;
    }

    $shipping_cost = 10.00;
    $discount      = 0.00;
    $coupon_code   = $this->input->post('coupon_code');

    if ($coupon_code) {
        $validation = $this->coupon_model->validate($coupon_code, $user_id, $subtotal);
        if ($validation['ok']) {
            $discount = $validation['discount'];
        }
    }

    $total_cents = (int) round(($subtotal + $shipping_cost - $discount) * 100);
    if ($total_cents < 50) {
        $this->_json(['error' => 'Order total too low for payment processing'], 422);
        return;
    }

    $intent = $this->payment->create_payment_intent($total_cents, null, [
        'user_id'      => $user_id,
        'coupon_code'  => $coupon_code ?: '',
        'shipping_cost'=> $shipping_cost,
        'discount'     => $discount,
    ]);

    $this->_json([
        'client_secret'   => $intent->client_secret,
        'publishable_key' => $this->payment->get_publishable_key(),
        'total'           => $subtotal + $shipping_cost - $discount,
        'csrf'            => $this->_new_csrf(),
    ]);
}

// POST /api/webhook/stripe
public function stripe_webhook()
{
    $payload    = file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    $this->load->library('payment');
    $this->load->model('order_model');

    try {
        $event = $this->payment->construct_webhook_event($payload, $sig_header);
    } catch (\Exception $e) {
        $this->output->set_status_header(400)->set_output('Webhook error: ' . $e->getMessage());
        return;
    }

    if ($event->type === 'payment_intent.succeeded') {
        $intent   = $event->data->object;
        $order_id = $intent->metadata->order_id ?? null;
        if ($order_id) {
            $this->db->update('orders',   ['status' => ORDER_PAID], ['id' => $order_id]);
            $this->db->update('payments', ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')], ['gateway_ref' => $intent->id]);
        }
    }

    $this->output->set_status_header(200)->set_output(json_encode(['received' => true]));
}
```

- [ ] **Step 2: Add routes**

In `application/config/routes.php`, add:
```php
$route['api/cart/summary']      = 'api/cart_summary';
$route['api/coupon/apply']      = 'api/apply_coupon';
$route['api/payment/intent']    = 'api/payment_intent';
$route['api/webhook/stripe']    = 'api/stripe_webhook';
```

- [ ] **Step 3: Commit**

```bash
git add application/controllers/Api.php application/config/routes.php
git commit -m "feat: add cart_summary, coupon, payment_intent, and stripe_webhook Api endpoints"
```

---

### Task 4: CheckoutForm.vue + Vite entry

**Files:**
- Create: `vue-src/src/components/CheckoutForm.vue`
- Create: `vue-src/src/entries/checkout.js`
- Modify: `vue-src/vite.config.js`

**Interfaces:**
- Consumes: `POST /api/payment/intent` from Task 3
- Consumes: `POST /api/coupon/apply` from Task 3
- Consumes: `data-csrf-name`, `data-csrf-hash`, `data-items` (JSON), `data-subtotal`, `data-shipping` on `#checkout-form` div
- Produces: Stripe card payment flow, redirects to `/checkout/confirm?payment_intent=xxx`

- [ ] **Step 1: Add checkout entry to `vue-src/vite.config.js`**

Update the `input` object in `rollupOptions`:
```js
input: {
  search:   resolve(__dirname, 'src/entries/search.js'),
  product:  resolve(__dirname, 'src/entries/product.js'),
  checkout: resolve(__dirname, 'src/entries/checkout.js'),
},
```

- [ ] **Step 2: Create `vue-src/src/components/CheckoutForm.vue`**

```vue
<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  csrfName:    { type: String, required: true },
  csrfHash:    { type: String, required: true },
  items:       { type: Array,  required: true },
  subtotal:    { type: Number, required: true },
  shipping:    { type: Number, default: 10 },
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
  'Johor','Kedah','Kelantan','Melaka','Negeri Sembilan',
  'Pahang','Penang','Perak','Perlis','Sabah','Sarawak',
  'Selangor','Terengganu','Kuala Lumpur','Labuan','Putrajaya',
]

const stripe     = ref(null)
const cardEl     = ref(null)
const cardWidget = ref(null)
const paying     = ref(false)
const payError   = ref('')

onMounted(async () => {
  if (typeof Stripe === 'undefined') return
  // Fetch publishable key from server first
  const res  = await fetch('/api/payment/intent', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ [csrfName]: csrfHash, _preflight: '1' }),
  })
  const data = await res.json()
  if (data.csrf) { csrfName = data.csrf.name; csrfHash = data.csrf.hash }

  stripe.value = Stripe(data.publishable_key)
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
    const res  = await fetch('/api/coupon/apply', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
    const data = await res.json()
    if (data.csrf) { csrfName = data.csrf.name; csrfHash = data.csrf.hash }
    if (data.ok) {
      couponApplied.value  = true
      couponDiscount.value = data.discount
    } else {
      couponError.value = data.error
    }
  } finally {
    couponLoading.value = false
  }
}

async function pay() {
  payError.value = ''
  paying.value   = true

  try {
    // 1. Get PaymentIntent client_secret
    const body = new URLSearchParams({
      coupon_code: couponApplied.value ? couponCode.value : '',
      shipping:    JSON.stringify(form.value),
      [csrfName]:  csrfHash,
    })
    const res  = await fetch('/api/payment/intent', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body })
    const data = await res.json()
    if (data.csrf) { csrfName = data.csrf.name; csrfHash = data.csrf.hash }
    if (!res.ok) { payError.value = data.error; return }

    // 2. Confirm payment with Stripe.js
    const { error, paymentIntent } = await stripe.value.confirmCardPayment(data.client_secret, {
      payment_method: {
        card: cardWidget.value,
        billing_details: { name: form.value.full_name },
      },
    })
    if (error) { payError.value = error.message; return }

    // 3. Redirect to server-side confirm endpoint
    window.location.href = `/checkout/confirm?payment_intent=${paymentIntent.id}&coupon=${encodeURIComponent(couponApplied.value ? couponCode.value : '')}&${encodeURIComponent(JSON.stringify(form.value))}`
    // Simpler: store shipping in sessionStorage, redirect with just the PI id
    sessionStorage.setItem('checkout_shipping', JSON.stringify(form.value))
    sessionStorage.setItem('checkout_coupon',   couponApplied.value ? couponCode.value : '')
    window.location.href = `/checkout/confirm?payment_intent=${paymentIntent.id}`
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
          <div v-if="couponApplied" class="alert alert-success py-2">
            Coupon applied! You save RM {{ couponDiscount.toFixed(2) }}
          </div>
          <div v-else>
            <div class="input-group">
              <input v-model="couponCode" type="text" class="form-control" placeholder="Enter code" :disabled="couponLoading" />
              <button @click="applyCoupon" class="btn btn-outline-secondary" :disabled="couponLoading || !couponCode">
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
          <li v-for="item in items" :key="item.id"
              class="list-group-item d-flex justify-content-between align-items-start">
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
          <hr>
          <div class="d-flex justify-content-between fw-bold fs-5">
            <span>Total</span><span>RM {{ total.toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
```

- [ ] **Step 3: Create `vue-src/src/entries/checkout.js`**

```js
import { createApp } from 'vue'
import CheckoutForm from '../components/CheckoutForm.vue'

const el = document.getElementById('checkout-form')
if (el) {
  const items    = JSON.parse(el.dataset.items    || '[]')
  const subtotal = parseFloat(el.dataset.subtotal || '0')
  const shipping = parseFloat(el.dataset.shipping || '10')

  createApp(CheckoutForm, {
    csrfName: el.dataset.csrfName,
    csrfHash: el.dataset.csrfHash,
    items,
    subtotal,
    shipping,
  }).mount(el)
}
```

- [ ] **Step 4: Build**

```bash
cd vue-src && npm run build
```

Expected: `assets/js/checkout.js` created.

- [ ] **Step 5: Commit**

```bash
git add vue-src/src/ assets/js/checkout.js
git commit -m "feat: add CheckoutForm Vue component with Stripe Elements + coupon"
```

---

### Task 5: Cart Controller — Stripe checkout and confirm

**Files:**
- Modify: `application/controllers/Cart.php`
- Modify: `application/views/cart/checkout.php`

**Interfaces:**
- Consumes: `CheckoutForm.vue` from Task 4 — reads `sessionStorage.checkout_shipping` and `sessionStorage.checkout_coupon` on redirect
- Produces: `Cart::checkout()` renders Vue mount point with Stripe.js loaded
- Produces: `Cart::confirm()` → verifies PaymentIntent, creates order, redeems coupon, clears cart

- [ ] **Step 1: Rewrite `Cart::checkout()` in `application/controllers/Cart.php`**

Replace the existing `checkout()` method:

```php
public function checkout()
{
    $this->require_login();

    $cart  = $this->_get_cart();
    $items = $this->cart_model->get_items($cart->id);

    if (empty($items)) {
        $this->redirect_with_message('cart', 'Your cart is empty.', 'error');
    }

    $this->load->library('payment');

    $subtotal = 0;
    foreach ($items as $item) {
        $unit            = $item->sale_price ?: $item->price;
        $item->unit_price = $unit;
        $item->line_total = $unit * $item->quantity;
        $subtotal        += $item->line_total;
    }

    $out_items = array_map(function($item) {
        return array(
            'id'         => $item->id,
            'name'       => $item->name,
            'image'      => $item->image ? base_url($item->image) : null,
            'unit_price' => (float)$item->unit_price,
            'quantity'   => (int)$item->quantity,
            'line_total' => (float)$item->line_total,
            'store_name' => $item->store_name,
        );
    }, $items);

    $this->_render('cart/checkout', array(
        'page_title'    => 'Checkout',
        'items_json'    => json_encode($out_items),
        'subtotal'      => $subtotal,
        'shipping_cost' => 10.00,
        'scripts'       => array('checkout'),
    ));
}
```

- [ ] **Step 2: Rewrite `Cart::confirm()` in `application/controllers/Cart.php`**

Replace the existing `confirm()` method:

```php
public function confirm()
{
    $this->require_login();

    $payment_intent_id = $this->input->get('payment_intent');
    if (!$payment_intent_id) {
        $this->redirect_with_message('checkout', 'Invalid payment reference.', 'error');
    }

    $this->load->library('payment');
    $this->load->model('coupon_model');

    try {
        $intent = $this->payment->retrieve_payment_intent($payment_intent_id);
    } catch (Exception $e) {
        $this->redirect_with_message('checkout', 'Payment verification failed.', 'error');
        return;
    }

    if ($intent->status !== 'succeeded') {
        $this->redirect_with_message('checkout', 'Payment was not completed. Please try again.', 'error');
        return;
    }

    $cart  = $this->_get_cart();
    $items = $this->cart_model->get_items($cart->id);

    if (empty($items)) {
        redirect('orders');
        return;
    }

    // Retrieve shipping + coupon stored by Vue in sessionStorage via POST
    // CheckoutForm POSTs these on confirm redirect (see note below)
    $shipping_address = array(
        'full_name'    => $this->input->post('full_name',    TRUE) ?: '',
        'phone'        => $this->input->post('phone',        TRUE) ?: '',
        'address_line' => $this->input->post('address_line', TRUE) ?: '',
        'city'         => $this->input->post('city',         TRUE) ?: '',
        'postcode'     => $this->input->post('postcode',     TRUE) ?: '',
        'state'        => $this->input->post('state',        TRUE) ?: '',
    );
    $coupon_code = $this->input->post('coupon_code', TRUE) ?: ($intent->metadata->coupon_code ?? '');

    $shipping_cost = 10.00;
    $discount      = 0.00;

    if ($coupon_code) {
        $subtotal = 0;
        foreach ($items as $item) {
            $unit      = $item->sale_price ?: $item->price;
            $subtotal += $unit * $item->quantity;
        }
        $validation = $this->coupon_model->validate($coupon_code, $this->current_user->id, $subtotal);
        if ($validation['ok']) {
            $discount = $validation['discount'];
        }
    }

    $order_ids = $this->order_model->create_from_cart(
        $this->current_user->id,
        $items,
        $shipping_address,
        $shipping_cost,
        $discount
    );

    // Record payment rows
    foreach ($order_ids as $order_id) {
        $this->db->insert('payments', array(
            'order_id'    => $order_id,
            'gateway'     => 'stripe',
            'gateway_ref' => $intent->id,
            'amount'      => $intent->amount / 100,
            'status'      => 'paid',
            'paid_at'     => date('Y-m-d H:i:s'),
            'payload'     => json_encode($intent->toArray()),
        ));
        $this->db->update('orders', array('status' => ORDER_PAID), array('id' => $order_id));

        // Redeem coupon per order
        if ($coupon_code && isset($validation) && $validation['ok']) {
            $this->coupon_model->redeem($validation['coupon']->id, $this->current_user->id, $order_id);
        }
    }

    $this->cart_model->clear($cart->id);
    $this->session->set_flashdata('success', 'Payment confirmed! Order #' . implode(', #', $order_ids));
    redirect('orders');
}
```

- [ ] **Step 3: Rewrite `application/views/cart/checkout.php`**

Replace the entire file content:
```php
<h2 class="mb-4">Checkout</h2>

<!-- SRI intentionally omitted: Stripe hot-patches this script for security fixes
     and explicitly states in their docs that integrity= must NOT be used here.
     See: https://stripe.com/docs/security/guide#content-security-policy -->
<script src="https://js.stripe.com/v3/"></script>

<div id="checkout-form"
     data-items="<?= htmlspecialchars($items_json, ENT_QUOTES) ?>"
     data-subtotal="<?= $subtotal ?>"
     data-shipping="<?= $shipping_cost ?>"
     data-csrf-name="<?= $this->security->get_csrf_token_name() ?>"
     data-csrf-hash="<?= $this->security->get_csrf_hash() ?>">
    <!-- Vue CheckoutForm mounts here -->
    <div class="text-center py-5"><span class="spinner-border text-primary"></span></div>
</div>
```

- [ ] **Step 4: Build and test**

```bash
cd vue-src && npm run build
```

1. Add a product to cart as a logged-in buyer.
2. Navigate to `/checkout`.
3. Verify the Vue form renders with Stripe card element.
4. Use Stripe test card `4242 4242 4242 4242`, any future expiry, any CVC.
5. Submit. You should be redirected to `/orders` with a success message.

- [ ] **Step 5: Commit**

```bash
git add application/controllers/Cart.php application/views/cart/checkout.php assets/js/checkout.js
git commit -m "feat: Stripe PaymentIntent checkout flow with coupon support"
```

---

### Task 6: Seller Orders + Order Detail + Store Settings

**Files:**
- Modify: `application/controllers/Seller.php`
- Modify: `application/models/Order_model.php`
- Modify: `application/views/seller/orders.php`
- Modify: `application/views/seller/order_detail.php`
- Modify: `application/views/seller/store_settings.php`
- Modify: `application/config/routes.php`

**Interfaces:**
- Consumes: `Order_model::get_for_seller($store_id)` and `get_detail_for_seller($order_id, $store_id)`
- Produces: seller sees their orders with status badges; can enter tracking number to mark as shipped; can save store name/description/logo

- [ ] **Step 1: Write PHPUnit test for Order_model seller methods**

Create `tests/models/Order_model_test.php`:
```php
<?php
require_once __DIR__ . '/../bootstrap.php';
require_once APPPATH . 'models/Order_model.php';

class Order_model_test extends PHPUnit\Framework\TestCase
{
    public function test_get_for_seller_method_exists()
    {
        $this->assertTrue(method_exists('Order_model', 'get_for_seller'));
    }

    public function test_get_detail_for_seller_method_exists()
    {
        $this->assertTrue(method_exists('Order_model', 'get_detail_for_seller'));
    }
}
```

Run: `vendor/bin/phpunit tests/models/Order_model_test.php`
Expected: FAIL

- [ ] **Step 2: Add seller methods to `application/models/Order_model.php`**

Add after `get_items()`:
```php
public function get_for_seller($store_id, $limit = 20, $offset = 0)
{
    return $this->db
        ->select('o.*, u.full_name AS buyer_name, u.email AS buyer_email')
        ->from('orders o')
        ->join('users u', 'u.id = o.user_id')
        ->where('o.store_id', $store_id)
        ->order_by('o.created_at', 'DESC')
        ->limit($limit, $offset)
        ->get()->result();
}

public function count_for_seller($store_id)
{
    return $this->db->where('store_id', $store_id)->count_all_results($this->table);
}

public function get_detail_for_seller($order_id, $store_id)
{
    return $this->db
        ->select('o.*, u.full_name AS buyer_name, u.email AS buyer_email')
        ->from('orders o')
        ->join('users u', 'u.id = o.user_id')
        ->where('o.id', $order_id)
        ->where('o.store_id', $store_id)
        ->get()->row();
}
```

- [ ] **Step 3: Run test**

```bash
vendor/bin/phpunit tests/models/Order_model_test.php
```
Expected: PASS

- [ ] **Step 4: Implement `Seller::orders()` and `Seller::order_detail()` in `application/controllers/Seller.php`**

Replace the stub `orders()` method:
```php
public function orders()
{
    $per_page = 15;
    $page     = max(1, (int) $this->input->get('page'));
    $offset   = ($page - 1) * $per_page;
    $this->load->model('order_model');

    $this->_render('seller/orders', array(
        'page_title' => 'My Orders',
        'orders'     => $this->order_model->get_for_seller($this->store->id, $per_page, $offset),
        'total'      => $this->order_model->count_for_seller($this->store->id),
        'per_page'   => $per_page,
        'page'       => $page,
    ));
}
```

Replace the stub `order_detail()` method:
```php
public function order_detail($id)
{
    $this->load->model('order_model');
    $order = $this->order_model->get_detail_for_seller((int)$id, $this->store->id);
    if (!$order) show_error('Order not found.', 404);

    $this->form_validation->set_rules('tracking_number', 'Tracking Number', 'trim|max_length[100]');

    if ($this->input->post('tracking_number') !== false && $this->form_validation->run()) {
        $tracking = $this->input->post('tracking_number', TRUE);
        $update   = array('tracking_number' => $tracking);
        if ($order->status === ORDER_PROCESSING || $order->status === ORDER_PAID) {
            $update['status'] = ORDER_SHIPPED;
        }
        $this->db->update('orders', $update, array('id' => $order->id));
        if ($tracking) {
            $this->db->update('shipments', array(
                'tracking_number' => $tracking,
                'status'          => 'in_transit',
            ), array('order_id' => $order->id));
        }
        $this->redirect_with_message('seller/orders', 'Order updated.');
    }

    $this->_render('seller/order_detail', array(
        'page_title' => 'Order #' . $order->id,
        'order'      => $order,
        'items'      => $this->order_model->get_items($order->id),
        'address'    => json_decode($order->shipping_address),
    ));
}
```

- [ ] **Step 5: Implement `Seller::save_store_settings()` in `application/controllers/Seller.php`**

Add a new method after `store_settings()`:
```php
public function save_store_settings()
{
    $this->form_validation->set_rules('name',        'Store Name',  'required|trim|max_length[255]');
    $this->form_validation->set_rules('description', 'Description', 'trim');

    if ($this->form_validation->run() === FALSE) {
        $this->redirect_with_message('seller/store-settings', 'Please fill in all required fields.', 'error');
        return;
    }

    $data = array(
        'name'        => $this->input->post('name',        TRUE),
        'description' => $this->input->post('description', TRUE),
    );
    $this->store_model->update($this->store->id, $data);
    $this->redirect_with_message('seller/store-settings', 'Store settings saved.');
}
```

- [ ] **Step 6: Add route for save_store_settings**

In `application/config/routes.php`, add:
```php
$route['seller/store-settings']      = 'seller/store_settings';
$route['seller/store-settings/save'] = 'seller/save_store_settings';
```

- [ ] **Step 7: Write `application/views/seller/orders.php`**

Replace the entire file:
```php
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">My Orders</h2>
    <span class="text-muted"><?= $total ?> total</span>
</div>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">No orders yet.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Buyer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order->id ?></td>
                <td><?= htmlspecialchars($order->buyer_name) ?></td>
                <td class="text-muted small"><?= $order->buyer_email ?></td>
                <td>RM <?= number_format($order->total, 2) ?></td>
                <td>
                    <?php
                    $badge = [
                        'pending'    => 'secondary',
                        'paid'       => 'info',
                        'processing' => 'primary',
                        'shipped'    => 'warning',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                    ][$order->status] ?? 'light';
                    ?>
                    <span class="badge bg-<?= $badge ?>"><?= ucfirst($order->status) ?></span>
                </td>
                <td class="text-muted small"><?= date('d M Y', strtotime($order->created_at)) ?></td>
                <td><a href="<?= base_url('seller/orders/' . $order->id) ?>" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
```

- [ ] **Step 8: Write `application/views/seller/order_detail.php`**

Replace the entire file:
```php
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Order #<?= $order->id ?></h2>
    <a href="<?= base_url('seller/orders') ?>" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header fw-semibold">Items</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->product_name_snapshot) ?></td>
                        <td><?= $item->quantity ?></td>
                        <td>RM <?= number_format($item->unit_price, 2) ?></td>
                        <td>RM <?= number_format($item->unit_price * $item->quantity, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="card-footer text-end fw-bold">
                Total: RM <?= number_format($order->total, 2) ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Shipping Address</div>
            <div class="card-body">
                <?php if ($address): ?>
                <p class="mb-1"><?= htmlspecialchars($address->full_name ?? '') ?> — <?= htmlspecialchars($address->phone ?? '') ?></p>
                <p class="mb-1"><?= htmlspecialchars($address->address_line ?? '') ?></p>
                <p class="mb-0"><?= htmlspecialchars(($address->postcode ?? '') . ' ' . ($address->city ?? '') . ', ' . ($address->state ?? '')) ?></p>
                <?php else: ?>
                <p class="text-muted">No address recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header fw-semibold">Order Status &amp; Tracking</div>
            <div class="card-body">
                <p>Status: <strong><?= ucfirst($order->status) ?></strong></p>

                <?php echo form_open('seller/orders/' . $order->id); ?>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Tracking Number</label>
                    <input type="text" name="tracking_number" class="form-control"
                           value="<?= htmlspecialchars($order->tracking_number ?? '') ?>"
                           placeholder="e.g. PO123456789MY">
                    <div class="form-text">Saving a tracking number marks the order as Shipped.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Save</button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 9: Update `application/views/seller/store_settings.php`** to include a save form

Replace the entire file:
```php
<h2 class="mb-4">Store Settings</h2>

<?php echo form_open('seller/store-settings/save'); ?>
<?= csrf_field() ?>
<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Store Name *</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($store->name) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($store->description ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
</div>
<?php echo form_close(); ?>
```

- [ ] **Step 10: Run tests**

```bash
vendor/bin/phpunit tests/models/Order_model_test.php
```
Expected: PASS

- [ ] **Step 11: Test in browser**

1. Log in as a seller. Navigate to `/seller/orders` — order list should appear.
2. Click View on an order. Verify items and address show.
3. Enter a tracking number and save. Verify status changes to Shipped.
4. Navigate to `/seller/store-settings`. Update name and save. Verify it persists.

- [ ] **Step 12: Commit**

```bash
git add application/controllers/Seller.php application/models/Order_model.php application/views/seller/ application/config/routes.php tests/models/Order_model_test.php
git commit -m "feat: implement seller order management and store settings save"
```
