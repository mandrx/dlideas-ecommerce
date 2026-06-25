<?php
$thumb      = !empty($product->primary_image) ? base_url($product->primary_image) : base_url('assets/img/placeholder.png');
$on_sale    = !empty($product->sale_price);
$display    = $on_sale ? $product->sale_price : $product->price;
$low_stock  = ($product->stock > 0 && $product->stock <= 3);
$out        = ($product->stock <= 0);
?>
<div class="dl-product-card<?= $out ? ' dl-product-card--out' : '' ?>">
    <a href="<?= base_url('product/' . $product->slug) ?>" class="dl-product-img-wrap" tabindex="-1">
        <?php if ($on_sale): ?>
            <span class="dl-badge-sale">Sale</span>
        <?php endif; ?>
        <?php if ($low_stock): ?>
            <span class="dl-badge-low">Only <?= $product->stock ?> left</span>
        <?php endif; ?>
        <img src="<?= $thumb ?>" class="dl-product-img" alt="<?= htmlspecialchars($product->name) ?>"
             loading="lazy" width="400" height="400"
             onerror="this.onerror=null;this.src='<?= base_url('assets/img/logo.png') ?>';this.style.opacity='0.15';this.style.objectFit='contain';this.style.padding='2rem'">
        <?php if ($out): ?>
        <div class="dl-product-sold-out" aria-label="Out of stock">
            <span>Out of Stock</span>
        </div>
        <?php endif; ?>
    </a>
    <div class="dl-product-info">
        <?php if (!empty($show_subcategory) && !empty($product->category_name)): ?>
        <div class="dl-subcategory-tag">
            <a href="<?= base_url('shop/' . $product->category_slug) ?>"><?= htmlspecialchars($product->category_name) ?></a>
        </div>
        <?php endif; ?>
        <div class="dl-vendor-name">
            <a href="<?= base_url('store/' . $product->store_slug) ?>"><?= htmlspecialchars($product->store_name) ?></a>
        </div>
        <h3 class="dl-product-title">
            <a href="<?= base_url('product/' . $product->slug) ?>"><?= htmlspecialchars($product->name) ?></a>
        </h3>
        <div class="dl-product-bottom">
            <div>
                <span class="dl-price">S$ <?= number_format($display, 2) ?></span>
                <?php if ($on_sale): ?>
                    <span class="dl-price-original">S$ <?= number_format($product->price, 2) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!$out): ?>
            <a href="<?= base_url('product/' . $product->slug) ?>" class="dl-view-btn">View</a>
            <?php endif; ?>
        </div>
    </div>
</div>
