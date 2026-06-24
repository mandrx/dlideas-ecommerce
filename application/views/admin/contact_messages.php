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
    <p>Contact form submissions will appear here.</p>
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
            <th>IP</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $m): ?>
    <tr>
        <td><?= $m->id ?></td>
        <td><?= htmlspecialchars($m->name) ?></td>
        <td><a href="mailto:<?= htmlspecialchars($m->email) ?>"><?= htmlspecialchars($m->email) ?></a></td>
        <td><span class="dl-badge dl-badge--info"><?= htmlspecialchars($m->subject) ?></span></td>
        <td style="max-width:360px;white-space:pre-wrap;word-break:break-word;"><?= htmlspecialchars($m->message) ?></td>
        <td style="font-size:.8rem;color:var(--dl-muted);"><?= htmlspecialchars($m->ip_address) ?></td>
        <td style="white-space:nowrap;"><?= date('d M Y H:i', strtotime($m->created_at)) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
