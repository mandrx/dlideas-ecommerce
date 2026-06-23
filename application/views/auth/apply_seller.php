<p class="text-muted">Fill in your store details. Your application will be reviewed by an admin.</p>
<?= form_open('apply-seller/post') ?>
<div class="mb-3">
    <label for="store_name" class="form-label">Store Name</label>
    <input type="text" class="form-control" id="store_name" name="store_name"
           value="<?= set_value('store_name') ?>" required>
</div>
<div class="mb-3">
    <label for="store_description" class="form-label">Description</label>
    <textarea class="form-control" id="store_description" name="store_description"
              rows="4"><?= set_value('store_description') ?></textarea>
</div>
<button type="submit" class="btn btn-primary w-100">Submit Application</button>
<?= form_close() ?>
