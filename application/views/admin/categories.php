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
<table class="dl-orders-table dl-categories-table">
    <thead>
        <tr>
            <th class="col-id">#</th>
            <th class="col-img">Image</th>
            <th>Name</th>
            <th>Slug</th>
            <th class="col-order">Sort Order</th>
            <th class="col-actions"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $cat): ?>
    <tr>
        <td class="col-id" style="color:var(--text-muted);font-size:0.82rem;"><?= $cat->id ?></td>
        <td class="col-img">
            <div class="dl-cat-thumb">
                <?php if (!empty($cat->image)): ?>
                    <img src="<?= base_url($cat->image) ?>" alt="<?= htmlspecialchars($cat->name) ?>">
                <?php else: ?>
                    <span class="dl-cat-thumb-placeholder">🗂️</span>
                <?php endif; ?>
            </div>
        </td>
        <td class="col-name"><strong><?= htmlspecialchars($cat->name) ?></strong></td>
        <td class="col-slug"><code><?= htmlspecialchars($cat->slug) ?></code></td>
        <td class="col-order"><?= (int) $cat->sort_order ?></td>
        <td class="col-actions">
            <div class="dl-row-actions">
                <a href="<?= base_url('admin/categories/edit/' . $cat->id) ?>" class="dl-action-btn dl-action-btn--edit">Edit</a>
                <?php echo form_open('admin/categories/delete/' . $cat->id); ?>
                <?= csrf_field() ?>
                <button type="submit" class="dl-action-btn dl-action-btn--danger"
                        onclick="return confirm('Delete <?= htmlspecialchars(addslashes($cat->name)) ?>? This cannot be undone.')">
                    Delete
                </button>
                <?php echo form_close(); ?>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<style>
.dl-categories-table .col-id   { width: 48px; }
.dl-categories-table .col-img  { width: 72px; }
.dl-categories-table .col-order { width: 100px; text-align: center; color: var(--text-muted); }
.dl-categories-table .col-actions { width: 140px; }
.dl-categories-table .col-slug code {
    font-family: ui-monospace, 'Cascadia Code', monospace;
    font-size: 0.83rem;
    color: var(--text-muted);
    background: none;
    padding: 0;
}

.dl-cat-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    background: var(--bg-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.dl-cat-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.dl-cat-thumb-placeholder {
    font-size: 1.5rem;
    line-height: 1;
}

.dl-row-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: flex-end;
}
.dl-row-actions form {
    display: contents;
}
</style>
<?php endif; ?>
