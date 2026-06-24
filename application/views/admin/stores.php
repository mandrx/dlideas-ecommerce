<h2 class="mb-4">Manage Stores</h2>

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr><th>#</th><th>Store</th><th>Slug</th><th>Status</th><th>Created</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($stores as $s): ?>
    <?php
    $badge = match($s->status) {
        'active'    => 'success',
        'pending'   => 'warning text-dark',
        'suspended' => 'danger',
        default     => 'secondary',
    };
    ?>
    <tr>
        <td><?= $s->id ?></td>
        <td><?= htmlspecialchars($s->name) ?></td>
        <td><code><?= htmlspecialchars($s->slug) ?></code></td>
        <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($s->status) ?></span></td>
        <td><?= date('d M Y', strtotime($s->created_at)) ?></td>
        <td class="d-flex gap-1">
            <?php if ($s->status !== 'active'): ?>
            <a href="<?= base_url('admin/stores/' . $s->id . '/approve') ?>"
               class="btn btn-sm btn-success"
               onclick="return confirm('Approve this store?')">Approve</a>
            <?php endif; ?>
            <?php if ($s->status !== 'suspended'): ?>
            <a href="<?= base_url('admin/stores/' . $s->id . '/suspend') ?>"
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Suspend this store?')">Suspend</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= $pagination ?>
