<link rel="stylesheet" href="<?= base_url('assets/js/checkout.css') ?>">
<div class="dl-demo-alert" role="alert">
    <strong>Demo Only — Do not place a real order.</strong> This checkout is a portfolio demonstration. No real products will be shipped.
</div>

<h2 class="mb-4" style="font-family:'Baloo 2',sans-serif;font-weight:800;">Checkout</h2>

<!-- SRI intentionally omitted: Stripe hot-patches this script for security fixes
     and explicitly states in their docs that integrity= must NOT be used here.
     See: https://stripe.com/docs/security/guide#content-security-policy -->
<script src="https://js.stripe.com/v3/"></script>

<div id="checkout-form"
     data-items="<?= htmlspecialchars($items_json, ENT_QUOTES) ?>"
     data-subtotal="<?= $subtotal ?>"
     data-shipping="<?= $shipping_cost ?>"
     data-csrf-name="<?= $this->security->get_csrf_token_name() ?>"
     data-csrf-hash="<?= $this->security->get_csrf_hash() ?>"
     data-stripe-key="<?= htmlspecialchars($stripe_key, ENT_QUOTES) ?>">
    <!-- Vue CheckoutForm mounts here -->
    <div class="text-center py-5"><span class="spinner-border text-primary"></span></div>
</div>
