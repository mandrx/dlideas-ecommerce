<h2 class="mb-4">All Orders <small class="text-muted fs-6">(<?= $total ?>)</small></h2>

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr><th>#</th><th>Buyer</th><th>Store</th><th>Total</th><th>Status</th><th>Date</th></tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
    <?php
    $badge = match($o->status) {
        'pending'    => 'warning text-dark',
        'paid'       => 'info',
        'processing' => 'primary',
        'shipped'    => 'primary',
        'delivered'  => 'success',
        'cancelled'  => 'secondary',
        'refunded'   => 'danger',
        default      => 'secondary',
    };
    ?>
    <tr>
        <td>#<?= $o->id ?></td>
        <td><?= htmlspecialchars($o->buyer_name) ?></td>
        <td><?= htmlspecialchars($o->store_name) ?></td>
        <td>RM <?= number_format($o->total, 2) ?></td>
        <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($o->status) ?></span></td>
        <td><?= date('d M Y', strtotime($o->created_at)) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
