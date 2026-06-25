<div class="dl-page-header">
    <h2>
        My Products
        <span class="dl-status-badge dl-status-badge--shipped" style="font-size:0.75rem;vertical-align:middle;margin-left:var(--space-2);"><?= $total ?></span>
    </h2>
    <a href="<?= base_url('seller/products/add') ?>" class="btn btn-primary">+ Add Product</a>
</div>

<?php if (empty($products)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">📦</div>
    <h3>No products yet</h3>
    <p>Start by adding your first product to the marketplace.</p>
    <a href="<?= base_url('seller/products/add') ?>" class="btn btn-primary">Add Product</a>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>#</th>
            <th style="width:52px;"></th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Sale Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
    <?php
    $s = $p->status;
    $status_class = $s === 'active' ? 'delivered' : ($s === 'draft' ? 'cancelled' : 'pending');
    ?>
    <tr>
        <td style="color:var(--text-muted);font-size:0.82rem;"><?= $p->id ?></td>
        <td style="padding:6px 8px;">
            <?php if (!empty($p->primary_image)): ?>
            <img src="<?= base_url($p->primary_image) ?>" alt=""
                 style="width:44px;height:44px;object-fit:cover;border-radius:6px;display:block;">
            <?php else: ?>
            <div style="width:44px;height:44px;border-radius:6px;background:var(--border);display:flex;align-items:center;justify-content:center;font-size:1.2rem;">📦</div>
            <?php endif; ?>
        </td>
        <td style="font-weight:700;color:var(--text-dark);max-width:220px;">
            <a href="<?= base_url('seller/products/edit/' . $p->id) ?>"
               style="color:inherit;transition:color var(--t-fast);"
               onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                <?= htmlspecialchars($p->name) ?>
            </a>
        </td>
        <td style="color:var(--text-muted);font-size:0.88rem;"><?= htmlspecialchars($p->category_name ?? '—') ?></td>
        <td style="font-family:'Baloo 2','Nunito',sans-serif;font-weight:700;">S$ <?= number_format($p->price, 2, '.', ',') ?></td>
        <td style="color:var(--text-muted);"><?= $p->sale_price ? 'S$ ' . number_format($p->sale_price, 2, '.', ',') : '—' ?></td>
        <td style="font-weight:700;<?= $p->stock <= 3 && $p->stock > 0 ? 'color:oklch(42% 0.15 65);' : ($p->stock <= 0 ? 'color:var(--danger);' : '') ?>">
            <?= $p->stock ?>
        </td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= $s ?></span></td>
        <td>
            <div class="dl-table-actions">
                <a href="<?= base_url('seller/products/edit/' . $p->id) ?>" class="dl-action-btn dl-action-btn--edit">Edit</a>
                <a href="<?= base_url('seller/products/delete/' . $p->id) ?>"
                   class="dl-action-btn dl-action-btn--danger"
                   onclick="return confirm('Delete this product? This cannot be undone.')">Delete</a>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php
$total_pages = ceil($total / $per_page);
if ($total_pages > 1):
?>
<nav aria-label="Page navigation">
    <ul class="dl-pagination pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>
