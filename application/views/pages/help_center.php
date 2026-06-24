<div class="dl-page-hero">
    <div class="dl-page-hero-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <h1>Help Center</h1>
    <p>Answers to the most common questions about shopping, selling, and your account.</p>
</div>

<div class="dl-page-content">

    <div class="dl-help-topics">
        <a href="#orders" class="dl-help-topic-chip">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Orders &amp; Shipping
        </a>
        <a href="#payments" class="dl-help-topic-chip">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payments &amp; Coupons
        </a>
        <a href="#account" class="dl-help-topic-chip">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Account &amp; Security
        </a>
    </div>

    <section class="dl-page-section" id="orders">
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Orders &amp; Shipping
        </h2>
        <div class="dl-faq">
            <div class="dl-faq-item">
                <h3>How do I track my order?</h3>
                <p>Log in and visit <a href="<?= base_url('orders') ?>">My Orders</a>. Each order card shows its current status — Pending, Processing, Shipped, or Delivered — updated in real time as sellers action your order.</p>
            </div>
            <div class="dl-faq-item">
                <h3>When will my order be shipped?</h3>
                <p>Sellers typically process and ship orders within <strong>1–3 business days</strong> of payment. You'll see the status change to Shipped once the seller dispatches your package.</p>
            </div>
            <div class="dl-faq-item">
                <h3>Can I cancel an order?</h3>
                <p>Cancellations are possible while an order is still in <em>Pending</em> status. Once a seller starts processing, reach out via the <a href="<?= base_url('contact') ?>">Contact page</a> with your order number and we'll do our best to help.</p>
            </div>
        </div>
    </section>

    <section class="dl-page-section" id="payments">
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Payments &amp; Coupons
        </h2>
        <div class="dl-faq">
            <div class="dl-faq-item">
                <h3>What payment methods are accepted?</h3>
                <p>We accept all major credit and debit cards — Visa, Mastercard, and Amex — via <strong>Stripe</strong>. All transactions are encrypted end-to-end. Your card details are never stored on our servers.</p>
            </div>
            <div class="dl-faq-item">
                <h3>How do I use a coupon code?</h3>
                <p>At checkout, enter your code in the <em>Coupon Code</em> field and click <strong>Apply</strong>. If the code is valid, your order total updates immediately before you confirm payment.</p>
            </div>
            <div class="dl-faq-item">
                <h3>Is it safe to pay here? (Demo note)</h3>
                <p>This is a demo build. To test checkout without real charges, use Stripe's test card: <strong>4242 4242 4242 4242</strong>, any future expiry, and any 3-digit CVC.</p>
            </div>
        </div>
    </section>

    <section class="dl-page-section" id="account">
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Account &amp; Security
        </h2>
        <div class="dl-faq">
            <div class="dl-faq-item">
                <h3>How do I reset my password?</h3>
                <p>Go to <a href="<?= base_url('forgot-password') ?>">Forgot Password</a>, enter your email address, and follow the reset link sent to your inbox. Links expire after 1 hour.</p>
            </div>
            <div class="dl-faq-item">
                <h3>How do I become a seller?</h3>
                <p>Register for a buyer account first, then submit a <a href="<?= base_url('apply-seller') ?>">Seller Application</a>. Our team reviews applications within 2 business days and will notify you by email on approval.</p>
            </div>
            <div class="dl-faq-item">
                <h3>Can I have multiple roles?</h3>
                <p>Each account holds one role at a time — buyer, seller, or admin. If you're approved as a seller, your account switches to seller mode and you gain access to the seller dashboard.</p>
            </div>
        </div>
    </section>

    <div class="dl-page-cta">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <p>Still have questions? <a href="<?= base_url('contact') ?>">Contact Ahmad directly</a> — usually replies within a day.</p>
    </div>

</div>
