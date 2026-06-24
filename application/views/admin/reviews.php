<h2 class="mb-4">Manage Reviews</h2>

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr><th>#</th><th>Reviewer</th><th>Product</th><th>Rating</th><th>Body</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($reviews as $r): ?>
    <?php
    $badge = match($r->status) {
        'approved' => 'success',
        'pending'  => 'warning text-dark',
        'rejected' => 'danger',
        default    => 'secondary',
    };
    ?>
    <tr>
        <td><?= $r->id ?></td>
        <td><?= htmlspecialchars($r->reviewer_name) ?></td>
        <td>
            <a href="<?= base_url('product/' . $r->product_slug) ?>" target="_blank">
                <?= htmlspecialchars($r->product_name) ?>
            </a>
        </td>
        <td><?= str_repeat('★', $r->rating) ?><?= str_repeat('☆', 5 - $r->rating) ?></td>
        <td><span class="text-truncate d-inline-block" style="max-width:200px;"><?= htmlspecialchars($r->body) ?></span></td>
        <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($r->status) ?></span></td>
        <td class="d-flex gap-1">
            <?php if ($r->status !== 'approved'): ?>
            <a href="<?= base_url('admin/reviews/' . $r->id . '/approve') ?>"
               class="btn btn-sm btn-success">Approve</a>
            <?php endif; ?>
            <?php if ($r->status !== 'rejected'): ?>
            <a href="<?= base_url('admin/reviews/' . $r->id . '/reject') ?>"
               class="btn btn-sm btn-outline-danger">Reject</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php if (empty($reviews)): ?>
<p class="text-muted text-center py-4">No reviews yet.</p>
<?php endif; ?>
