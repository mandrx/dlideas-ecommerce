<div class="dl-page-header">
    <h2>Manage Stores</h2>
</div>

<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Store Name</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($stores as $s): ?>
    <?php
    $status_class = match($s->status) {
        'active'    => 'delivered',
        'pending'   => 'pending',
        'suspended' => 'cancelled',
        default     => 'cancelled',
    };
    ?>
    <tr>
        <td style="color:var(--text-muted);font-size:0.82rem;"><?= $s->id ?></td>
        <td style="font-weight:700;color:var(--text-dark);"><?= htmlspecialchars($s->name) ?></td>
        <td><span class="dl-code"><?= htmlspecialchars($s->slug) ?></span></td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($s->status) ?></span></td>
        <td style="color:var(--text-muted);font-size:0.85rem;"><?= date('d M Y', strtotime($s->created_at)) ?></td>
        <td>
            <div class="dl-table-actions">
                <?php if ($s->status !== 'active'): ?>
                <a href="<?= base_url('admin/stores/' . $s->id . '/approve') ?>"
                   class="dl-action-btn dl-action-btn--approve"
                   onclick="return confirm('Approve this store?')">Approve</a>
                <?php endif; ?>
                <?php if ($s->status !== 'suspended'): ?>
                <a href="<?= base_url('admin/stores/' . $s->id . '/suspend') ?>"
                   class="dl-action-btn dl-action-btn--danger"
                   onclick="return confirm('Suspend this store?')">Suspend</a>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= $pagination ?>
