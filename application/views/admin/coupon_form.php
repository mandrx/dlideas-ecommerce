<div class="dl-page-header">
    <h2>New Coupon</h2>
    <a href="<?= base_url('admin/coupons') ?>" class="dl-back-link">All Coupons</a>
</div>

<?php echo form_open('admin/coupons/add'); ?>
<?= csrf_field() ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="dl-form-card">
            <div class="dl-form-card-header">Coupon Details</div>
            <div class="dl-form-card-body">
                <div class="row g-4">
                    <div class="col-sm-6">
                        <label class="form-label" style="font-weight:800;">Coupon Code <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="code" class="form-control"
                               style="text-transform:uppercase;font-family:ui-monospace,'Cascadia Code',monospace;font-weight:700;letter-spacing:0.05em;"
                               value="<?= set_value('code') ?>" placeholder="e.g. SAVE10" required>
                        <?php if (form_error('code')): ?>
                        <p style="color:var(--danger);font-size:0.8rem;font-weight:700;margin-top:4px;"><?= form_error('code') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label" style="font-weight:800;">Type <span style="color:var(--danger);">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="percent" <?= set_value('type') === 'percent' ? 'selected' : '' ?>>Percent (%)</option>
                            <option value="fixed"   <?= set_value('type') === 'fixed'   ? 'selected' : '' ?>>Fixed (S$)</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label" style="font-weight:800;">Value <span style="color:var(--danger);">*</span></label>
                        <input type="number" name="value" step="0.01" min="0" class="form-control"
                               value="<?= set_value('value') ?>" required>
                        <?php if (form_error('value')): ?>
                        <p style="color:var(--danger);font-size:0.8rem;font-weight:700;margin-top:4px;"><?= form_error('value') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Min Order (S$)</label>
                        <input type="number" name="min_order" step="0.01" min="0" class="form-control"
                               value="<?= set_value('min_order', '0') ?>">
                        <p class="dl-form-hint">Leave 0 for no minimum.</p>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Max Uses <span style="color:var(--danger);">*</span></label>
                        <input type="number" name="max_uses" min="1" class="form-control"
                               value="<?= set_value('max_uses', '1') ?>" required>
                        <?php if (form_error('max_uses')): ?>
                        <p style="color:var(--danger);font-size:0.8rem;font-weight:700;margin-top:4px;"><?= form_error('max_uses') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Expires At <span style="color:var(--danger);">*</span></label>
                        <input type="datetime-local" name="expires_at" class="form-control"
                               value="<?= set_value('expires_at') ?>" required>
                        <?php if (form_error('expires_at')): ?>
                        <p style="color:var(--danger);font-size:0.8rem;font-weight:700;margin-top:4px;"><?= form_error('expires_at') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="dl-form-card-footer">
                <a href="<?= base_url('admin/coupons') ?>" class="dl-action-btn dl-action-btn--edit">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Coupon</button>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
