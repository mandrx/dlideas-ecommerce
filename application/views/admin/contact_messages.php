<div class="dl-page-header">
    <div>
        <h2>Contact Messages</h2>
        <p class="dl-page-subtitle"><?= count($messages) ?> message<?= count($messages) !== 1 ? 's' : '' ?> received</p>
    </div>
</div>

<?php if (empty($messages)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">✉️</div>
    <h3>No messages yet</h3>
    <p>Contact form submissions will appear here once someone reaches out.</p>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Date</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $m): ?>
    <tr>
        <td style="color:var(--text-muted);font-size:.82rem;"><?= $m->id ?></td>
        <td style="font-weight:700;color:var(--text-dark);white-space:nowrap;"><?= htmlspecialchars($m->name) ?></td>
        <td><a href="mailto:<?= htmlspecialchars($m->email) ?>" style="color:var(--primary);text-decoration:none;font-size:.88rem;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"><?= htmlspecialchars($m->email) ?></a></td>
        <td>
            <span style="display:inline-block;padding:.2rem .65rem;border-radius:var(--radius-pill);font-size:.75rem;font-weight:700;background:var(--primary-light);color:var(--primary);white-space:nowrap;">
                <?= htmlspecialchars($m->subject) ?>
            </span>
        </td>
        <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-muted);font-size:.88rem;"><?= htmlspecialchars(mb_strimwidth($m->message, 0, 80, '…')) ?></td>
        <td style="white-space:nowrap;font-size:.85rem;color:var(--text-muted);"><?= date('d M Y, H:i', strtotime($m->created_at)) ?></td>
        <td>
            <a href="<?= base_url('admin/contact-messages/' . $m->id) ?>" style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .8rem;border-radius:var(--radius-pill);font-size:.8rem;font-weight:700;color:var(--primary);border:2px solid var(--primary);text-decoration:none;background:transparent;transition:background var(--t-fast),transform var(--t-fast);" onmouseover="this.style.background='var(--primary-light)';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='transparent';this.style.transform='none'">
                View
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
