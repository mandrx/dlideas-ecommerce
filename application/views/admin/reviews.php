<div class="dl-page-header">
    <div>
        <h2>Manage Reviews</h2>
        <?php
        $pending_count = count(array_filter($reviews, fn($r) => $r->status === 'pending'));
        if ($pending_count > 0): ?>
        <p class="dl-page-subtitle"><?= $pending_count ?> pending moderation</p>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($reviews)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">💬</div>
    <h3>No reviews yet</h3>
    <p>Customer reviews will appear here once submitted.</p>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Reviewer</th>
            <th>Product</th>
            <th>Rating</th>
            <th>Review</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($reviews as $r): ?>
    <?php
    $status_class = match($r->status) {
        'approved' => 'delivered',
        'pending'  => 'pending',
        'rejected' => 'cancelled',
        default    => 'cancelled',
    };
    ?>
    <tr>
        <td style="color:var(--text-muted);font-size:0.82rem;"><?= $r->id ?></td>
        <td style="font-weight:700;color:var(--text-dark);white-space:nowrap;"><?= htmlspecialchars($r->reviewer_name) ?></td>
        <td style="max-width:160px;">
            <a href="<?= base_url('product/' . $r->product_slug) ?>" target="_blank"
               style="font-weight:700;color:var(--text-dark);transition:color var(--t-fast);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
               onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dark)'">
                <?= htmlspecialchars($r->product_name) ?> <span style="font-size:0.7rem;color:var(--text-muted);">↗</span>
            </a>
        </td>
        <td>
            <span class="dl-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $r->rating ? 'filled' : 'empty' ?>">★</span>
                <?php endfor; ?>
            </span>
        </td>
        <td>
            <span class="dl-review-body" title="<?= htmlspecialchars($r->body) ?>">
                <?= htmlspecialchars($r->body) ?>
            </span>
        </td>
        <td><span class="dl-status-badge dl-status-badge--<?= $status_class ?>"><?= ucfirst($r->status) ?></span></td>
        <td>
            <div class="dl-table-actions">
                <?php if ($r->status !== 'approved'): ?>
                <a href="<?= base_url('admin/reviews/' . $r->id . '/approve') ?>"
                   class="dl-action-btn dl-action-btn--approve">Approve</a>
                <?php endif; ?>
                <?php if ($r->status !== 'rejected'): ?>
                <a href="<?= base_url('admin/reviews/' . $r->id . '/reject') ?>"
                   class="dl-action-btn dl-action-btn--danger">Reject</a>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
