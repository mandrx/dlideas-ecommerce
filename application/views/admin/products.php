<h2 class="mb-4">All Products <small class="text-muted fs-6">(<?= $total ?>)</small></h2>

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr><th>#</th><th>Product</th><th>Store</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th></tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
    <?php
    $badge = match($p->status) {
        'active'   => 'success',
        'draft'    => 'warning text-dark',
        'inactive' => 'secondary',
        default    => 'secondary',
    };
    ?>
    <tr>
        <td><?= $p->id ?></td>
        <td>
            <a href="<?= base_url('product/' . $p->slug) ?>" target="_blank">
                <?= htmlspecialchars($p->name) ?>
            </a>
        </td>
        <td><?= htmlspecialchars($p->store_name) ?></td>
        <td><?= htmlspecialchars($p->category_name ?? '—') ?></td>
        <td>RM <?= number_format($p->price, 2) ?></td>
        <td><?= $p->stock ?></td>
        <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($p->status) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
