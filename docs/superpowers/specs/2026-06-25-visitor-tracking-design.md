# Visitor Tracking — Design Spec
**Date:** 2026-06-25  
**Status:** Approved

## Overview

Track every page request on the site, recording the visitor's IP, resolved country, URI, user agent, and bot flag. Country is resolved once per unique IP via the free ip-api.com API and cached in the database. Analytics are exposed to the owner via a new `/admin/visitors` page.

---

## Goals

- Log every HTTP request (bots included, but flagged)
- Resolve country from IP once, reuse on repeat visits
- Show visit stats and a filterable log table in the admin panel

## Non-Goals

- Blocking or rate-limiting visitors
- Real-time streaming/live dashboard
- GDPR consent UI (out of scope for now)

---

## Database

### Table: `visitor_ips`

One row per unique IP address. Country is resolved on first encounter.

| Column         | Type         | Notes                        |
|----------------|--------------|------------------------------|
| id             | INT PK AI    |                              |
| ip_address     | VARCHAR(45)  | UNIQUE, supports IPv6        |
| country_code   | VARCHAR(2)   | Nullable — NULL if API fails |
| country_name   | VARCHAR(100) | Nullable                     |
| resolved_at    | DATETIME     |                              |

### Table: `visitor_logs`

One row per page request.

| Column      | Type         | Notes                              |
|-------------|--------------|------------------------------------|
| id          | INT PK AI    |                                    |
| ip_id       | INT FK       | → visitor_ips.id                   |
| uri         | VARCHAR(500) | Current request URI                |
| user_agent  | VARCHAR(500) | Raw User-Agent header              |
| is_bot      | TINYINT(1)   | 1 if UA matches crawler patterns   |
| user_id     | INT FK       | Nullable → users.id (if logged in) |
| created_at  | DATETIME     |                                    |

---

## Tracking Logic

### Location
`MY_Controller::__construct()` — after existing session/auth/category setup.

### Flow

1. **Get client IP** — check `HTTP_X_FORWARDED_FOR` first, fall back to `REMOTE_ADDR`. Take the first IP in a comma-separated list.
2. **Resolve country** — query `visitor_ips` by IP.
   - If found: reuse existing row.
   - If not found: call `http://ip-api.com/json/{ip}?fields=countryCode,country` with a 3-second timeout. Store result (or NULLs on failure) in `visitor_ips`.
3. **Detect bot** — match User-Agent (case-insensitive) against: `bot`, `crawl`, `spider`, `slurp`, `mediapartners`.
4. **Insert log row** — write to `visitor_logs` with `ip_id`, `uri`, `user_agent`, `is_bot`, `user_id` (from session, nullable).

### Error Handling
- ip-api.com failure (timeout, non-200, invalid JSON): insert `visitor_ips` row with `country_code = NULL`, `country_name = NULL`. Tracking continues normally.
- Never throw an exception or halt the request due to tracking failure.

---

## New Files

| File | Purpose |
|------|---------|
| `application/models/Visitor_model.php` | DB queries: log visit, resolve/cache IP, stats, paginated log |
| `application/views/admin/visitors.php` | Admin visitors page view |

## Modified Files

| File | Change |
|------|--------|
| `application/core/MY_Controller.php` | Add `_track_visit()` call in constructor, load `visitor_model` |
| `application/controllers/Admin.php` | Add `visitors()` method (owner-only) |
| `application/config/routes.php` | Add `admin/visitors` route |
| `application/views/admin/*.php` | Add "Visitors" nav link to admin sidebar |
| `db/seeds.sql` | Add `CREATE TABLE` statements for both new tables |

---

## Admin Page: `/admin/visitors`

**Access:** Owner role only.

### Summary Stats (top of page)
- Total visits (all time)
- Unique IPs
- Visits today
- Top 5 countries (name + count)

### Log Table
- Columns: Date/Time, IP Address, Country, URI, Bot, User
- Filters: date range, country (dropdown), bot toggle (all / human only / bots only)
- Pagination: 25 rows per page

---

## Visitor_model Methods

| Method | Description |
|--------|-------------|
| `log_visit($ip, $uri, $ua, $user_id)` | Main entry point — resolves IP, detects bot, inserts log row |
| `find_or_create_ip($ip)` | Returns `visitor_ips` row, creating + resolving if new |
| `resolve_country($ip)` | Calls ip-api.com, returns `['code' => ..., 'name' => ...]` or NULLs |
| `get_stats()` | Returns total visits, unique IPs, visits today |
| `get_top_countries($limit)` | Returns country name + visit count, ordered DESC |
| `get_logs($filters, $limit, $offset)` | Paginated log rows with IP and country joined |

---

## ip-api.com Integration

- Endpoint: `http://ip-api.com/json/{ip}?fields=countryCode,country`
- Free tier: 45 requests/minute (sufficient — only called once per unique IP)
- No API key required
- Response: `{"countryCode": "US", "country": "United States"}`
