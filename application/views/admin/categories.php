<div class="dl-page-header">
    <h2>Manage Categories</h2>
    <a href="<?= base_url('admin/categories/new') ?>" class="btn btn-primary">+ New Category</a>
</div>

<?php if (empty($categories)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">🗂️</div>
    <h3>No categories yet</h3>
    <p>Add your first category to organise products on the shop.</p>
    <a href="<?= base_url('admin/categories/new') ?>" class="btn btn-primary">Add Category</a>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Sort Order</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $cat): ?>
    <tr>
        <td>
            <?php if (!empty($cat->image)): ?>
                <img src="<?= base_url($cat->image) ?>" alt="<?= htmlspecialchars($cat->name) ?>"
                     style="width:48px;height:48px;object-fit:contain;border-radius:6px;background:var(--bg-subtle);">
            <?php else: ?>
                <span style="font-size:1.8rem;line-height:1;">🗂️</span>
            <?php endif; ?>
        </td>
        <td style="font-weight:800;"><?= htmlspecialchars($cat->name) ?></td>
        <td style="color:var(--text-muted);font-size:0.85rem;font-family:ui-monospace,'Cascadia Code',monospace;"><?= htmlspecialchars($cat->slug) ?></td>
        <td style="color:var(--text-muted);"><?= (int) $cat->sort_order ?></td>
        <td style="display:flex;gap:8px;align-items:center;">
            <a href="<?= base_url('admin/categories/edit/' . $cat->id) ?>" class="dl-action-btn dl-action-btn--edit">Edit</a>
            <?php echo form_open('admin/categories/delete/' . $cat->id, ['style' => 'display:inline;']); ?>
            <?= csrf_field() ?>
            <button type="submit" class="dl-action-btn dl-action-btn--danger"
                    onclick="return confirm('Delete <?= htmlspecialchars(addslashes($cat->name)) ?>? This cannot be undone.')">
                Delete
            </button>
            <?php echo form_close(); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
