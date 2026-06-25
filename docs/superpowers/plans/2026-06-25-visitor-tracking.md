# Visitor Tracking Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Track every page request (IP, country, URI, user agent, bot flag) and display analytics on a new `/admin/visitors` page.

**Architecture:** A `Visitor_model` handles all DB queries. `MY_Controller::__construct()` calls `_track_visit()` on every request — resolving the country once per unique IP via ip-api.com free API, then inserting a log row. The admin visitors page is owner-only and shows summary stats + a paginated, filterable log table.

**Tech Stack:** CodeIgniter 3, PHP, MySQL/MySQLi, ip-api.com (free, no key)

## Global Constraints

- CI3 conventions: models in `application/models/`, controllers extend `MY_Controller`, views in `application/views/`
- All DB identifiers lowercase with underscores
- All PHP files begin with `defined('BASEPATH') OR exit('No direct script access allowed');`
- Owner-only pages call `$this->require_owner()` (defined in `MY_Controller`)
- Admin pages rendered via `$this->_render('admin/view_name', $data)` in `Admin.php`
- Nav links for admin sidebar live in `application/views/partials/nav.php` lines 62–76

---

## File Map

| Action | File | Responsibility |
|--------|------|----------------|
| Create | `application/models/Visitor_model.php` | All visitor DB logic: log visit, IP cache, stats, paginated log |
| Create | `application/views/admin/visitors.php` | Admin visitors page: stats + filterable table |
| Modify | `db/seeds.sql` | Add CREATE TABLE for `visitor_ips` and `visitor_logs` |
| Modify | `application/core/MY_Controller.php` | Load visitor_model, call `_track_visit()` in constructor |
| Modify | `application/controllers/Admin.php` | Add `visitors()` method and load `visitor_model` |
| Modify | `application/config/routes.php` | Add `admin/visitors` route |
| Modify | `application/views/partials/nav.php` | Add "Visitors" link to admin subnav (owner-only) |

---

## Task 1: Database Tables

**Files:**
- Modify: `db/seeds.sql`

**Interfaces:**
- Produces: `visitor_ips` table, `visitor_logs` table — used by all subsequent tasks

- [ ] **Step 1: Add CREATE TABLE statements to seeds.sql**

Open `db/seeds.sql` and append the following at the end of the file:

```sql
-- ---------------------------------------------------------------
-- Visitor Tracking
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `visitor_ips` (
  `id`           INT          NOT NULL AUTO_INCREMENT,
  `ip_address`   VARCHAR(45)  NOT NULL,
  `country_code` VARCHAR(2)   DEFAULT NULL,
  `country_name` VARCHAR(100) DEFAULT NULL,
  `resolved_at`  DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_visitor_ips_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `ip_id`      INT          NOT NULL,
  `uri`        VARCHAR(500) NOT NULL DEFAULT '',
  `user_agent` VARCHAR(500) NOT NULL DEFAULT '',
  `is_bot`     TINYINT(1)   NOT NULL DEFAULT 0,
  `user_id`    INT          DEFAULT NULL,
  `created_at` DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_visitor_logs_ip_id` (`ip_id`),
  KEY `idx_visitor_logs_created_at` (`created_at`),
  CONSTRAINT `fk_visitor_logs_ip` FOREIGN KEY (`ip_id`) REFERENCES `visitor_ips` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 2: Run the SQL against your local database**

```bash
mysql -u root -p ci3_ecomm < db/seeds.sql
```

Expected: No errors. Verify with:
```sql
SHOW TABLES LIKE 'visitor%';
-- Should list: visitor_ips, visitor_logs
DESCRIBE visitor_ips;
DESCRIBE visitor_logs;
```

- [ ] **Step 3: Commit**

```bash
git add db/seeds.sql
git commit -m "feat: add visitor_ips and visitor_logs tables"
```

---

## Task 2: Visitor_model

**Files:**
- Create: `application/models/Visitor_model.php`

