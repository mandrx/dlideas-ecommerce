<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — DLIdeas' : 'DLIdeas' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <style>
        html, body { height: 100%; margin: 0; }

        .auth-shell {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ── Brand panel ── */
        .auth-brand {
            background: var(--primary);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: var(--space-8) var(--space-7);
            position: relative;
            overflow: hidden;
        }

        .auth-brand::before,
        .auth-brand::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }
        .auth-brand::before {
            width: 340px; height: 340px;
            bottom: -100px; right: -120px;
            background: oklch(68% 0.16 33 / 0.30);
        }
        .auth-brand::after {
            width: 200px; height: 200px;
            top: -60px; left: -70px;
            background: oklch(72% 0.14 33 / 0.22);
        }

        .auth-brand-inner {
            position: relative;
            z-index: 1;
        }

        .auth-brand-logo {
            display: block;
            text-decoration: none;
            margin-bottom: var(--space-9);
        }
        .auth-brand-logo img {
            width: 160px;
            height: auto;
            object-fit: contain;
            filter: invert(1) brightness(2);
            display: block;
        }

        .auth-brand-headline {
            font-family: 'Baloo 2', system-ui, sans-serif;
            font-weight: 800;
            font-size: clamp(1.6rem, 2.5vw, 2.1rem);
            color: var(--text-on-primary);
            line-height: 1.18;
            letter-spacing: -0.02em;
            margin-bottom: var(--space-4);
        }
        .auth-brand-sub {
            font-family: 'Nunito', system-ui, sans-serif;
            font-weight: 600;
            font-size: 0.9375rem;
            color: oklch(96% 0.012 33 / 0.82);
            line-height: 1.55;
            max-width: 30ch;
            margin-bottom: var(--space-7);
        }

        .auth-trust-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }
        .auth-trust-list li {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            font-family: 'Nunito', system-ui, sans-serif;
            font-weight: 700;
            font-size: 0.875rem;
            color: oklch(96% 0.012 33 / 0.88);
        }
        .auth-trust-list li svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            background: oklch(96% 0.012 33 / 0.18);
            border-radius: 50%;
            padding: 3px;
        }

        .auth-brand-footer {
            position: relative;
            z-index: 1;
        }
        .auth-back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Nunito', system-ui, sans-serif;
            font-weight: 800;
            font-size: 1rem;
            color: var(--text-on-primary);
            text-decoration: none;
            background: oklch(98% 0.01 33);
            color: var(--primary);
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            transition: background var(--t-fast) var(--ease-out), gap var(--t-fast) var(--ease-out);
        }
        .auth-back-link:hover { background: white; gap: 12px; }
        .auth-back-link svg { transition: transform var(--t-fast) var(--ease-out); }
        .auth-back-link:hover svg { transform: translateX(-3px); }

        /* ── Form panel ── */
        .auth-form-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: var(--space-8) clamp(var(--space-7), 8vw, 96px);
            background: var(--bg-body);
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 400px;
        }

        .auth-page-title {
            font-family: 'Baloo 2', system-ui, sans-serif;
            font-weight: 800;
            font-size: 1.75rem;
            color: var(--text-dark);
            letter-spacing: -0.02em;
            margin-bottom: var(--space-2);
            line-height: 1.15;
        }
        .auth-page-sub {
            font-family: 'Nunito', system-ui, sans-serif;
            font-size: 0.9375rem;
            color: oklch(42% 0.015 33);
            margin-bottom: var(--space-6);
            line-height: 1.5;
        }

        /* Flash messages */
        .auth-flash {
            border-radius: var(--radius-sm);
            padding: var(--space-3) var(--space-4);
            margin-bottom: var(--space-5);
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            font-size: 0.875rem;
        }
        .auth-flash-error {
            background: var(--danger-light);
            color: var(--danger);
        }
        .auth-flash-success {
            background: var(--success-light);
            color: var(--success);
        }

        /* Form controls */
        .auth-field { margin-bottom: var(--space-4); }

        .auth-label-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .auth-label {
            display: block;
            font-family: 'Nunito', system-ui, sans-serif;
            font-weight: 700;
            font-size: 0.8125rem;
            color: var(--text-dark);
            letter-spacing: 0.01em;
        }
        .auth-forgot-link {
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            font-size: 0.8125rem;
            color: var(--primary);
            text-decoration: none;
            transition: color var(--t-fast);
        }
        .auth-forgot-link:hover { color: var(--primary-hover); text-decoration: underline; }

        /* Input wrapper for password toggle */
        .auth-input-wrap {
            position: relative;
        }
        .auth-input-wrap .auth-input {
            padding-right: 44px;
        }
        .auth-pw-toggle {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            cursor: pointer;
            color: oklch(55% 0.01 33);
            transition: color var(--t-fast);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }
        .auth-pw-toggle:hover { color: var(--text-dark); }
        .auth-pw-toggle:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: -2px;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }

        .auth-input {
            width: 100%;
            padding: 11px var(--space-4);
            border: 1.5px solid var(--border-strong);
            border-radius: var(--radius-sm);
            background: var(--bg-card);
            font-family: 'Nunito', system-ui, sans-serif;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-dark);
            outline: none;
            transition: border-color var(--t-fast) var(--ease-out), box-shadow var(--t-fast) var(--ease-out);
            appearance: none;
        }
        .auth-input::placeholder { color: oklch(65% 0.012 33); font-weight: 400; }
        .auth-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px oklch(62% 0.18 33 / 0.12);
        }

        /* Submit button */
        .auth-btn {
            width: 100%;
            padding: 13px var(--space-5);
            background: var(--primary);
            color: var(--text-on-primary);
            border: none;
            border-radius: var(--radius-sm);
            font-family: 'Baloo 2', system-ui, sans-serif;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: background var(--t-fast) var(--ease-out), transform var(--t-fast) var(--ease-out), box-shadow var(--t-fast) var(--ease-out);
            margin-top: var(--space-3);
            letter-spacing: 0.01em;
        }
        .auth-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-hover);
        }
        .auth-btn:active { transform: translateY(0); box-shadow: none; }
        .auth-btn:focus-visible {
            outline: 3px solid oklch(62% 0.18 33 / 0.55);
            outline-offset: 2px;
        }

        /* Bottom switch link */
        .auth-switch {
            margin-top: var(--space-5);
            text-align: center;
            font-family: 'Nunito', sans-serif;
            font-size: 0.875rem;
            color: oklch(48% 0.012 33);
        }
        .auth-switch a {
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            transition: color var(--t-fast);
        }
        .auth-switch a:hover { color: var(--primary-hover); text-decoration: underline; }
        .auth-switch a:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
            border-radius: 2px;
        }

        /* Test accounts widget */
        .test-accounts {
            margin-top: var(--space-5);
            border: 1.5px dashed var(--border-strong);
            border-radius: var(--radius-sm);
            overflow: hidden;
        }
        .test-accounts-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 10px var(--space-4);
            background: none;
            border: none;
            cursor: pointer;
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            font-size: 0.8125rem;
            color: oklch(48% 0.012 33);
            text-align: left;
            transition: background var(--t-fast);
        }
        .test-accounts-toggle:hover { background: var(--bg-alt); }
        .test-accounts-toggle svg:first-child { color: oklch(55% 0.08 33); flex-shrink: 0; }
        .test-accounts-chevron {
            margin-left: auto;
            transition: transform var(--t-fast) var(--ease-out);
            flex-shrink: 0;
        }
        .test-accounts-toggle[aria-expanded="true"] .test-accounts-chevron {
            transform: rotate(180deg);
        }

        .test-accounts-list {
            list-style: none;
            margin: 0;
            padding: 0 var(--space-3) var(--space-3);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .test-accounts-list li {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: 8px 10px;
            border-radius: calc(var(--radius-sm) - 2px);
            background: var(--bg-alt);
        }
        .ta-info {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 0;
            flex-wrap: wrap;
        }
        .ta-badge {
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 0.6875rem;
            padding: 2px 7px;
            border-radius: 99px;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }
        .ta-badge--admin  { background: oklch(88% 0.08 33);  color: oklch(38% 0.12 33); }
        .ta-badge--seller { background: oklch(88% 0.10 200); color: oklch(36% 0.12 200); }
        .ta-badge--buyer  { background: oklch(90% 0.08 145); color: oklch(36% 0.12 145); }
        .ta-desc {
            font-family: 'Nunito', sans-serif;
            font-weight: 600;
            font-size: 0.8125rem;
            color: var(--text-dark);
        }
        .ta-email {
            font-family: 'Nunito', sans-serif;
            font-size: 0.75rem;
            color: oklch(52% 0.012 33);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ta-password {
            font-family: monospace;
            font-size: 0.75rem;
            color: oklch(48% 0.08 33);
            background: oklch(94% 0.04 33 / 0.6);
            padding: 1px 6px;
            border-radius: 4px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .ta-fill-btn {
            flex-shrink: 0;
            padding: 4px 12px;
            background: var(--primary);
            color: var(--text-on-primary);
            border: none;
            border-radius: calc(var(--radius-sm) - 2px);
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            font-size: 0.75rem;
            cursor: pointer;
            transition: background var(--t-fast), transform var(--t-fast);
        }
        .ta-fill-btn:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .ta-fill-btn:active { transform: translateY(0); }
        .ta-fill-btn:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

        /* Terms note */
        .auth-terms {
            margin-top: var(--space-4);
            text-align: center;
            font-family: 'Nunito', sans-serif;
            font-size: 0.75rem;
            color: oklch(58% 0.01 33);
            line-height: 1.5;
        }
        .auth-terms a {
            color: oklch(48% 0.012 33);
            text-decoration: underline;
        }

        /* Optional field hint */
        .auth-label-hint {
            font-weight: 400;
            color: oklch(58% 0.01 33);
            font-size: 0.8125rem;
        }

        /* Mobile brand strip */
        .auth-mobile-brand {
            display: none;
            background: var(--primary);
            padding: var(--space-4) var(--space-5);
            align-items: center;
            justify-content: space-between;
        }
        .auth-mobile-logo {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            text-decoration: none;
        }
        .auth-mobile-logo img {
            width: auto;
            height: 36px;
            object-fit: contain;
            filter: invert(1) brightness(2);
        }
        .auth-mobile-back {
            font-family: 'Nunito', sans-serif;
            font-weight: 700;
            font-size: 0.8125rem;
            color: oklch(96% 0.012 33 / 0.80);
            text-decoration: none;
        }
        .auth-mobile-back:hover { color: var(--text-on-primary); }

        /* Focus ring for brand links */
        .auth-back-link:focus-visible,
        .auth-mobile-back:focus-visible,
        .auth-brand-logo:focus-visible,
        .auth-mobile-logo:focus-visible {
            outline: 2px solid var(--text-on-primary);
            outline-offset: 3px;
            border-radius: 4px;
        }

        @keyframes input-pulse {
            0%   { box-shadow: 0 0 0 0px oklch(62% 0.18 33 / 0.55); border-color: var(--border-strong); }
            40%  { box-shadow: 0 0 0 5px oklch(62% 0.18 33 / 0.30); border-color: var(--primary); }
            100% { box-shadow: 0 0 0 0px oklch(62% 0.18 33 / 0);    border-color: var(--primary); }
        }
        .auth-input--pulse {
            animation: input-pulse 0.6s ease-out forwards;
        }

        @media (prefers-reduced-motion: reduce) {
            .auth-btn, .auth-back-link, .auth-back-link svg,
            .auth-input, .auth-switch a, .auth-forgot-link, .auth-pw-toggle {
                transition: none;
            }
            .auth-btn:hover { transform: none; }
            .auth-input--pulse { animation: none; border-color: var(--primary); }
        }

        @media (max-width: 768px) {
            .auth-shell {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
            }
            .auth-brand { display: none; }
            .auth-mobile-brand { display: flex; }
            .auth-form-panel {
                padding: var(--space-7) var(--space-5);
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>

<!-- Mobile brand strip (hidden on desktop) -->
<div class="auth-mobile-brand">
    <a href="<?= base_url() ?>" class="auth-mobile-logo">
        <img src="<?= base_url('assets/img/logo-black.png') ?>" alt="DLIdeas">
    </a>
    <a href="<?= base_url() ?>" class="auth-mobile-back">← Back to shop</a>
</div>

<div class="auth-shell">

    <!-- Left brand panel -->
    <aside class="auth-brand">
        <div class="auth-brand-inner">
            <a href="<?= base_url() ?>" class="auth-brand-logo">
                <img src="<?= base_url('assets/img/logo-black.png') ?>" alt="DLIdeas">
            </a>
            <p class="auth-brand-headline">Singapore's Family&nbsp;Marketplace</p>
            <p class="auth-brand-sub">Unique toys, gifts, and finds from verified local sellers.</p>
            <ul class="auth-trust-list">
                <li>
                    <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 9l3.5 3.5L14 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Verified local sellers
                </li>
                <li>
                    <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 9l3.5 3.5L14 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Safe & secure checkout
                </li>
                <li>
                    <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 9l3.5 3.5L14 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Trusted by thousands of families
                </li>
            </ul>
        </div>
        <div class="auth-brand-footer">
            <a href="<?= base_url() ?>" class="auth-back-link">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to shop
            </a>
        </div>
    </aside>

    <!-- Right form panel -->
    <main class="auth-form-panel">
        <div class="auth-form-wrap">

            <?php if ($this->session->flashdata('error')): ?>
                <div class="auth-flash auth-flash-error" role="alert">
                    <?= htmlspecialchars($this->session->flashdata('error')) ?>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('message')): ?>
                <div class="auth-flash auth-flash-success" role="alert">
                    <?= htmlspecialchars($this->session->flashdata('message')) ?>
                </div>
            <?php endif; ?>

            <?php $this->load->view($content_view, get_defined_vars()); ?>

        </div>
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    // Test accounts toggle + fill
    var taToggle = document.querySelector('.test-accounts-toggle');
    if (taToggle) {
        taToggle.addEventListener('click', function() {
            var expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!expanded));
            document.getElementById('test-accounts-list').hidden = expanded;
        });
        document.querySelectorAll('.ta-fill-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var emailInput = document.getElementById('email');
                var pwInput = document.getElementById('password');
                if (emailInput) emailInput.value = this.dataset.email;
                if (pwInput) pwInput.value = this.dataset.password;
                emailInput && emailInput.dispatchEvent(new Event('input'));
                pwInput && pwInput.dispatchEvent(new Event('input'));
                [emailInput, pwInput].forEach(function(el) {
                    if (!el) return;
                    el.classList.remove('auth-input--pulse');
                    void el.offsetWidth; // reflow to restart animation
                    el.classList.add('auth-input--pulse');
                    el.addEventListener('animationend', function() {
                        el.classList.remove('auth-input--pulse');
                    }, { once: true });
                });
                if (emailInput) {
                    setTimeout(function() {
                        emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 80);
                }
            });
        });
    }

    // Password show/hide toggle
    document.querySelectorAll('.auth-pw-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.previousElementSibling;
            var isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            this.querySelector('.icon-eye').style.display = isText ? 'block' : 'none';
            this.querySelector('.icon-eye-off').style.display = isText ? 'none' : 'block';
            this.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
        });
    });
</script>
</body>
</html>
