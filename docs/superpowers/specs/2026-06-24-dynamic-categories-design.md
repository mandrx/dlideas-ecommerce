# Dynamic Categories — Design Spec
Date: 2026-06-24

## Overview
Add admin CRUD for product categories so admins can manage category names, images, and sort order without code changes. Categories are stored in the existing `categories` DB table and displayed on the shop front-end.

## Scope
- Flat category list only (no subcategory nesting in the UI; `parent_id` remains in DB but is unused by this feature)
- Image upload to local disk (`assets/img/categories/`)
- Admin panel CRUD: list, create, edit, delete

## Database
No schema changes. Existing table:
```
categories: id, parent_id, name, slug, image, sort_order
```

## Model — `Category_model`
Add to `application/models/Category_model.php`:

- `create($data)` — insert row; auto-generate unique slug from name via `url_title(strtolower($name))`, appending `-2`/`-3` for collisions
- `update($id, $data)` — update name, image, sort_order; regenerate slug if name changed
- `delete($id)` — delete row; remove image file from disk if it exists

## Controller — `Admin`
Add to `application/controllers/Admin.php`:

| Method | Route | Action |
|---|---|---|
| `categories()` | GET `admin/categories` | List all |
| `category_form()` | GET `admin/categories/new` | Create form |
| `category_form($id)` | GET `admin/categories/edit/:id` | Edit form |
| `category_save()` | POST `admin/categories/save` | Create or update |
| `category_delete($id)` | POST `admin/categories/delete/:id` | Delete |

**Image upload rules:**
- Allowed types: jpg, jpeg, png, gif, webp
- Max size: 2 MB
- Save to: `assets/img/categories/`
- Filename: `{slug}-{uniqid()}.{ext}`
- On edit with new upload: delete old file from disk, store new path

## Views
- `application/views/admin/categories.php` — table: thumbnail, name, slug, sort order, edit/delete buttons. Style matches existing admin tables (coupons, users).
- `application/views/admin/category_form.php` — fields: Name (text, required), Sort Order (number, default 0), Image (file, optional on edit). Shows current image preview on edit. Style matches `coupon_form.php`.
- `application/views/layouts/dashboard.php` — add "Categories" nav link in sidebar under Products section.

## Shop Integration
No changes required. `application/views/shop/index.php` and `Shop` controller already load `$categories` and render `$cat->image` via `base_url()`.

## Routes
Add to `application/config/routes.php`:
```php
$route['admin/categories']              = 'admin/categories';
$route['admin/categories/new']          = 'admin/category_form';
$route['admin/categories/edit/(:num)']  = 'admin/category_form/$1';
$route['admin/categories/save']         = 'admin/category_save';
$route['admin/categories/delete/(:num)']= 'admin/category_delete/$1';
```

## Out of Scope
- Subcategory/parent management UI
- Image CDN or cloud storage
- Bulk operations
