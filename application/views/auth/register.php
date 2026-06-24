<h1 class="auth-page-title">Create your account</h1>
<p class="auth-page-sub">Join thousands of families shopping local on DLIdeas.</p>

<?= form_open('register/post') ?>

<div class="auth-field">
    <label class="auth-label" for="full_name">Full name</label>
    <input
        type="text"
        class="auth-input"
        id="full_name"
        name="full_name"
        value="<?= htmlspecialchars(set_value('full_name')) ?>"
        placeholder="Jane Tan"
        autocomplete="name"
        autofocus
        required>
</div>

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
        required>
</div>

<div class="auth-field">
    <label class="auth-label" for="phone">Phone <span class="auth-label-hint">(optional)</span></label>
    <input
        type="tel"
        class="auth-input"
        id="phone"
        name="phone"
        value="<?= htmlspecialchars(set_value('phone')) ?>"
        placeholder="+65 9123 4567"
        autocomplete="tel">
</div>

<div class="auth-field">
    <label class="auth-label" for="password">Password</label>
    <div class="auth-input-wrap">
        <input
            type="password"
            class="auth-input"
            id="password"
            name="password"
            placeholder="At least 8 characters"
            autocomplete="new-password"
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

<div class="auth-field">
    <label class="auth-label" for="password_confirm">Confirm password</label>
    <div class="auth-input-wrap">
        <input
            type="password"
            class="auth-input"
            id="password_confirm"
            name="password_confirm"
            placeholder="Re-enter your password"
            autocomplete="new-password"
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

<button type="submit" class="auth-btn">Create account</button>

<?= form_close() ?>

<p class="auth-terms">By creating an account you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>

<p class="auth-switch">Already have an account? <a href="<?= base_url('login') ?>">Sign in</a></p>
