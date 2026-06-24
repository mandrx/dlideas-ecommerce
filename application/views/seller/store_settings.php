<div class="dl-page-header">
    <h2>Store Settings</h2>
</div>

<?php echo form_open('seller/store-settings/save'); ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="dl-form-card">
            <div class="dl-form-card-header">Store Information</div>
            <div class="dl-form-card-body">
                <div class="mb-4">
                    <label class="form-label" style="font-weight:800;">Store Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($store->name) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-weight:800;">Description</label>
                    <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($store->description ?? '') ?></textarea>
                    <p class="dl-form-hint">Tell shoppers what makes your store special. Shown on your public store page.</p>
                </div>
            </div>
            <div class="dl-form-card-footer">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="dl-form-card">
            <div class="dl-form-card-header">Store Status</div>
            <div class="dl-form-card-body">
                <span class="dl-store-status dl-store-status--<?= $store->status ?>">
                    <?= ucfirst($store->status) ?>
                </span>
                <?php if ($store->status === 'pending'): ?>
                <p style="margin-top:var(--space-3);font-size:0.85rem;color:var(--text-muted);">Your store is under review. We'll notify you once it's approved.</p>
                <?php elseif ($store->status === 'active'): ?>
                <p style="margin-top:var(--space-3);font-size:0.85rem;color:var(--text-muted);">Your store is live and visible to shoppers.</p>
                <?php else: ?>
                <p style="margin-top:var(--space-3);font-size:0.85rem;color:var(--text-muted);">Your store has been suspended. Contact support for more information.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php echo form_close(); ?>
