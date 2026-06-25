<div class="dl-page-header">
    <div>
        <h2>Visitor Analytics</h2>
        <p class="dl-page-subtitle">Traffic log across all pages</p>
    </div>
</div>

<!-- Summary Stats -->
<div class="dl-stat-grid" style="margin-bottom:var(--space-6);">
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
        <div class="dl-stat-label">Today</div>
    </div>
</div>

<!-- Top Countries -->
<?php if (!empty($top_countries)): ?>
<div class="dl-visitors-countries">
    <h3 class="dl-section-title">Top Countries</h3>
    <div class="dl-visitors-country-list">
    <?php foreach ($top_countries as $i => $c): ?>
        <div class="dl-visitors-country-row">
            <span class="dl-visitors-country-rank"><?= $i + 1 ?></span>
            <span class="dl-visitors-country-name"><?= htmlspecialchars($c->country_name ?: 'Unknown') ?></span>
            <span class="dl-visitors-country-count"><?= number_format($c->visit_count) ?></span>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<form method="get" action="<?= base_url('admin/visitors') ?>" class="dl-filter-bar">
    <div class="dl-filter-group">
        <label class="dl-filter-label">From</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>" class="form-control form-control-sm">
    </div>
    <div class="dl-filter-group">
        <label class="dl-filter-label">To</label>
        <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>" class="form-control form-control-sm">
    </div>
    <div class="dl-filter-group">
        <label class="dl-filter-label">Traffic</label>
        <select name="bot" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="0" <?= $filters['bot'] === '0' ? 'selected' : '' ?>>Human only</option>
            <option value="1" <?= $filters['bot'] === '1' ? 'selected' : '' ?>>Bots only</option>
        </select>
    </div>
    <div class="dl-filter-actions">
        <button type="submit" class="dl-btn dl-btn-primary dl-btn-sm">Filter</button>
        <a href="<?= base_url('admin/visitors') ?>" class="dl-btn dl-btn-ghost dl-btn-sm">Reset</a>
    </div>
</form>

<!-- Log Table -->
<div class="dl-table-meta">
    <span><?= number_format($total) ?> records</span>
</div>

<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Date / Time</th>
            <th>IP Address</th>
            <th>Country</th>
            <th>URI</th>
            <th>Type</th>
            <th>User</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($logs)): ?>
    <tr>
        <td colspan="6" class="dl-table-empty">No records match your filters.</td>
    </tr>
    <?php else: ?>
    <?php foreach ($logs as $log): ?>
    <tr>
        <td class="dl-visitors-time"><?= date('d M Y H:i', strtotime($log->created_at)) ?></td>
        <td class="dl-visitors-ip"><?= htmlspecialchars($log->ip_address) ?></td>
        <td class="dl-visitors-country">
            <?php if ($log->country_code): ?>
                <?= htmlspecialchars($log->country_name) ?>
                <span class="dl-visitors-country-code"><?= htmlspecialchars($log->country_code) ?></span>
            <?php else: ?>
                <span class="dl-text-muted">—</span>
            <?php endif; ?>
        </td>
        <td class="dl-visitors-uri" title="/<?= htmlspecialchars($log->uri) ?>">
            <span class="dl-visitors-uri-slash">/</span><?= htmlspecialchars($log->uri) ?>
        </td>
        <td>
            <?php if ($log->is_bot): ?>
                <span class="dl-status-badge dl-status-badge--bot">Bot</span>
            <?php else: ?>
                <span class="dl-status-badge dl-status-badge--human">Human</span>
            <?php endif; ?>
        </td>
        <td class="dl-visitors-user">
            <?= $log->user_id ? '#' . $log->user_id : '<span class="dl-text-muted">Guest</span>' ?>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
</div>

<?php if ($pagination): ?>
<div class="dl-pagination-wrap"><?= $pagination ?></div>
<?php endif; ?>
