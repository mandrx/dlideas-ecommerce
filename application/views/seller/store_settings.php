<h2 class="mb-4">Store Settings</h2>

<?php echo form_open('seller/store-settings/save'); ?>
<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Store Name *</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($store->name) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($store->description ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
</div>
<?php echo form_close(); ?>
