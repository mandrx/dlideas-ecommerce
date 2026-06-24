<div class="dl-page-header">
    <h2>Admin Dashboard</h2>
</div>

<!-- Stats -->
<div class="dl-stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
    <div class="dl-stat-card dl-stat-card--users">
        <div class="dl-stat-value"><?= $total_users ?></div>
        <div class="dl-stat-label">Total Users</div>
    </div>
    <div class="dl-stat-card dl-stat-card--stores">
        <div class="dl-stat-value"><?= $total_stores ?></div>
        <div class="dl-stat-label">Total Stores</div>
    </div>
    <div class="dl-stat-card dl-stat-card--products">
        <div class="dl-stat-value"><?= $total_products ?></div>
        <div class="dl-stat-label">Total Products</div>
    </div>
    <div class="dl-stat-card dl-stat-card--orders">
        <div class="dl-stat-value"><?= $total_orders ?></div>
        <div class="dl-stat-label">Total Orders</div>
    </div>
</div>

<!-- Pending reviews notice -->
<?php if ($pending_reviews > 0): ?>
<div class="dl-notice dl-notice--warning" style="margin-bottom:var(--space-5);">
    <span>⚠️</span>
    <span>
        <strong><?= $pending_reviews ?> review<?= $pending_reviews !== 1 ? 's' : '' ?></strong> pending moderation.
        <a href="<?= base_url('admin/reviews') ?>" style="color:inherit;font-weight:800;text-decoration:underline;text-underline-offset:2px;">Review now →</a>
    </span>
</div>
<?php endif; ?>

<!-- Pending store approvals -->
<?php if (!empty($pending_stores)): ?>
<div class="dl-form-card">
    <div class="dl-form-card-header">
        Pending Store Approvals
        <span class="dl-pending-badge"><?= count($pending_stores) ?></span>
    </div>
    <div class="table-responsive">
    <table class="dl-orders-table" style="border:none;box-shadow:none;">
        <thead>
            <tr>
                <th>Store Name</th>
                <th>Slug</th>
                <th>Registered</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pending_stores as $s): ?>
        <tr>
            <td style="font-weight:700;color:var(--text-dark);"><?= htmlspecialchars($s->name) ?></td>
            <td><span class="dl-code"><?= htmlspecialchars($s->slug) ?></span></td>
            <td style="color:var(--text-muted);font-size:0.85rem;"><?= date('d M Y', strtotime($s->created_at)) ?></td>
            <td>
                <div class="dl-table-actions">
                    <a href="<?= base_url('admin/stores/' . $s->id . '/approve') ?>"
                       class="dl-action-btn dl-action-btn--approve"
                       onclick="return confirm('Approve this store?')">Approve</a>
                    <a href="<?= base_url('admin/stores/' . $s->id . '/suspend') ?>"
                       class="dl-action-btn dl-action-btn--danger"
                       onclick="return confirm('Suspend this store?')">Suspend</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php else: ?>
<div class="dl-notice dl-notice--info">
    <span>✓</span>
    <span>No stores pending approval — all caught up.</span>
</div>
<?php endif; ?>
