<div class="dl-page-hero dl-page-hero--contact">
    <div class="dl-page-hero-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </div>
    <h1>Contact Us</h1>
    <p>Got a question or feedback? Ahmad reads every message personally.</p>
</div>

<div class="dl-page-content dl-page-content--wide">

    <div class="dl-contact-grid">

        <!-- Left: Info -->
        <div class="dl-contact-sidebar">

            <div class="dl-contact-card">
                <div class="dl-contact-card-icon dl-contact-card-icon--email">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <div>
                    <p class="dl-contact-card-label">Email</p>
                    <a class="dl-contact-card-value" href="mailto:mandrx@gmail.com">mandrx@gmail.com</a>
                </div>
            </div>

            <div class="dl-contact-card">
                <div class="dl-contact-card-icon dl-contact-card-icon--clock">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <p class="dl-contact-card-label">Response Time</p>
                    <p class="dl-contact-card-value">Within 1–2 business days</p>
                </div>
            </div>

            <div class="dl-contact-card">
                <div class="dl-contact-card-icon dl-contact-card-icon--globe">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div>
                    <p class="dl-contact-card-label">About This Site</p>
                    <p class="dl-contact-card-value">Demo build for <a href="https://dlideas.com/" target="_blank" rel="noopener">DL Ideas Pte. Ltd.</a></p>
                </div>
            </div>

            <div class="dl-contact-quicklinks">
                <p class="dl-contact-quicklinks-title">Quick Links</p>
                <a href="<?= base_url('help-center') ?>" class="dl-contact-quicklink">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Help Center
                </a>
                <a href="<?= base_url('returns') ?>" class="dl-contact-quicklink">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
                    Returns &amp; Refunds
                </a>
                <a href="<?= base_url('orders') ?>" class="dl-contact-quicklink">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    Track My Order
                </a>
                <a href="<?= base_url('our-story') ?>" class="dl-contact-quicklink">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    Our Story
                </a>
            </div>

        </div>

        <!-- Right: Form -->
        <div class="dl-contact-form-wrap">
            <div class="dl-contact-form-header">
                <h2>Send a Message</h2>
                <p>Fill in the form below and we'll get back to you within 1–2 business days.</p>
            </div>

            <form class="dl-contact-form" id="contactForm" method="POST" action="<?= site_url('contact/submit') ?>">
                <?= $this->security->get_csrf_field() ?>
                <div class="dl-form-row">
                    <div class="dl-form-group">
                        <label for="contact_name">Your Name</label>
                        <input type="text" id="contact_name" name="name" placeholder="Full name" required autocomplete="name">
                    </div>
                    <div class="dl-form-group">
                        <label for="contact_email">Your Email</label>
                        <input type="email" id="contact_email" name="email" placeholder="you@example.com" required autocomplete="email">
                    </div>
                </div>
                <div class="dl-form-group">
                    <label for="contact_subject">Subject</label>
                    <select id="contact_subject" name="subject">
                        <option value="General Enquiry">General Enquiry</option>
                        <option value="Order Issue">Order Issue</option>
                        <option value="Return Request">Return Request</option>
                        <option value="Seller Enquiry">Seller Enquiry</option>
                        <option value="Portfolio / Hire Ahmad">Portfolio / Hire Ahmad</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="dl-form-group">
                    <label for="contact_message">Message</label>
                    <textarea id="contact_message" name="message" rows="6" placeholder="Describe your question or feedback…" required></textarea>
                </div>
                <button type="submit" class="dl-btn dl-btn-primary dl-btn-full">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Send Message
                </button>
            </form>
        </div>

    </div>
</div>
