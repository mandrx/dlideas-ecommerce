<h1 class="auth-page-title">Welcome back</h1>
<p class="auth-page-sub">Sign in to your account to continue shopping.</p>

<?= form_open('login/post') ?>

<div class="auth-field">
    <label class="auth-label" for="email">Email address</label>
    <input
        type="email"
        class="auth-input"
        id="email"
        name="email"
        value="<?= htmlspecialchars(set_value('email')) ?>"
        placeholder="you@example.com"
        autocomplete="email"
        autofocus
        required>
</div>

<div class="auth-field">
    <div class="auth-label-row">
        <label class="auth-label" for="password">Password</label>
        <a href="<?= base_url('forgot-password') ?>" class="auth-forgot-link">Forgot password?</a>
    </div>
    <div class="auth-input-wrap">
        <input
            type="password"
            class="auth-input"
            id="password"
            name="password"
            placeholder="Enter your password"
            autocomplete="current-password"
            required>
        <button type="button" class="auth-pw-toggle" aria-label="Show password">
            <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none">
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
        </button>
    </div>
</div>

<button type="submit" class="auth-btn">Sign in</button>

<?= form_close() ?>

<p class="auth-switch">Don't have an account? <a href="<?= base_url('register') ?>">Create one — it's free</a></p>

<div class="test-accounts">
    <button type="button" class="test-accounts-toggle" aria-expanded="true" aria-controls="test-accounts-list">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Test accounts
        <svg class="test-accounts-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <ul class="test-accounts-list" id="test-accounts-list">
        <li>
            <div class="ta-info">
                <span class="ta-badge ta-badge--admin">Admin</span>
                <span class="ta-desc">Platform admin</span>
                <span class="ta-email">admin@ci3ecomm.local</span>
                <span class="ta-password">Admin@1234</span>
            </div>
            <button type="button" class="ta-fill-btn" data-email="admin@ci3ecomm.local" data-password="Admin@1234">Use</button>
        </li>
        <li>
            <div class="ta-info">
                <span class="ta-badge ta-badge--seller">Seller</span>
                <span class="ta-desc">Alice Tech Store</span>
                <span class="ta-email">alice@example.com</span>
                <span class="ta-password">Test@1234</span>
            </div>
            <button type="button" class="ta-fill-btn" data-email="alice@example.com" data-password="Test@1234">Use</button>
        </li>
        <li>
            <div class="ta-info">
                <span class="ta-badge ta-badge--seller">Seller</span>
                <span class="ta-desc">Bob Fashion Hub</span>
                <span class="ta-email">bob@example.com</span>
                <span class="ta-password">Test@1234</span>
            </div>
            <button type="button" class="ta-fill-btn" data-email="bob@example.com" data-password="Test@1234">Use</button>
        </li>
        <li>
            <div class="ta-info">
                <span class="ta-badge ta-badge--buyer">Buyer</span>
                <span class="ta-desc">Regular buyer</span>
                <span class="ta-email">dave@example.com</span>
                <span class="ta-password">Test@1234</span>
            </div>
            <button type="button" class="ta-fill-btn" data-email="dave@example.com" data-password="Test@1234">Use</button>
        </li>
        <li>
            <div class="ta-info">
                <span class="ta-badge ta-badge--buyer">Buyer</span>
                <span class="ta-desc">Regular buyer</span>
                <span class="ta-email">eve@example.com</span>
                <span class="ta-password">Test@1234</span>
            </div>
            <button type="button" class="ta-fill-btn" data-email="eve@example.com" data-password="Test@1234">Use</button>
        </li>
        <li>
            <div class="ta-info">
                <span class="ta-badge ta-badge--buyer">Buyer</span>
                <span class="ta-desc">Original test buyer</span>
                <span class="ta-email">buyer@test.com</span>
                <span class="ta-password">Test@1234</span>
            </div>
            <button type="button" class="ta-fill-btn" data-email="buyer@test.com" data-password="Test@1234">Use</button>
        </li>
    </ul>
</div>
