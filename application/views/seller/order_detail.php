<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Order #<?= $order->id ?></h2>
    <a href="<?= base_url('seller/orders') ?>" class="btn btn-outline-secondary btn-sm">&larr; Back</a>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header fw-semibold">Items</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->product_name_snapshot) ?></td>
                        <td><?= $item->quantity ?></td>
                        <td>RM <?= number_format($item->unit_price, 2) ?></td>
                        <td>RM <?= number_format($item->unit_price * $item->quantity, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="card-footer text-end fw-bold">
                Total: RM <?= number_format($order->total, 2) ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Shipping Address</div>
            <div class="card-body">
                <?php if ($address): ?>
                <p class="mb-1"><?= htmlspecialchars($address->full_name ?? '') ?> &mdash; <?= htmlspecialchars($address->phone ?? '') ?></p>
                <p class="mb-1"><?= htmlspecialchars($address->address_line ?? '') ?></p>
                <p class="mb-0"><?= htmlspecialchars(($address->postcode ?? '') . ' ' . ($address->city ?? '') . ', ' . ($address->state ?? '')) ?></p>
                <?php else: ?>
                <p class="text-muted">No address recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header fw-semibold">Order Status &amp; Tracking</div>
            <div class="card-body">
                <p>Status: <strong><?= ucfirst($order->status) ?></strong></p>

                <?php echo form_open('seller/orders/' . $order->id); ?>
                <div class="mb-3">
                    <label class="form-label">Tracking Number</label>
                    <input type="text" name="tracking_number" class="form-control"
                           value="<?= htmlspecialchars($order->tracking_number ?? '') ?>"
                           placeholder="e.g. PO123456789MY">
                    <div class="form-text">Saving a tracking number marks the order as Shipped.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Save</button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
