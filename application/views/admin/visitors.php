<div class="dl-page-header">
    <h2>Visitor Analytics</h2>
</div>

<!-- Summary Stats -->
<div class="dl-stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:var(--space-6);">
    <div class="dl-stat-card">
        <div class="dl-stat-value"><?= number_format($stats->total_visits) ?></div>
        <div class="dl-stat-label">Total Visits</div>
    </div>
    <div class="dl-stat-card">
        <div class="dl-stat-value"><?= number_format($stats->unique_ips) ?></div>
        <div class="dl-stat-label">Unique IPs</div>
    </div>
    <div class="dl-stat-card">
        <div class="dl-stat-value"><?= number_format($stats->visits_today) ?></div>
        <div class="dl-stat-label">Visits Today</div>
    </div>
</div>

<!-- Top Countries -->
<?php if (!empty($top_countries)): ?>
<div style="margin-bottom:var(--space-6);">
    <h5 style="font-weight:700;margin-bottom:var(--space-3);">Top Countries</h5>
    <div style="display:flex;gap:var(--space-3);flex-wrap:wrap;">
    <?php foreach ($top_countries as $c): ?>
        <div class="dl-stat-card" style="min-width:140px;text-align:center;">
            <div class="dl-stat-value" style="font-size:1.1rem;"><?= number_format($c->visit_count) ?></div>
            <div class="dl-stat-label"><?= htmlspecialchars($c->country_name ?: 'Unknown') ?></div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<form method="get" action="<?= base_url('admin/visitors') ?>" class="dl-filter-bar" style="display:flex;gap:var(--space-3);flex-wrap:wrap;align-items:flex-end;margin-bottom:var(--space-5);">
    <div>
        <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">From</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>" class="form-control form-control-sm">
    </div>
    <div>
        <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">To</label>
        <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>" class="form-control form-control-sm">
    </div>
    <div>
        <label style="font-size:0.82rem;font-weight:600;display:block;margin-bottom:4px;">Traffic</label>
        <select name="bot" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="0" <?= $filters['bot'] === '0' ? 'selected' : '' ?>>Human only</option>
            <option value="1" <?= $filters['bot'] === '1' ? 'selected' : '' ?>>Bots only</option>
        </select>
    </div>
    <div>
        <button type="submit" class="dl-btn-primary" style="padding:6px 16px;font-size:0.88rem;">Filter</button>
        <a href="<?= base_url('admin/visitors') ?>" class="dl-btn-ghost" style="padding:6px 14px;font-size:0.88rem;margin-left:6px;">Reset</a>
    </div>
</form>

<!-- Log Table -->
<p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:var(--space-3);"><?= number_format($total) ?> records found</p>

<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Date / Time</th>
            <th>IP Address</th>
            <th>Country</th>
            <th>URI</th>
            <th>Bot</th>
            <th>User</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($logs)): ?>
    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:2rem;">No records found.</td></tr>
    <?php else: ?>
    <?php foreach ($logs as $log): ?>
    <tr>
        <td style="font-size:0.82rem;color:var(--text-muted);white-space:nowrap;">
            <?= date('d M Y H:i', strtotime($log->created_at)) ?>
        </td>
        <td style="font-family:monospace;font-size:0.85rem;"><?= htmlspecialchars($log->ip_address) ?></td>
        <td style="font-size:0.85rem;">
            <?php if ($log->country_code): ?>
                <?= htmlspecialchars($log->country_name) ?>
                <span style="color:var(--text-muted);font-size:0.78rem;">(<?= htmlspecialchars($log->country_code) ?>)</span>
            <?php else: ?>
                <span style="color:var(--text-muted);">—</span>
            <?php endif; ?>
        </td>
        <td style="font-size:0.82rem;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($log->uri) ?>">
            /<?= htmlspecialchars($log->uri) ?>
        </td>
        <td>
            <?php if ($log->is_bot): ?>
                <span class="dl-status-badge" style="background:var(--warning-50,#fef9c3);color:var(--warning-700,#a16207);">Bot</span>
            <?php else: ?>
                <span style="color:var(--text-muted);font-size:0.82rem;">—</span>
            <?php endif; ?>
        </td>
        <td style="font-size:0.82rem;color:var(--text-muted);">
            <?= $log->user_id ? '#' . $log->user_id : '—' ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
</div>

<?php if ($pagination): ?>
<div style="margin-top:var(--space-4);"><?= $pagination ?></div>
<?php endif; ?>
