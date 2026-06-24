<div class="dl-page-header">
    <h2>Manage Coupons</h2>
    <a href="<?= base_url('admin/coupons/add') ?>" class="btn btn-primary">+ New Coupon</a>
</div>

<?php if (empty($coupons)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">🎟️</div>
    <h3>No coupons yet</h3>
    <p>Create discount codes to reward your shoppers.</p>
    <a href="<?= base_url('admin/coupons/add') ?>" class="btn btn-primary">Create Coupon</a>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Code</th>
            <th>Type</th>
            <th>Value</th>
            <th>Min Order</th>
            <th>Usage</th>
            <th>Expires</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($coupons as $c): ?>
    <?php
    $usage_pct   = $c->max_uses > 0 ? min(100, round($c->used_count / $c->max_uses * 100)) : 0;
    $is_active   = $c->status === 'active';
    $is_expired  = strtotime($c->expires_at) < time();
    $status_class = ($is_active && !$is_expired) ? 'delivered' : 'cancelled';
    ?>
    <tr>
        <td><span class="dl-coupon-code"><?= htmlspecialchars($c->code) ?></span></td>
        <td style="color:var(--text-muted);font-size:0.88rem;text-transform:capitalize;"><?= $c->type ?></td>
        <td style="font-weight:800;font-family:'Baloo 2','Nunito',sans-serif;color:var(--primary);">
            <?php if ($c->type === 'percent'): ?>
                <?= $c->value ?>%
            <?php else: ?>
                S$ <?= number_format($c->value, 2) ?>
            <?php endif; ?>
        </td>
        <td style="color:var(--text-muted);font-size:0.88rem;">S$ <?= number_format($c->min_order, 2) ?></td>
        <td>
            <div class="dl-usage-bar">
                <div class="dl-usage-track">
                    <div class="dl-usage-fill" style="width:<?= $usage_pct ?>%;"></div>
                </div>
                <span class="dl-usage-label"><?= $c->used_count ?>/<?= $c->max_uses ?></span>
            </div>
        </td>
        <td style="font-size:0.85rem;<?= $is_expired ? 'color:var(--danger);font-weight:700;' : 'color:var(--text-muted);' ?>">
            <?= $is_expired ? '⚠ ' : '' ?><?= date('d M Y', strtotime($c->expires_at)) ?>
        </td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= $is_expired ? 'Expired' : ucfirst($c->status) ?></span></td>
        <td>
            <a href="<?= base_url('admin/coupons/' . $c->id . '/toggle') ?>"
               class="dl-action-btn <?= $is_active ? 'dl-action-btn--danger' : 'dl-action-btn--approve' ?>"
               onclick="return confirm('<?= $is_active ? 'Deactivate' : 'Activate' ?> this coupon?')">
                <?= $is_active ? 'Deactivate' : 'Activate' ?>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
