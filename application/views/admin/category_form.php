<div class="dl-page-header">
    <h2><?= $category ? 'Edit Category' : 'New Category' ?></h2>
    <a href="<?= base_url('admin/categories') ?>" class="dl-back-link">All Categories</a>
</div>

<?php echo form_open_multipart('admin/categories/save'); ?>
<?= csrf_field() ?>
<?php if ($category): ?>
    <input type="hidden" name="id" value="<?= $category->id ?>">
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="dl-form-card">
            <div class="dl-form-card-header">Category Details</div>
            <div class="dl-form-card-body">
                <div class="row g-4">
                    <div class="col-sm-8">
                        <label class="form-label" style="font-weight:800;">Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="<?= set_value('name', $category ? $category->name : '') ?>"
                               placeholder="e.g. Toys &amp; Games" required maxlength="100">
                        <?php if (form_error('name')): ?>
                        <p style="color:var(--danger);font-size:0.8rem;font-weight:700;margin-top:4px;"><?= form_error('name') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" min="0"
                               value="<?= set_value('sort_order', $category ? $category->sort_order : '0') ?>">
                        <p class="dl-form-hint">Lower numbers appear first.</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-weight:800;">Image</label>
                        <?php if ($category && !empty($category->image)): ?>
                        <div style="margin-bottom:12px;">
                            <img src="<?= base_url($category->image) ?>" alt="Current image"
                                 style="width:80px;height:80px;object-fit:contain;border-radius:8px;background:var(--bg-subtle);padding:6px;border:1px solid var(--border);">
                            <p class="dl-form-hint" style="margin-top:4px;">Upload a new image to replace the current one.</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <p class="dl-form-hint">JPG, PNG, GIF, or WebP. Max 2 MB. Recommended: square, at least 200×200 px.</p>
                    </div>
                </div>
            </div>
            <div class="dl-form-card-footer">
                <a href="<?= base_url('admin/categories') ?>" class="dl-action-btn dl-action-btn--edit">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <?= $category ? 'Save Changes' : 'Create Category' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
