<div class="dl-category-header">
    <div>
        <h2>All Products</h2>
        <p class="dl-category-count"><?= $total ?> product<?= $total !== 1 ? 's' : '' ?> found</p>
    </div>
</div>

<?php if (empty($products)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">🛍️</div>
    <h3>No products yet</h3>
    <p>Be the first to list something amazing!</p>
    <a href="<?= base_url('register') ?>" class="btn btn-primary">Start Selling</a>
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