**Interfaces:**
- Consumes: `visitor_ips`, `visitor_logs` tables from Task 1
- Produces:
  - `log_visit(string $ip, string $uri, string $ua, int|null $user_id): void`
  - `get_stats(): object` — `->total_visits`, `->unique_ips`, `->visits_today`
  - `get_top_countries(int $limit): array` — each row has `->country_name`, `->visit_count`
  - `get_logs(array $filters, int $limit, int $offset): array`
  - `count_logs(array $filters): int`

- [ ] **Step 1: Create the model file**

Create `application/models/Visitor_model.php` with this content:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Visitor_model extends CI_Model
{
    private $_bot_patterns = ['bot', 'crawl', 'spider', 'slurp', 'mediapartners'];

    public function log_visit($ip, $uri, $ua, $user_id = null)
    {
        $ip_row = $this->_find_or_create_ip($ip);
        $is_bot = $this->_detect_bot($ua);

        $this->db->insert('visitor_logs', [
            'ip_id'      => $ip_row->id,
            'uri'        => substr($uri, 0, 500),
            'user_agent' => substr($ua, 0, 500),
            'is_bot'     => $is_bot ? 1 : 0,
            'user_id'    => $user_id ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function _find_or_create_ip($ip)
    {
        $row = $this->db->get_where('visitor_ips', ['ip_address' => $ip])->row();
        if ($row) {
            return $row;
        }

        $country = $this->_resolve_country($ip);
        $this->db->insert('visitor_ips', [
            'ip_address'   => $ip,
            'country_code' => $country['code'],
            'country_name' => $country['name'],
            'resolved_at'  => date('Y-m-d H:i:s'),
        ]);
        return $this->db->get_where('visitor_ips', ['id' => $this->db->insert_id()])->row();
    }

    private function _resolve_country($ip)
    {
        $url  = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=countryCode,country';
        $ctx  = stream_context_create(['http' => ['timeout' => 3]]);
        $resp = @file_get_contents($url, false, $ctx);

        if ($resp === false) {
            return ['code' => null, 'name' => null];
        }

        $data = json_decode($resp, true);
        if (!is_array($data) || empty($data['countryCode'])) {
            return ['code' => null, 'name' => null];
        }

        return [
            'code' => $data['countryCode'],
            'name' => $data['country'] ?? null,
        ];
    }

    private function _detect_bot($ua)
    {
        $ua_lower = strtolower($ua);
        foreach ($this->_bot_patterns as $pattern) {
            if (strpos($ua_lower, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    public function get_stats()
    {
        $total  = $this->db->count_all('visitor_logs');
        $unique = $this->db->count_all('visitor_ips');
        $today  = $this->db
            ->where('DATE(created_at)', date('Y-m-d'))
            ->count_all_results('visitor_logs');

        return (object) [
            'total_visits' => $total,
            'unique_ips'   => $unique,
            'visits_today' => $today,
        ];
    }

    public function get_top_countries($limit = 5)
    {
        return $this->db
            ->select('vi.country_name, COUNT(vl.id) AS visit_count')
            ->from('visitor_logs vl')
            ->join('visitor_ips vi', 'vi.id = vl.ip_id')
            ->where('vi.country_name IS NOT NULL')
            ->group_by('vi.country_name')
            ->order_by('visit_count', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    public function get_logs($filters = [], $limit = 25, $offset = 0)
    {
        $this->_apply_log_filters($filters);
        return $this->db
            ->select('vl.id, vl.uri, vl.user_agent, vl.is_bot, vl.user_id, vl.created_at, vi.ip_address, vi.country_name, vi.country_code')
            ->from('visitor_logs vl')
            ->join('visitor_ips vi', 'vi.id = vl.ip_id')
            ->order_by('vl.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    public function count_logs($filters = [])
    {
        $this->_apply_log_filters($filters);
        return $this->db
            ->from('visitor_logs vl')
            ->join('visitor_ips vi', 'vi.id = vl.ip_id')
            ->count_all_results();
    }

    private function _apply_log_filters($filters)
    {
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(vl.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(vl.created_at) <=', $filters['date_to']);
        }
        if (!empty($filters['country'])) {
            $this->db->where('vi.country_code', $filters['country']);
        }
        if ($filters['bot'] === '1') {
            $this->db->where('vl.is_bot', 1);
        } elseif ($filters['bot'] === '0') {
            $this->db->where('vl.is_bot', 0);
        }
    }
}
```

- [ ] **Step 2: Verify the file is syntactically valid**

```bash
php -l application/models/Visitor_model.php
```

Expected: `No syntax errors detected in application/models/Visitor_model.php`

- [ ] **Step 3: Commit**

```bash
git add application/models/Visitor_model.php
git commit -m "feat: add Visitor_model with log_visit, stats, and paginated log queries"
```

---

## Task 3: Hook tracking into MY_Controller

**Files:**
- Modify: `application/core/MY_Controller.php`

**Interfaces:**
- Consumes: `Visitor_model::log_visit(string $ip, string $uri, string $ua, int|null $user_id): void`
- Produces: `_track_visit()` called automatically on every request after session/auth setup

- [ ] **Step 1: Add visitor_model load and _track_visit() call to the constructor**

In `application/core/MY_Controller.php`, add the visitor model load and method call at the end of `__construct()`, just before the closing brace. Also add the private `_track_visit()` method at the end of the class.

The full updated file:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $current_user = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');

        $user_id = $this->session->userdata('user_id');
        if ($user_id) {
            $this->current_user = $this->user_model->find($user_id);

            // Guard: if user was banned after login, destroy session
            if ($this->current_user && $this->current_user->status === USER_BANNED) {
                $this->session->sess_destroy();
                $this->current_user = null;
                redirect('login');
            }
        }

        // Make current_user and nav categories available to all views
        $this->load->model('category_model');
        $nav_categories = $this->category_model->get_all();
        $this->load->vars([
            'current_user'   => $this->current_user,
            'categories'     => $nav_categories,
        ]);

        $this->load->model('visitor_model');
        $this->_track_visit();
    }

    protected function require_login()
    {
        if (!$this->current_user) {
            $this->session->set_userdata('redirect_after_login', current_url());
            redirect('login');
        }
    }

    protected function require_role($role)
    {
        $this->require_login();
        if ($this->current_user->role !== $role) {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    protected function require_role_in(array $roles)
    {
        $this->require_login();
        if (!in_array($this->current_user->role, $roles, true)) {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    protected function require_owner()
    {
        $this->require_login();
        if ($this->current_user->role !== ROLE_OWNER || $this->current_user->email !== OWNER_EMAIL) {
            show_error('You do not have permission to access this page.', 403);
        }
    }

    protected function json_response($data, $status = 200)
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    protected function redirect_with_message($url, $message, $type = 'success')
    {
        $this->session->set_flashdata($type, $message);
        redirect($url);
    }

    private function _track_visit()
    {
        // Get real client IP (proxy-aware)
        $forwarded = $this->input->server('HTTP_X_FORWARDED_FOR');
        if ($forwarded) {
            $parts = explode(',', $forwarded);
            $ip = trim($parts[0]);
        } else {
            $ip = $this->input->server('REMOTE_ADDR');
        }

        $uri     = $this->uri->uri_string();
        $ua      = (string) $this->input->user_agent();
        $user_id = $this->current_user ? $this->current_user->id : null;

        $this->visitor_model->log_visit($ip, $uri, $ua, $user_id);
    }
}
```

- [ ] **Step 2: Verify syntax**

```bash
php -l application/core/MY_Controller.php
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Load the site and verify a row is written**

Open the site homepage in a browser, then run:

```sql
SELECT vl.id, vi.ip_address, vi.country_name, vl.uri, vl.is_bot, vl.created_at
FROM visitor_logs vl
JOIN visitor_ips vi ON vi.id = vl.ip_id
ORDER BY vl.id DESC
LIMIT 5;
```

Expected: At least one row with your IP, a URI (e.g. empty string or `shop/home`), and `is_bot = 0`.

- [ ] **Step 4: Commit**

```bash
git add application/core/MY_Controller.php
git commit -m "feat: track every page request in MY_Controller via Visitor_model"
```

---

## Task 4: Admin controller method + route

**Files:**
- Modify: `application/controllers/Admin.php`
- Modify: `application/config/routes.php`

**Interfaces:**
- Consumes:
  - `Visitor_model::get_stats(): object`
  - `Visitor_model::get_top_countries(int $limit): array`
  - `Visitor_model::get_logs(array $filters, int $limit, int $offset): array`
  - `Visitor_model::count_logs(array $filters): int`
- Produces: `GET /admin/visitors` renders `admin/visitors` view

- [ ] **Step 1: Add visitor_model to Admin constructor model load list**

In `application/controllers/Admin.php` line 10, add `'visitor_model'` to the model array:

```php
$this->load->model(['user_model', 'store_model', 'product_model', 'order_model', 'review_model', 'coupon_model', 'contact_model', 'category_model', 'visitor_model']);
```

- [ ] **Step 2: Add the visitors() method to Admin.php**

Add this method before the `_render()` private method at the bottom of `Admin.php` (before line 356):

```php
public function visitors()
{
    $this->require_owner();

    $limit  = 25;
    $page   = max(1, (int) $this->input->get('page'));
    $offset = ($page - 1) * $limit;

    $filters = [
        'date_from' => $this->input->get('date_from') ?: '',
        'date_to'   => $this->input->get('date_to')   ?: '',
        'country'   => $this->input->get('country')   ?: '',
        'bot'       => $this->input->get('bot')        ?: '',
    ];

    $total = $this->visitor_model->count_logs($filters);

    $this->load->library('pagination');
    $this->pagination->initialize([
        'base_url'             => base_url('admin/visitors'),
        'total_rows'           => $total,
        'per_page'             => $limit,
        'uri_segment'          => 0,
        'use_page_numbers'     => TRUE,
        'query_string_segment' => 'page',
        'full_tag_open'        => '<ul class="pagination mb-0">',
        'full_tag_close'       => '</ul>',
        'first_tag_open'       => '<li class="page-item">',  'first_tag_close' => '</li>',
        'last_tag_open'        => '<li class="page-item">',  'last_tag_close'  => '</li>',
        'next_tag_open'        => '<li class="page-item">',  'next_tag_close'  => '</li>',
        'prev_tag_open'        => '<li class="page-item">',  'prev_tag_close'  => '</li>',
        'cur_tag_open'         => '<li class="page-item active"><a class="page-link" href="#">',
        'cur_tag_close'        => '</a></li>',
        'num_tag_open'         => '<li class="page-item">',  'num_tag_close'   => '</li>',
        'num_links'            => 3,
        'attributes'           => ['class' => 'page-link'],
        'reuse_query_string'   => TRUE,
    ]);

    $this->_render('admin/visitors', [
        'page_title'    => 'Visitor Analytics',
        'stats'         => $this->visitor_model->get_stats(),
        'top_countries' => $this->visitor_model->get_top_countries(5),
        'logs'          => $this->visitor_model->get_logs($filters, $limit, $offset),
        'total'         => $total,
        'filters'       => $filters,
        'pagination'    => $this->pagination->create_links(),
    ]);
}
```

- [ ] **Step 3: Add route to routes.php**

In `application/config/routes.php`, add after the last `admin/categories` line (around line 76):

```php
$route['admin/visitors']                = 'admin/visitors';
```

- [ ] **Step 4: Verify syntax**

```bash
php -l application/controllers/Admin.php
php -l application/config/routes.php
```

Expected: No syntax errors in both files.

- [ ] **Step 5: Commit**

```bash
git add application/controllers/Admin.php application/config/routes.php
git commit -m "feat: add admin visitors controller method and route"
```

---

## Task 5: Admin visitors view

**Files:**
- Create: `application/views/admin/visitors.php`

**Interfaces:**
- Consumes variables passed from `Admin::visitors()`:
  - `$stats` — object with `->total_visits`, `->unique_ips`, `->visits_today`
  - `$top_countries` — array of objects with `->country_name`, `->visit_count`
  - `$logs` — array of objects with `->ip_address`, `->country_name`, `->country_code`, `->uri`, `->user_agent`, `->is_bot`, `->user_id`, `->created_at`
  - `$filters` — array with keys `date_from`, `date_to`, `country`, `bot`
  - `$pagination` — HTML string from CI3 pagination library
  - `$total` — int total matching rows

- [ ] **Step 1: Create the view file**

Create `application/views/admin/visitors.php`:

```php
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
```

- [ ] **Step 2: Verify syntax**

```bash
php -l application/views/admin/visitors.php
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add application/views/admin/visitors.php
git commit -m "feat: add admin visitors analytics view"
```

---

## Task 6: Admin nav link

**Files:**
- Modify: `application/views/partials/nav.php`

**Interfaces:**
- Consumes: `$current_user->role === 'owner'` (already in template scope)
- Produces: "Visitors" link visible only to the owner in the admin subnav

- [ ] **Step 1: Add the Visitors nav link**

In `application/views/partials/nav.php`, inside the owner-only block (around line 72–74), add the Visitors link alongside the existing Messages link:

Find this block:
```php
            <?php if (isset($current_user) && $current_user->role === 'owner'): ?>
            <a href="<?= base_url('admin/contact-messages') ?>" class="<?= dl_nav_active('admin/contact-messages', $current_uri) ?>">Messages</a>
            <?php endif; ?>
```

Replace with:
```php
            <?php if (isset($current_user) && $current_user->role === 'owner'): ?>
            <a href="<?= base_url('admin/contact-messages') ?>" class="<?= dl_nav_active('admin/contact-messages', $current_uri) ?>">Messages</a>
            <a href="<?= base_url('admin/visitors') ?>" class="<?= dl_nav_active('admin/visitors', $current_uri) ?>">Visitors</a>
            <?php endif; ?>
```

- [ ] **Step 2: Commit**

```bash
git add application/views/partials/nav.php
git commit -m "feat: add Visitors nav link to admin subnav (owner-only)"
```

---

## Task 7: End-to-end verification

- [ ] **Step 1: Load the homepage a few times** (both logged out and logged in as owner)

- [ ] **Step 2: Verify rows accumulate**

```sql
SELECT COUNT(*) FROM visitor_logs;
SELECT COUNT(*) FROM visitor_ips;
```

- [ ] **Step 3: Navigate to `/admin/visitors` as owner**

Expected: Page loads with stats, top countries, and log table showing recent visits.

- [ ] **Step 4: Test filters**

- Set "Date From" to today → table should only show today's entries
- Toggle "Bots only" → table should show only rows where `is_bot = 1` (you may need to simulate one with a bot user-agent)
- Click Reset → all filters clear

- [ ] **Step 5: Verify a non-owner admin cannot access `/admin/visitors`**

Log in as an admin (non-owner) and navigate to `/admin/visitors`. Expected: 403 error.

- [ ] **Step 6: Verify ip-api.com fallback**

Temporarily break the URL in `_resolve_country()` (change hostname), reload the page, confirm a visitor_ips row is created with NULL country values, and the page still loads. Revert the change.

- [ ] **Step 7: Final commit if any fixes were needed**

```bash
git add -A
git commit -m "fix: visitor tracking end-to-end verification fixes"
```
