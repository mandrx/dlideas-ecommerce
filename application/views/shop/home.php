<!-- Hero -->
<section class="dl-hero">
    <img src="<?= base_url('assets/img/hero-bg.png') ?>" alt="" class="dl-hero-bg" aria-hidden="true">
    <div class="dl-hero-content">
        <h1>Discover <span>Fun</span> &amp; Learning!</h1>
        <p>Singapore's trusted marketplace for kids, teens, and everyone in between.<br>Shop unique items from verified local sellers.</p>
        <div class="dl-hero-search">
            <input type="text" placeholder="Search products, brands, categories…" id="hero-search-input"
                   aria-label="Search products"
                   onkeydown="if(event.key==='Enter')window.location='<?= base_url('shop') ?>?q='+encodeURIComponent(this.value)">
            <button onclick="window.location='<?= base_url('shop') ?>?q='+encodeURIComponent(document.getElementById('hero-search-input').value)">
                Search
            </button>
        </div>
    </div>
</section>

<!-- Categories -->
<?php if (!empty($categories)): ?>
<h2 class="dl-section-title">Top Categories</h2>
<div class="dl-categories">
    <?php
    $cat_icons = ['🧩','🤖','🎨','🏹','📚','🎮','🏃','⚽','🎵','🔬'];
    $i = 0;
    foreach ($categories as $cat):
        $icon = $cat_icons[$i % count($cat_icons)];
        $cat_img = !empty($cat->image) ? base_url($cat->image) : null;
        $i++;
    ?>
    <a href="<?= base_url('shop/' . $cat->slug) ?>" class="dl-cat-card">
        <div class="dl-cat-icon">
            <?php if ($cat_img): ?>
                <img src="<?= $cat_img ?>" alt="<?= htmlspecialchars($cat->name) ?>"
                     style="width:52px;height:52px;object-fit:contain;" loading="lazy">
            <?php else: ?>
                <?= $icon ?>
            <?php endif; ?>
        </div>
        <div class="dl-cat-name"><?= htmlspecialchars($cat->name) ?></div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Random Products -->
<h2 class="dl-section-title">
    Top Picks For You
    <a href="<?= base_url('shop') ?>">See all &rarr;</a>
</h2>

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
<?php endif; ?>
