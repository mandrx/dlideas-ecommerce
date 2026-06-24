<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Manage Coupons</h2>
    <a href="<?= base_url('admin/coupons/add') ?>" class="btn btn-primary btn-sm">+ New Coupon</a>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used / Max</th><th>Expires</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($coupons as $c): ?>
    <tr>
        <td><code><?= htmlspecialchars($c->code) ?></code></td>
        <td><?= ucfirst($c->type) ?></td>
        <td>
            <?php if ($c->type === 'percent'): ?>
            <?= $c->value ?>%
            <?php else: ?>
            RM <?= number_format($c->value, 2) ?>
            <?php endif; ?>
        </td>
        <td>RM <?= number_format($c->min_order, 2) ?></td>
        <td><?= $c->used_count ?> / <?= $c->max_uses ?></td>
        <td><?= date('d M Y', strtotime($c->expires_at)) ?></td>
        <td>
            <span class="badge bg-<?= $c->status === 'active' ? 'success' : 'secondary' ?>">
                <?= ucfirst($c->status) ?>
            </span>
        </td>
        <td>
            <a href="<?= base_url('admin/coupons/' . $c->id . '/toggle') ?>"
               class="btn btn-sm btn-outline-secondary"
               onclick="return confirm('Toggle coupon status?')">
                <?= $c->status === 'active' ? 'Deactivate' : 'Activate' ?>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php if (empty($coupons)): ?>
<p class="text-muted text-center py-4">No coupons yet. <a href="<?= base_url('admin/coupons/add') ?>">Create one</a>.</p>
<?php endif; ?>
