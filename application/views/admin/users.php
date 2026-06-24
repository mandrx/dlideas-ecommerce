<div class="dl-page-header">
    <h2>Manage Users</h2>
</div>

<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Joined</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
        <td style="color:var(--text-muted);font-size:0.82rem;"><?= $u->id ?></td>
        <td style="font-weight:700;color:var(--text-dark);"><?= htmlspecialchars($u->full_name) ?></td>
        <td style="color:var(--text-muted);font-size:0.88rem;"><?= htmlspecialchars($u->email) ?></td>
        <td>
            <span class="dl-role-badge dl-role-badge--<?= $u->role ?>">
                <?= ucfirst($u->role) ?>
            </span>
        </td>
        <td>
            <?php if ($u->status === 'banned'): ?>
            <span class="dl-status-badge dl-user-status--banned">Banned</span>
            <?php else: ?>
            <span class="dl-status-badge dl-user-status--active">Active</span>
            <?php endif; ?>
        </td>
        <td style="color:var(--text-muted);font-size:0.85rem;"><?= date('d M Y', strtotime($u->created_at)) ?></td>
        <td>
            <?php if ($u->role !== 'admin'): ?>
            <?php if ($u->status === 'banned'): ?>
            <a href="<?= base_url('admin/users/' . $u->id . '/unban') ?>"
               class="dl-action-btn dl-action-btn--approve"
               onclick="return confirm('Unban this user?')">Unban</a>
            <?php else: ?>
            <a href="<?= base_url('admin/users/' . $u->id . '/ban') ?>"
               class="dl-action-btn dl-action-btn--danger"
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
