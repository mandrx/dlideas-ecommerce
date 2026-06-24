<div class="dl-page-header">
    <div>
        <a href="<?= base_url('admin/contact-messages') ?>" class="dl-btn dl-btn-sm dl-btn-outline" style="margin-bottom:.75rem;display:inline-flex;align-items:center;gap:.35rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            All Messages
        </a>
        <h2>Message #<?= $message->id ?></h2>
        <p class="dl-page-subtitle"><?= date('l, d M Y \a\t H:i', strtotime($message->created_at)) ?></p>
    </div>
    <a href="mailto:<?= htmlspecialchars($message->email) ?>?subject=Re: <?= urlencode($message->subject) ?>" class="dl-btn dl-btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Reply via Email
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start;">

    <!-- Message body -->
    <div class="dl-card" style="padding:2rem;">
        <div style="margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--dl-border);">
            <span class="dl-badge dl-badge--info" style="font-size:.8rem;"><?= htmlspecialchars($message->subject) ?></span>
        </div>
        <div style="font-size:1rem;line-height:1.75;white-space:pre-wrap;word-break:break-word;color:var(--dl-text);">
            <?= htmlspecialchars($message->message) ?>
        </div>
    </div>

    <!-- Sidebar: sender details -->
    <div style="display:flex;flex-direction:column;gap:1rem;">
        <div class="dl-card" style="padding:1.25rem;">
            <p style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--dl-muted);margin-bottom:.75rem;">Sender</p>
            <p style="font-weight:700;font-size:1rem;margin-bottom:.2rem;"><?= htmlspecialchars($message->name) ?></p>
            <a href="mailto:<?= htmlspecialchars($message->email) ?>" style="font-size:.9rem;color:var(--dl-primary);"><?= htmlspecialchars($message->email) ?></a>
        </div>
        <div class="dl-card" style="padding:1.25rem;">
            <p style="font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--dl-muted);margin-bottom:.75rem;">Details</p>
            <dl style="display:grid;grid-template-columns:auto 1fr;gap:.4rem .75rem;font-size:.85rem;margin:0;">
                <dt style="color:var(--dl-muted);">Received</dt>
                <dd style="margin:0;"><?= date('d M Y H:i', strtotime($message->created_at)) ?></dd>
                <dt style="color:var(--dl-muted);">IP</dt>
                <dd style="margin:0;font-family:monospace;"><?= htmlspecialchars($message->ip_address) ?></dd>
            </dl>
        </div>
    </div>

</div>
