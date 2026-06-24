<?php
$status_class = match($order->status) {
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

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-6);">
    <div style="display:flex;align-items:center;gap:var(--space-4);">
        <h2 style="font-family:'Baloo 2','Nunito',sans-serif;font-size:1.8rem;font-weight:900;margin:0;">
            Order #<?= $order->id ?>
        </h2>
        <span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($order->status) ?></span>
    </div>
    <a href="<?= base_url('orders') ?>" class="dl-back-link">All Orders</a>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-6">
        <div class="dl-summary-card" style="height:100%;">
            <div class="dl-summary-header">Order Summary</div>
            <div class="dl-summary-body">
                <div class="dl-summary-row">
                    <span style="color:var(--text-muted);">Store</span>
                    <span style="font-weight:700;"><?= htmlspecialchars($order->store_name) ?></span>
                </div>
                <div class="dl-summary-row">
                    <span style="color:var(--text-muted);">Placed</span>
                    <span><?= date('d M Y, g:i A', strtotime($order->created_at)) ?></span>
                </div>
                <div class="dl-summary-divider"></div>
                <div class="dl-summary-row">
                    <span>Subtotal</span>
                    <span>RM <?= number_format($order->subtotal, 2) ?></span>
                </div>
                <div class="dl-summary-row" style="color:var(--text-muted);">
                    <span>Shipping</span>
                    <span>RM <?= number_format($order->shipping_cost, 2) ?></span>
                </div>
                <?php if ($order->discount > 0): ?>
                <div class="dl-summary-row" style="color:var(--success);">
                    <span>Discount</span>
                    <span>− RM <?= number_format($order->discount, 2) ?></span>
                </div>
                <?php endif; ?>
                <div class="dl-summary-row total">
                    <span>Total</span>
                    <span class="dl-summary-amount">RM <?= number_format($order->total, 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="dl-summary-card" style="height:100%;">
            <div class="dl-summary-header">Shipping Address</div>
            <div class="dl-summary-body">
                <?php if ($address): ?>
                <p style="font-weight:700;margin:0 0 var(--space-2);font-size:1rem;"><?= htmlspecialchars($address->full_name) ?></p>
                <p style="color:var(--text-muted);margin:0 0 var(--space-1);font-size:0.92rem;"><?= htmlspecialchars($address->phone) ?></p>
                <p style="color:var(--text-muted);margin:0 0 var(--space-1);font-size:0.92rem;"><?= htmlspecialchars($address->address_line) ?></p>
                <p style="color:var(--text-muted);margin:0;font-size:0.92rem;">
                    <?= htmlspecialchars($address->postcode) ?> <?= htmlspecialchars($address->city) ?>, <?= htmlspecialchars($address->state) ?>
                </p>
                <?php else: ?>
                <p style="color:var(--text-muted);margin:0;">No address on file.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<h3 style="font-family:'Baloo 2','Nunito',sans-serif;font-size:1.2rem;margin-bottom:var(--space-4);">Items Ordered</h3>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Product</th>
            <th>Unit Price</th>
            <th>Qty</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
    <tr>
        <td>
            <?php if ($item->product_slug): ?>
            <a href="<?= base_url('product/' . $item->product_slug) ?>"
               style="font-weight:700;color:var(--text-dark);transition:color var(--t-fast) var(--ease-out);"
               onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dark)'">
                <?= htmlspecialchars($item->product_name_snapshot) ?>
            </a>
            <?php else: ?>
            <span><?= htmlspecialchars($item->product_name_snapshot) ?></span>
            <span style="font-size:0.78rem;color:var(--text-muted);margin-left:4px;">(removed)</span>
            <?php endif; ?>
        </td>
        <td style="color:var(--text-muted);">RM <?= number_format($item->unit_price, 2) ?></td>
        <td style="font-weight:700;">×<?= $item->quantity ?></td>
        <td class="dl-order-total">RM <?= number_format($item->unit_price * $item->quantity, 2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
