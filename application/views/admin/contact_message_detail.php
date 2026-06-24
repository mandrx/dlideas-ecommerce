<style>
.cm-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-card);
}
.cm-label {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin: 0 0 .6rem;
}
.cm-subject-tag {
    display: inline-block;
    padding: .25rem .75rem;
    border-radius: var(--radius-pill);
    font-size: .78rem;
    font-weight: 700;
    background: var(--primary-light);
    color: var(--primary);
}
</style>

<div class="dl-page-header">
    <div>
        <a href="<?= base_url('admin/contact-messages') ?>" style="display:inline-flex;align-items:center;gap:.3rem;font-size:.82rem;font-weight:700;color:var(--text-muted);text-decoration:none;margin-bottom:.6rem;transition:color var(--t-fast);" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            All Messages
        </a>
        <h2 style="margin:0;">Message #<?= $message->id ?></h2>
        <p class="dl-page-subtitle"><?= date('l, d M Y \a\t H:i', strtotime($message->created_at)) ?></p>
    </div>
    <a href="mailto:<?= htmlspecialchars($message->email) ?>?subject=Re: <?= urlencode($message->subject) ?>" class="dl-btn dl-btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Reply via Email
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 268px;gap:var(--space-5);align-items:start;">

    <!-- Message body -->
    <div class="cm-card" style="padding:var(--space-6);">
        <div style="margin-bottom:var(--space-5);padding-bottom:var(--space-4);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:var(--space-3);">
            <span class="cm-subject-tag"><?= htmlspecialchars($message->subject) ?></span>
        </div>
        <div style="font-size:.97rem;line-height:1.8;white-space:pre-wrap;word-break:break-word;color:var(--text-dark);"><?= htmlspecialchars(trim($message->message)) ?></div>
    </div>

    <!-- Sidebar -->
    <div style="display:flex;flex-direction:column;gap:var(--space-4);">

        <div class="cm-card" style="padding:var(--space-5);">
            <p class="cm-label">Sender</p>
            <p style="font-weight:700;font-size:1rem;margin:0 0 .25rem;color:var(--text-dark);"><?= htmlspecialchars($message->name) ?></p>
            <a href="mailto:<?= htmlspecialchars($message->email) ?>" style="font-size:.88rem;color:var(--primary);text-decoration:none;word-break:break-all;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"><?= htmlspecialchars($message->email) ?></a>
        </div>

        <div class="cm-card" style="padding:var(--space-5);">
            <p class="cm-label">Details</p>
            <dl style="display:grid;grid-template-columns:auto 1fr;gap:.4rem var(--space-4);font-size:.84rem;margin:0;">
                <dt style="color:var(--text-muted);white-space:nowrap;">Received</dt>
                <dd style="margin:0;color:var(--text-dark);"><?= date('d M Y, H:i', strtotime($message->created_at)) ?></dd>
                <dt style="color:var(--text-muted);">IP</dt>
                <dd style="margin:0;font-family:monospace;font-size:.8rem;color:var(--text-muted);"><?= htmlspecialchars($message->ip_address) ?></dd>
            </dl>
        </div>

    </div>

</div>
