<div class="dl-page-top">
    <h2 style="font-family:'Baloo 2','Nunito',sans-serif;font-size:1.8rem;font-weight:900;margin:0;">My Orders</h2>
</div>

<?php if (empty($orders)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">📋</div>
    <h3>No orders yet</h3>
    <p>When you place an order, it'll show up here.</p>
    <a href="<?= base_url() ?>" class="btn btn-primary">Start Shopping</a>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Store</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <?php
    $status_class = match($o->status) {
        'pending'    => 'pending',
        'paid'       => 'paid',
        'processing' => 'processing',
        'shipped'    => 'shipped',
        'delivered'  => 'delivered',
        'cancelled'  => 'cancelled',
        'refunded'   => 'refunded',
        default      => 'cancelled',
    };
    ?>
    <tr>
        <td><span class="dl-order-id">#<?= $o->id ?></span></td>
        <td><?= htmlspecialchars($o->store_name) ?></td>
        <td><span class="dl-order-total">S$ <?= number_format($o->total, 2) ?></span></td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($o->status) ?></span></td>
        <td style="color:var(--text-muted);font-size:0.88rem;"><?= date('d M Y', strtotime($o->created_at)) ?></td>
        <td>
            <a href="<?= base_url('orders/' . $o->id) ?>" class="dl-view-btn" style="white-space:nowrap;">View</a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
