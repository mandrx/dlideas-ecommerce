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

<div class="dl-page-header">
    <div style="display:flex;align-items:center;gap:var(--space-4);">
        <h2>Order #<?= $order->id ?></h2>
        <span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($order->status) ?></span>
    </div>
    <a href="<?= base_url('seller/orders') ?>" class="dl-back-link">All Orders</a>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <!-- Items card -->
        <div class="dl-form-card">
            <div class="dl-form-card-header">Order Items</div>
            <table class="dl-orders-table" style="border:none;box-shadow:none;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td style="font-weight:700;color:var(--text-dark);">
                        <?= htmlspecialchars($item->product_name_snapshot) ?>
                    </td>
                    <td style="color:var(--text-muted);">×<?= $item->quantity ?></td>
                    <td style="color:var(--text-muted);">RM <?= number_format($item->unit_price, 2) ?></td>
                    <td class="dl-order-total">RM <?= number_format($item->unit_price * $item->quantity, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="padding:var(--space-4) var(--space-5);border-top:1.5px solid var(--border);text-align:right;background:var(--bg-subtle);">
                <span style="font-weight:800;font-size:0.85rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;">Order Total</span>
                <span style="font-family:'Baloo 2','Nunito',sans-serif;font-weight:900;font-size:1.3rem;color:var(--primary);margin-left:var(--space-4);">
                    RM <?= number_format($order->total, 2) ?>
                </span>
            </div>
        </div>

        <!-- Shipping address -->
        <div class="dl-form-card">
            <div class="dl-form-card-header">Shipping Address</div>
            <div class="dl-form-card-body">
                <?php if ($address): ?>
                <p style="font-weight:800;margin:0 0 var(--space-2);font-size:1rem;"><?= htmlspecialchars($address->full_name ?? '') ?></p>
                <p style="color:var(--text-muted);margin:0 0 var(--space-1);font-size:0.9rem;"><?= htmlspecialchars($address->phone ?? '') ?></p>
                <p style="color:var(--text-muted);margin:0 0 var(--space-1);font-size:0.9rem;"><?= htmlspecialchars($address->address_line ?? '') ?></p>
                <p style="color:var(--text-muted);margin:0;font-size:0.9rem;">
                    <?= htmlspecialchars(trim(($address->postcode ?? '') . ' ' . ($address->city ?? '') . ', ' . ($address->state ?? ''))) ?>
                </p>
                <?php else: ?>
                <p style="color:var(--text-muted);margin:0;">No address recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <!-- Status & tracking -->
        <div class="dl-form-card">
            <div class="dl-form-card-header">Status &amp; Tracking</div>
            <div class="dl-form-card-body">
                <div style="margin-bottom:var(--space-4);padding:var(--space-3) var(--space-4);background:var(--bg-subtle);border-radius:var(--radius-sm);display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.82rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;">Current Status</span>
                    <span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($order->status) ?></span>
                </div>

                <?php echo form_open('seller/orders/' . $order->id); ?>
                <div class="dl-tracking-form">
                    <div>
                        <label class="form-label" style="font-weight:800;font-size:0.88rem;">Tracking Number</label>
                        <input type="text" name="tracking_number" class="form-control"
                               value="<?= htmlspecialchars($order->tracking_number ?? '') ?>"
                               placeholder="e.g. PO123456789MY">
                        <p class="dl-form-hint">Saving a tracking number will mark this order as <strong>Shipped</strong>.</p>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Tracking</button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
