<?php
$is_edit    = ($action === 'edit' && $product);
$form_url   = $is_edit ? base_url('seller/products/edit/' . $product->id) : base_url('seller/products/add');
$name_val   = set_value('name',        $is_edit ? $product->name : '');
$desc_val   = set_value('description', $is_edit ? $product->description : '');
$price_val  = set_value('price',       $is_edit ? $product->price : '');
$sale_val   = set_value('sale_price',  $is_edit ? $product->sale_price : '');
$stock_val  = set_value('stock',       $is_edit ? $product->stock : '0');
$weight_val = set_value('weight',      $is_edit ? $product->weight : '');
$status_val = set_value('status',      $is_edit ? $product->status : 'draft');
$cat_val    = set_value('category_id', $is_edit ? $product->category_id : '');
$tags_val   = set_value('tags',        isset($tag_str) ? $tag_str : '');
?>

<div class="dl-page-header">
    <h2><?= $is_edit ? 'Edit Product' : 'Add Product' ?></h2>
    <a href="<?= base_url('seller/products') ?>" class="dl-back-link">All Products</a>
</div>

<?php echo validation_errors('<div class="dl-notice dl-notice--warning">', '</div>'); ?>

<?php echo form_open($form_url, ['class' => 'needs-validation']); ?>

<div class="row g-4">
    <!-- Left column -->
    <div class="col-lg-8">

        <div class="dl-form-card">
            <div class="dl-form-card-header">Basic Information</div>
            <div class="dl-form-card-body">
                <div class="mb-4">
                    <label class="form-label" style="font-weight:800;">Product Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($name_val) ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label" style="font-weight:800;">Description</label>
                    <textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($desc_val) ?></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label" style="font-weight:800;">Tags <small style="font-weight:600;color:var(--text-muted);">(comma-separated)</small></label>
                    <input type="text" name="tags" class="form-control"
                           value="<?= htmlspecialchars($tags_val) ?>"
                           placeholder="e.g. puzzle, educational, ages 3–6">
                </div>
            </div>
        </div>

        <div class="dl-form-card">
            <div class="dl-form-card-header">Pricing &amp; Stock</div>
            <div class="dl-form-card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Price (RM) <span style="color:var(--danger);">*</span></label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0"
                               value="<?= htmlspecialchars($price_val) ?>" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Sale Price (RM) <small style="font-weight:600;color:var(--text-muted);">optional</small></label>
                        <input type="number" name="sale_price" class="form-control" step="0.01" min="0"
                               value="<?= htmlspecialchars($sale_val) ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Stock <span style="color:var(--danger);">*</span></label>
                        <input type="number" name="stock" class="form-control" min="0"
                               value="<?= htmlspecialchars($stock_val) ?>" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Weight (grams) <small style="font-weight:600;color:var(--text-muted);">optional</small></label>
                        <input type="number" name="weight" class="form-control" step="0.01" min="0"
                               value="<?= htmlspecialchars($weight_val) ?>">
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Right column -->
    <div class="col-lg-4">

        <div class="dl-form-card">
            <div class="dl-form-card-header">Visibility</div>
            <div class="dl-form-card-body">
                <div class="dl-status-radio">
                    <label class="dl-status-option">
                        <input type="radio" name="status" value="draft" <?= $status_val === 'draft' ? 'checked' : '' ?>>
                        <div class="dl-status-option-text">
                            <strong>Draft</strong>
                            <span>Hidden from the shop</span>
                        </div>
                    </label>
                    <label class="dl-status-option">
                        <input type="radio" name="status" value="active" <?= $status_val === 'active' ? 'checked' : '' ?>>
                        <div class="dl-status-option-text">
                            <strong>Active</strong>
                            <span>Visible to all shoppers</span>
                        </div>
                    </label>
                    <label class="dl-status-option">
                        <input type="radio" name="status" value="inactive" <?= $status_val === 'inactive' ? 'checked' : '' ?>>
                        <div class="dl-status-option-text">
                            <strong>Inactive</strong>
                            <span>Temporarily hidden</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="dl-form-card">
            <div class="dl-form-card-header">Category</div>
            <div class="dl-form-card-body">
                <select name="category_id" class="form-select">
                    <?php foreach ($categories as $cat_id => $cat_label): ?>
                    <option value="<?= $cat_id ?>" <?= (string)$cat_val === (string)$cat_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat_label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display:grid;gap:var(--space-3);">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <?= $is_edit ? 'Save Changes' : 'Add Product' ?>
            </button>
            <?php if ($is_edit): ?>
            <a href="<?= base_url('seller/products/delete/' . $product->id) ?>"
               class="dl-action-btn dl-action-btn--danger"
               style="text-align:center;display:block;padding:10px;"
               onclick="return confirm('Permanently delete this product? This cannot be undone.')">
                Delete Product
            </a>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php echo form_close(); ?>

