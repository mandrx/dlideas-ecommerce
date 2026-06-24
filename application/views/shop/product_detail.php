<nav aria-label="breadcrumb" style="margin-bottom:var(--space-5);">
    <ol class="breadcrumb" style="font-size:0.85rem;font-weight:600;color:var(--text-muted);">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>" style="color:var(--text-muted);">Home</a></li>
        <?php if ($product->category_name): ?>
        <li class="breadcrumb-item">
            <a href="<?= base_url('shop/' . slugify($product->category_name)) ?>"
               style="color:var(--text-muted);"><?= htmlspecialchars($product->category_name) ?></a>
        </li>
        <?php endif; ?>
        <li class="breadcrumb-item active" style="color:var(--text-dark);"><?= htmlspecialchars($product->name) ?></li>
    </ol>
</nav>

<div class="row g-5 mb-5">
    <!-- Gallery -->
    <div class="col-md-6">
        <?php if (!empty($images)): ?>
        <div id="product-gallery" data-images="<?= htmlspecialchars(json_encode(array_map(function($img){
            return ['path' => base_url($img->image_path), 'primary' => (bool)$img->is_primary];
        }, $images)), ENT_QUOTES) ?>">
            <div style="background:var(--bg-subtle);border-radius:var(--radius-md);border:1.5px solid var(--border);overflow:hidden;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;">
                <img src="<?= base_url($images[0]->image_path) ?>" id="main-image"
                     style="width:100%;height:100%;object-fit:contain;"
                     alt="<?= htmlspecialchars($product->name) ?>">
            </div>
            <?php if (count($images) > 1): ?>
            <div style="display:flex;gap:var(--space-2);margin-top:var(--space-3);flex-wrap:wrap;">
                <?php foreach ($images as $img): ?>
                <button onclick="document.getElementById('main-image').src='<?= base_url($img->image_path) ?>'"
                        style="width:64px;height:64px;border:2px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;cursor:pointer;padding:0;background:var(--bg-subtle);transition:border-color var(--t-fast) var(--ease-out);"
                        onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'"
                        type="button">
                    <img src="<?= base_url($img->image_path) ?>" style="width:100%;height:100%;object-fit:cover;" alt="">
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="background:var(--bg-subtle);border-radius:var(--radius-md);border:1.5px solid var(--border);height:420px;display:flex;align-items:center;justify-content:center;">
            <span style="color:var(--text-muted);font-size:0.9rem;">No image available</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="col-md-6">
        <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);margin-bottom:var(--space-2);">
            Sold by <a href="<?= base_url('store/' . $product->store_slug) ?>"
                       style="color:var(--primary);transition:color var(--t-fast);"><?= htmlspecialchars($product->store_name) ?></a>
        </div>

        <h1 style="font-size:1.7rem;font-weight:900;margin-bottom:var(--space-4);line-height:1.2;">
            <?= htmlspecialchars($product->name) ?>
        </h1>

        <div style="margin-bottom:var(--space-5);">
            <?php if ($product->sale_price): ?>
                <span style="font-family:'Baloo 2','Nunito',sans-serif;font-size:2rem;font-weight:900;color:var(--primary);">
                    S$ <?= number_format($product->sale_price, 2) ?>
                </span>
                <span style="font-size:1.1rem;color:var(--text-muted);text-decoration:line-through;margin-left:var(--space-2);">
                    S$ <?= number_format($product->price, 2) ?>
                </span>
                <span class="dl-badge-sale" style="position:relative;top:0;left:0;margin-left:var(--space-2);display:inline-block;">
                    <?= round((1 - $product->sale_price / $product->price) * 100) ?>% OFF
                </span>
            <?php else: ?>
                <span style="font-family:'Baloo 2','Nunito',sans-serif;font-size:2rem;font-weight:900;color:var(--primary);">
                    S$ <?= number_format($product->price, 2) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($product->stock <= 0): ?>
            <div style="background:var(--bg-subtle);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:var(--space-3) var(--space-4);margin-bottom:var(--space-4);font-weight:700;color:var(--text-muted);">
                Out of stock
            </div>
        <?php elseif ($product->stock <= 3): ?>
            <div style="background:oklch(93% 0.08 65);border-radius:var(--radius-sm);padding:var(--space-3) var(--space-4);margin-bottom:var(--space-4);font-weight:700;color:oklch(42% 0.15 65);">
                Only <?= $product->stock ?> left in stock — order soon!
            </div>
        <?php else: ?>
            <p style="color:var(--success);font-weight:700;margin-bottom:var(--space-4);">
                ✓ In stock (<?= $product->stock ?> available)
            </p>
        <?php endif; ?>

        <?php if ($product->weight): ?>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:var(--space-4);">Weight: <?= $product->weight ?>g</p>
        <?php endif; ?>

        <?php if ($product->stock > 0): ?>
        <form action="<?= base_url('cart/add') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= $product->id ?>">
            <div style="display:flex;align-items:center;gap:var(--space-3);margin-bottom:var(--space-4);">
                <input type="number" name="quantity" value="1" min="1" max="<?= $product->stock ?>"
                       class="form-control" style="width:80px;font-weight:700;text-align:center;"
                       aria-label="Quantity">
                <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">Add to Cart</button>
            </div>
        </form>
        <?php endif; ?>

        <?php if (!empty($tags)): ?>
        <div style="display:flex;flex-wrap:wrap;gap:var(--space-2);margin-top:var(--space-3);">
            <?php foreach ($tags as $t): ?>
            <span style="display:inline-block;background:var(--bg-subtle);border:1.5px solid var(--border);border-radius:var(--radius-pill);padding:3px 12px;font-size:0.78rem;font-weight:700;color:var(--text-muted);">
                <?= htmlspecialchars($t['tag']) ?>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Description -->
<?php if ($product->description): ?>
<div style="margin-bottom:var(--space-7);">
    <h3 style="font-family:'Baloo 2','Nunito',sans-serif;font-size:1.2rem;margin-bottom:var(--space-4);">Description</h3>
    <p style="color:var(--text-dark);line-height:1.75;max-width:72ch;"><?= nl2br(htmlspecialchars($product->description)) ?></p>
</div>
<?php endif; ?>

<!-- Related -->
<?php if (!empty($related)): ?>
<section style="margin-bottom:var(--space-7);">
    <h3 style="font-family:'Baloo 2','Nunito',sans-serif;font-size:1.3rem;margin-bottom:var(--space-5);">You might also like</h3>
    <div class="dl-product-grid">
        <?php foreach ($related as $product): ?>
            <?php $this->load->view('partials/product_card', ['product' => $product]); ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Reviews -->
<section style="margin-top:var(--space-7);">
    <div id="review-list"
         data-product-id="<?= $product->id ?>"
         data-csrf-name="<?= $this->security->get_csrf_token_name() ?>"
         data-csrf-hash="<?= $this->security->get_csrf_hash() ?>">
        <p style="color:var(--text-muted);">Loading reviews…</p>
    </div>
</section>
