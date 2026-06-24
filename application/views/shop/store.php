<!-- Store header -->
<div class="dl-store-header">
    <?php if ($store->logo): ?>
    <img src="<?= base_url($store->logo) ?>" class="dl-store-avatar" width="80" height="80" alt="<?= htmlspecialchars($store->name) ?>">
    <?php else: ?>
    <div class="dl-store-avatar dl-store-avatar-placeholder" aria-hidden="true">
        <?= strtoupper(substr($store->name, 0, 1)) ?>
    </div>
    <?php endif; ?>
    <div class="dl-store-info">
        <h2><?= htmlspecialchars($store->name) ?></h2>
        <?php if ($store->description): ?>
        <p><?= htmlspecialchars($store->description) ?></p>
        <?php endif; ?>
        <span class="dl-store-count"><?= $total ?> active product<?= $total !== 1 ? 's' : '' ?></span>
    </div>
</div>

<?php if (empty($products)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">📦</div>
    <h3>No products yet</h3>
    <p>This store hasn't listed anything yet. Check back soon!</p>
</div>
<?php else: ?>
<div class="dl-product-grid">
    <?php foreach ($products as $product): ?>
        <?php $this->load->view('partials/product_card', ['product' => $product]); ?>
    <?php endforeach; ?>
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
