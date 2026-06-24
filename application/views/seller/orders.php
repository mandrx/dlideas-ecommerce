<div class="dl-page-header">
    <div>
        <h2>Orders</h2>
        <p class="dl-page-subtitle"><?= $total ?> total order<?= $total !== 1 ? 's' : '' ?></p>
    </div>
</div>

<?php if (empty($orders)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">📋</div>
    <h3>No orders yet</h3>
    <p>When customers place orders from your store, they'll appear here.</p>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Buyer</th>
            <th>Email</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $order): ?>
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
        <td style="color:var(--text-muted);font-size:0.85rem;"><?= htmlspecialchars($order->buyer_email) ?></td>
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
