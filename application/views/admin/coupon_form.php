<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">New Coupon</h2>
    <a href="<?= base_url('admin/coupons') ?>" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<?php echo form_open('admin/coupons/add'); ?>
<?= csrf_field() ?>
<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-6">
                <label class="form-label">Coupon Code *</label>
                <input type="text" name="code" class="form-control text-uppercase"
                       value="<?= set_value('code') ?>" placeholder="e.g. SAVE10" required>
                <div class="text-danger small"><?= form_error('code') ?></div>
            </div>
            <div class="col-sm-3">
                <label class="form-label">Type *</label>
                <select name="type" class="form-select" required>
                    <option value="percent" <?= set_value('type') === 'percent' ? 'selected' : '' ?>>Percent (%)</option>
                    <option value="fixed"   <?= set_value('type') === 'fixed'   ? 'selected' : '' ?>>Fixed (RM)</option>
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label">Value *</label>
                <input type="number" name="value" step="0.01" min="0" class="form-control"
                       value="<?= set_value('value') ?>" required>
                <div class="text-danger small"><?= form_error('value') ?></div>
            </div>
            <div class="col-sm-4">
                <label class="form-label">Min Order (RM)</label>
                <input type="number" name="min_order" step="0.01" min="0" class="form-control"
                       value="<?= set_value('min_order', '0') ?>">
            </div>
            <div class="col-sm-4">
                <label class="form-label">Max Uses *</label>
                <input type="number" name="max_uses" min="1" class="form-control"
                       value="<?= set_value('max_uses', '1') ?>" required>
                <div class="text-danger small"><?= form_error('max_uses') ?></div>
            </div>
            <div class="col-sm-4">
                <label class="form-label">Expires At *</label>
                <input type="datetime-local" name="expires_at" class="form-control"
                       value="<?= set_value('expires_at') ?>" required>
                <div class="text-danger small"><?= form_error('expires_at') ?></div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" class="btn btn-primary">Create Coupon</button>
        <a href="<?= base_url('admin/coupons') ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
    </div>
</div>
<?php echo form_close(); ?>