<!-- Image Upload Panel (edit mode only) -->
<?php if ($is_edit): ?>
<div class="dl-form-card" style="margin-top:var(--space-6);" id="image-uploader">
    <div class="dl-form-card-header">
        Product Images
        <small>First image is shown as primary &mdash; max 4 MB per image</small>
    </div>
    <div class="dl-form-card-body">

        <!-- Existing images -->
        <div class="dl-image-grid" id="image-grid">
        <?php foreach ($images as $img): ?>
        <div class="dl-image-thumb" data-id="<?= $img->id ?>">
            <img src="<?= base_url($img->image_path) ?>" alt="">
            <?php if ($img->is_primary): ?>
            <span class="dl-image-thumb-badge">Primary</span>
            <?php else: ?>
            <button type="button" class="dl-image-thumb-set-primary" onclick="setPrimary(<?= $img->id ?>)">Set primary</button>
            <?php endif; ?>
            <button type="button" class="dl-image-thumb-delete" onclick="deleteImage(<?= $img->id ?>, this)">✕</button>
        </div>
        <?php endforeach; ?>
        </div>

        <!-- Drop zone -->
        <input type="file" id="file-input" accept="image/*" multiple style="display:none;">
        <label for="file-input" id="drop-zone" class="dl-drop-zone">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
            </svg>
            <p>Drag &amp; drop images here or <span style="color:var(--primary);font-weight:800;">click to browse</span></p>
            <small>JPEG, PNG, WebP, GIF — max 4 MB each</small>
        </label>

        <!-- Upload queue -->
        <div id="upload-queue" style="margin-top:var(--space-3);"></div>
    </div>
</div>

<script>
(function () {
    const PRODUCT_ID  = <?= (int)$product->id ?>;
    const UPLOAD_URL  = '<?= base_url('api/images/upload') ?>';
    const API_BASE    = '<?= base_url('api/images') ?>/';

    const dropZone  = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const queue     = document.getElementById('upload-queue');
    const grid      = document.getElementById('image-grid');

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('is-over');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('is-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('is-over');
        uploadFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', () => uploadFiles(fileInput.files));

    function uploadFiles(files) {
        [...files].forEach(uploadOne);
        fileInput.value = '';
    }

    function uploadOne(file) {
        const item = document.createElement('div');
        item.className = 'dl-upload-item';
        item.innerHTML = `
            <div class="dl-upload-item-header">
                <span class="dl-upload-item-name">${escHtml(file.name)}</span>
                <span class="dl-upload-status status-text">Uploading…</span>
            </div>
            <div class="dl-upload-progress">
                <div class="dl-upload-progress-bar" style="width:0%"></div>
            </div>`;
        queue.prepend(item);

        const bar    = item.querySelector('.dl-upload-progress-bar');
        const status = item.querySelector('.status-text');

        const fd = new FormData();
        fd.append('file', file);
        fd.append('product_id', PRODUCT_ID);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', UPLOAD_URL, true);

        xhr.upload.onprogress = e => {
            if (e.lengthComputable) bar.style.width = Math.round(e.loaded / e.total * 100) + '%';
        };

        xhr.onload = () => {
            let data = {};
            try { data = JSON.parse(xhr.responseText); } catch(e) {}

            if (xhr.status === 201) {
                bar.style.width = '100%';
                bar.classList.add('done');
                status.textContent = 'Done ✓';
                status.style.color = 'var(--success)';
                setTimeout(() => item.remove(), 1800);
                appendThumb(data);
            } else {
                bar.classList.add('error');
                status.textContent = data.error || 'Upload failed';
                status.style.color = 'var(--danger)';
            }
        };

        xhr.onerror = () => {
            bar.classList.add('error');
            status.textContent = 'Network error';
        };

        xhr.send(fd);
    }

    function appendThumb(data) {
        const div = document.createElement('div');
        div.className  = 'dl-image-thumb';
        div.dataset.id = data.id;
        div.innerHTML = `
            <img src="${data.url}" alt="">
            ${data.is_primary
                ? '<span class="dl-image-thumb-badge">Primary</span>'
                : `<button type="button" class="dl-image-thumb-set-primary" onclick="setPrimary(${data.id})">Set primary</button>`}
            <button type="button" class="dl-image-thumb-delete" onclick="deleteImage(${data.id}, this)">✕</button>`;
        grid.appendChild(div);
    }

    window.setPrimary = function(imageId) {
        fetch(`${API_BASE}${imageId}/set-primary`, {method: 'POST'})
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            grid.querySelectorAll('.dl-image-thumb').forEach(thumb => {
                const id  = parseInt(thumb.dataset.id);
                const topLeft = thumb.querySelector('.dl-image-thumb-badge, .dl-image-thumb-set-primary');
                if (id === imageId) {
                    if (topLeft) topLeft.outerHTML = '<span class="dl-image-thumb-badge">Primary</span>';
                } else {
                    if (topLeft && topLeft.tagName === 'SPAN') {
                        topLeft.outerHTML = `<button type="button" class="dl-image-thumb-set-primary" onclick="setPrimary(${id})">Set primary</button>`;
                    }
                }
            });
        });
    };

    window.deleteImage = function(imageId, btn) {
        if (!confirm('Delete this image?')) return;
        fetch(`${API_BASE}${imageId}/delete`, {method: 'POST'})
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const thumb = btn.closest('.dl-image-thumb');
                const wasPrimary = !!thumb.querySelector('.dl-image-thumb-badge');
                thumb.remove();
                if (wasPrimary) {
                    const first = grid.querySelector('.dl-image-thumb');
                    if (first) {
                        const id  = parseInt(first.dataset.id);
                        const topLeft = first.querySelector('.dl-image-thumb-set-primary');
                        if (topLeft) topLeft.outerHTML = '<span class="dl-image-thumb-badge">Primary</span>';
                        setPrimary(id);
                    }
                }
            }
        });
    };

    function escHtml(s) {
        return s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
})();
</script>
<?php endif; ?>
