<div class="dl-page-header">
    <div>
        <h2><?= htmlspecialchars($store->name) ?></h2>
        <div class="dl-page-subtitle">
            Store status:
            <span class="dl-store-status dl-store-status--<?= $store->status ?>">
                <?= ucfirst($store->status) ?>
            </span>
        </div>
    </div>
    <a href="<?= base_url('seller/products/add') ?>" class="btn btn-primary">+ Add Product</a>
</div>

<?php if ($store->status !== 'active'): ?>
<div class="dl-notice dl-notice--warning">
    <span>⚠️</span>
    <span>Your store is <strong><?= $store->status ?></strong>. You cannot add products until it is approved and active.</span>
</div>
<?php endif; ?>

<div class="dl-stat-grid">
    <div class="dl-stat-card">
        <div class="dl-stat-value"><?= $total_products ?></div>
        <div class="dl-stat-label">Total Products</div>
    </div>
    <div class="dl-stat-card">
        <div class="dl-stat-value"><?= $total_orders ?></div>
        <div class="dl-stat-label">Total Orders</div>
    </div>
    <div class="dl-stat-card">
        <div class="dl-stat-value"><?= $pending_orders ?></div>
        <div class="dl-stat-label">Pending Orders</div>
    </div>
    <div class="dl-stat-card">
        <div class="dl-stat-value" style="font-size:1.4rem;">S$ <?= number_format($total_revenue, 2, '.', ',') ?></div>
        <div class="dl-stat-label">Total Revenue</div>
    </div>
</div>

<!-- Recent Orders -->
<div class="dl-page-header" style="margin-bottom:var(--space-4);">
    <h3 style="font-size:1.1rem;margin:0;font-family:'Nunito',sans-serif;font-weight:800;">Recent Orders</h3>
    <a href="<?= base_url('seller/orders') ?>" style="font-size:0.85rem;font-weight:700;color:var(--primary);">View all →</a>
</div>

<?php if (empty($recent_orders)): ?>
<div class="dl-empty-state" style="padding:var(--space-6) 0;">
    <div class="dl-empty-state-icon">📋</div>
    <h3>No orders yet</h3>
    <p>When customers place orders from your store, they'll appear here.</p>
</div>
<?php else: ?>
<div class="table-responsive" style="margin-bottom:var(--space-8);">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Buyer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($recent_orders as $order): ?>
    <?php
    $status_class = match($order->status) {
        'pending'    => 'pending',
        'paid'       => 'paid',
        'processing' => 'processing',
        'shipped'    => 'shipped',
        'delivered'  => 'delivered',
        'cancelled'  => 'cancelled',
        default      => 'cancelled',
    };
    ?>
    <tr>
        <td><span class="dl-order-id">#<?= $order->id ?></span></td>
        <td style="font-weight:700;color:var(--text-dark);"><?= htmlspecialchars($order->buyer_name) ?></td>
        <td><span class="dl-order-total">S$ <?= number_format($order->total, 2) ?></span></td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($order->status) ?></span></td>
        <td style="color:var(--text-muted);font-size:0.85rem;"><?= date('d M Y', strtotime($order->created_at)) ?></td>
        <td>
            <a href="<?= base_url('seller/orders/' . $order->id) ?>" class="dl-action-btn dl-action-btn--edit">View</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<!-- Recent Products -->
<div class="dl-page-header" style="margin-bottom:var(--space-4);">
    <h3 style="font-size:1.1rem;margin:0;font-family:'Nunito',sans-serif;font-weight:800;">Recent Products</h3>
    <a href="<?= base_url('seller/products') ?>" style="font-size:0.85rem;font-weight:700;color:var(--primary);">View all →</a>
</div>

<?php if (empty($recent_products)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">📦</div>
    <h3>No products yet</h3>
    <p>List your first product and start selling today.</p>
    <a href="<?= base_url('seller/products/add') ?>" class="btn btn-primary">Add Your First Product</a>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($recent_products as $p): ?>
    <?php
    $s = $p->status;
    $status_class = $s === 'active' ? 'delivered' : ($s === 'draft' ? 'cancelled' : 'pending');
    ?>
    <tr>
        <td style="font-weight:700;color:var(--text-dark);"><?= htmlspecialchars($p->name) ?></td>
        <td style="font-family:'Baloo 2','Nunito',sans-serif;font-weight:700;">S$ <?= number_format($p->price, 2, '.', ',') ?></td>
        <td><?= $p->stock ?></td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= $s ?></span></td>
        <td>
            <a href="<?= base_url('seller/products/edit/' . $p->id) ?>" class="dl-action-btn dl-action-btn--edit">Edit</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
