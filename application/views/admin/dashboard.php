<h2 class="mb-4">Admin Dashboard</h2>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="fs-1 fw-bold text-primary"><?= $total_users ?></div>
                <div class="text-muted">Total Users</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="fs-1 fw-bold text-success"><?= $total_stores ?></div>
                <div class="text-muted">Total Stores</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="fs-1 fw-bold text-info"><?= $total_products ?></div>
                <div class="text-muted">Total Products</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="fs-1 fw-bold text-warning"><?= $total_orders ?></div>
                <div class="text-muted">Total Orders</div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($pending_stores)): ?>
<div class="card mb-4">
    <div class="card-header fw-semibold d-flex justify-content-between">
        Pending Store Approvals
        <span class="badge bg-warning text-dark"><?= count($pending_stores) ?></span>
    </div>
    <div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Store</th><th>Slug</th><th>Created</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($pending_stores as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s->name) ?></td>
            <td><code><?= htmlspecialchars($s->slug) ?></code></td>
            <td><?= date('d M Y', strtotime($s->created_at)) ?></td>
            <td>
                <a href="<?= base_url('admin/stores/' . $s->id . '/approve') ?>" class="btn btn-sm btn-success">Approve</a>
                <a href="<?= base_url('admin/stores/' . $s->id . '/suspend') ?>" class="btn btn-sm btn-outline-secondary ms-1">Suspend</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success">No stores pending approval.</div>
<?php endif; ?>

<?php if ($pending_reviews > 0): ?>
<div class="alert alert-warning">
    <?= $pending_reviews ?> review<?= $pending_reviews !== 1 ? 's' : '' ?> pending moderation.
    <a href="<?= base_url('admin/reviews') ?>" class="alert-link">Review now →</a>
</div>
<?php endif; ?>
