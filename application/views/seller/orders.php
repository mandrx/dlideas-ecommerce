<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">My Orders</h2>
    <span class="text-muted"><?= $total ?> total</span>
</div>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">No orders yet.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
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
            <tr>
                <td><?= $order->id ?></td>
                <td><?= htmlspecialchars($order->buyer_name) ?></td>
                <td class="text-muted small"><?= htmlspecialchars($order->buyer_email) ?></td>
                <td>RM <?= number_format($order->total, 2) ?></td>
                <td>
                    <?php
                    $badge = [
                        'pending'    => 'secondary',
                        'paid'       => 'info',
                        'processing' => 'primary',
                        'shipped'    => 'warning',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                    ][$order->status] ?? 'light';
                    ?>
                    <span class="badge bg-<?= $badge ?>"><?= ucfirst($order->status) ?></span>
                </td>
                <td class="text-muted small"><?= date('d M Y', strtotime($order->created_at)) ?></td>
                <td><a href="<?= base_url('seller/orders/' . $order->id) ?>" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
