<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Manage Users</h2>
</div>

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= $u->id ?></td>
        <td><?= htmlspecialchars($u->full_name) ?></td>
        <td><?= htmlspecialchars($u->email) ?></td>
        <td><span class="badge bg-secondary"><?= ucfirst($u->role) ?></span></td>
        <td>
            <?php if ($u->status === 'banned'): ?>
            <span class="badge bg-danger">Banned</span>
            <?php else: ?>
            <span class="badge bg-success">Active</span>
            <?php endif; ?>
        </td>
        <td><?= date('d M Y', strtotime($u->created_at)) ?></td>
        <td>
            <?php if ($u->role !== 'admin'): ?>
            <?php if ($u->status === 'banned'): ?>
            <a href="<?= base_url('admin/users/' . $u->id . '/unban') ?>"
               class="btn btn-sm btn-outline-success"
               onclick="return confirm('Unban this user?')">Unban</a>
            <?php else: ?>
            <a href="<?= base_url('admin/users/' . $u->id . '/ban') ?>"
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Ban this user?')">Ban</a>
            <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= $pagination ?>
