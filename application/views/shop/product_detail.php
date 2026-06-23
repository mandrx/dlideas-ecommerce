<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
        <?php if ($product->category_name): ?>
        <li class="breadcrumb-item"><a href="<?= base_url('shop/' . slugify($product->category_name)) ?>"><?= htmlspecialchars($product->category_name) ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?= htmlspecialchars($product->name) ?></li>
    </ol>
</nav>

<div class="row g-5 mb-5">
    <!-- Gallery -->
    <div class="col-md-6">
        <?php if (!empty($images)): ?>
        <div id="product-gallery" data-images="<?= htmlspecialchars(json_encode(array_map(function($img){
            return ['path' => base_url($img->image_path), 'primary' => (bool)$img->is_primary];
        }, $images)), ENT_QUOTES) ?>">
            <!-- Vue ProductGallery mounts here -->
            <img src="<?= base_url($images[0]->image_path) ?>" class="img-fluid rounded w-100" id="main-image"
                 style="max-height:420px;object-fit:contain;" alt="<?= htmlspecialchars($product->name) ?>">
            <?php if (count($images) > 1): ?>
            <div class="d-flex gap-2 mt-3 flex-wrap">
                <?php foreach ($images as $img): ?>
                <img src="<?= base_url($img->image_path) ?>"
                     onclick="document.getElementById('main-image').src=this.src"
                     class="rounded border" style="width:70px;height:70px;object-fit:cover;cursor:pointer;"
                     alt="">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:420px;">
            <span class="text-muted">No image</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="col-md-6">
        <h1 class="h3"><?= htmlspecialchars($product->name) ?></h1>
        <p class="text-muted mb-1">
            Sold by <a href="<?= base_url('store/' . $product->store_slug) ?>"><?= htmlspecialchars($product->store_name) ?></a>
        </p>

        <div class="my-3">
            <?php if ($product->sale_price): ?>
                <span class="fs-3 fw-bold text-danger">RM <?= number_format($product->sale_price, 2) ?></span>
                <span class="text-muted text-decoration-line-through fs-5 ms-2">RM <?= number_format($product->price, 2) ?></span>
                <span class="badge bg-danger ms-2">
                    <?= round((1 - $product->sale_price / $product->price) * 100) ?>% OFF
                </span>
            <?php else: ?>
                <span class="fs-3 fw-bold">RM <?= number_format($product->price, 2) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($product->stock <= 0): ?>
            <div class="alert alert-secondary py-2">Out of stock</div>
        <?php elseif ($product->stock <= 3): ?>
            <div class="alert alert-warning py-2">Only <?= $product->stock ?> left in stock!</div>
        <?php else: ?>
            <p class="text-success mb-3">✓ In stock (<?= $product->stock ?> available)</p>
        <?php endif; ?>

        <?php if ($product->weight): ?>
        <p class="text-muted small mb-3">Weight: <?= $product->weight ?>g</p>
        <?php endif; ?>

        <?php if ($product->stock > 0): ?>
        <form action="<?= base_url('cart/add') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= $product->id ?>">
            <div class="d-flex align-items-center gap-3 mb-3">
                <input type="number" name="quantity" value="1" min="1" max="<?= $product->stock ?>"
                       class="form-control" style="width:80px;">
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">Add to Cart</button>
            </div>
        </form>
        <?php endif; ?>

        <?php if (!empty($tags)): ?>
        <div class="mt-3">
            <?php foreach ($tags as $t): ?>
            <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($t['tag']) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Description -->
<?php if ($product->description): ?>
<div class="mb-5">
    <h4>Description</h4>
    <p class="text-muted"><?= nl2br(htmlspecialchars($product->description)) ?></p>
</div>
<?php endif; ?>

<!-- Related -->
<?php if (!empty($related)): ?>
<section>
    <h4 class="mb-3">You might also like</h4>
    <div class="row row-cols-2 row-cols-md-4 g-3">
        <?php foreach ($related as $product): ?>
        <div class="col">
            <?php $this->load->view('partials/product_card', array('product' => $product)); ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Reviews -->
<section class="mt-5">
    <div id="review-list"
         data-product-id="<?= $product->id ?>"
         data-csrf-name="<?= $this->security->get_csrf_token_name() ?>"
         data-csrf-hash="<?= $this->security->get_csrf_hash() ?>">
        <!-- Vue ReviewList mounts here -->
        <p class="text-muted">Loading reviews…</p>
    </div>
</section>
