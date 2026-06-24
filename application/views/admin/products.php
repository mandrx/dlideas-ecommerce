<div class="dl-page-header">
    <div>
        <h2>All Products</h2>
        <p class="dl-page-subtitle"><?= $total ?> total product<?= $total !== 1 ? 's' : '' ?></p>
    </div>
</div>

<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Store</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
    <?php
    $status_class = match($p->status) {
        'active'   => 'delivered',
        'draft'    => 'pending',
        'inactive' => 'cancelled',
        default    => 'cancelled',
    };
    ?>
    <tr>
        <td style="color:var(--text-muted);font-size:0.82rem;"><?= $p->id ?></td>
        <td style="max-width:220px;">
            <a href="<?= base_url('product/' . $p->slug) ?>" target="_blank"
               style="font-weight:700;color:var(--text-dark);transition:color var(--t-fast);"
               onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dark)'">
                <?= htmlspecialchars($p->name) ?>
                <span style="font-size:0.7rem;color:var(--text-muted);margin-left:4px;">↗</span>
            </a>
        </td>
        <td style="color:var(--text-muted);font-size:0.88rem;"><?= htmlspecialchars($p->store_name) ?></td>
        <td style="color:var(--text-muted);font-size:0.88rem;"><?= htmlspecialchars($p->category_name ?? '—') ?></td>
        <td style="font-family:'Baloo 2','Nunito',sans-serif;font-weight:700;">S$ <?= number_format($p->price, 2) ?></td>
        <td style="font-weight:700;<?= $p->stock <= 3 && $p->stock > 0 ? 'color:oklch(42% 0.15 65);' : ($p->stock <= 0 ? 'color:var(--danger);' : '') ?>">
            <?= $p->stock ?>
        </td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($p->status) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
