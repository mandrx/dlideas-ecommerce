<div class="dl-page-header">
    <div>
        <h2>All Orders</h2>
        <p class="dl-page-subtitle"><?= $total ?> total order<?= $total !== 1 ? 's' : '' ?></p>
    </div>
</div>

<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Buyer</th>
            <th>Store</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
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
        <td style="font-weight:700;color:var(--text-dark);"><?= htmlspecialchars($o->buyer_name) ?></td>
        <td style="color:var(--text-muted);font-size:0.88rem;"><?= htmlspecialchars($o->store_name) ?></td>
        <td><span class="dl-order-total">S$ <?= number_format($o->total, 2) ?></span></td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($o->status) ?></span></td>
        <td style="color:var(--text-muted);font-size:0.85rem;"><?= date('d M Y', strtotime($o->created_at)) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
