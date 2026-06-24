# Dynamic Categories Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add admin CRUD for product categories (name, image, sort order) so admins can manage them without touching code.

**Architecture:** Extend the existing `Category_model` with write methods, add five methods to the `Admin` controller, and create two admin views. Image files are uploaded to `assets/img/categories/` on disk and the relative path is stored in the `image` column. No schema changes needed.

**Tech Stack:** CodeIgniter 3, PHP, MySQL, CI3 Upload library, CI3 Form Validation

## Global Constraints

- CI3 conventions: `defined('BASEPATH') OR exit(...)` at top of every PHP file
- CSRF: every POST form must include `<?= csrf_field() ?>`
- Auth guard: Admin controller constructor calls `$this->require_role_in([ROLE_ADMIN, ROLE_OWNER])` — already in place
- Slug generation: `url_title(strtolower($name))` (CI3 URL helper)
- Image allowed types: jpg|jpeg|png|gif|webp, max 2 MB
- Image save path: `assets/img/categories/` (relative to FCPATH)
- Admin views use the `dashboard` layout via `$this->_render('admin/view', $data)`
- Flash messages: `$this->session->set_flashdata('success'|'error', 'msg')`

---

### Task 1: Add write methods to Category_model

**Files:**
- Modify: `application/models/Category_model.php`

**Interfaces:**
- Produces:
  - `create(array $data): int` — returns new category ID
  - `update(int $id, array $data): void`
  - `delete(int $id): void`
  - `slug_exists(string $slug, int $exclude_id = 0): bool`

- [ ] **Step 1: Open the file and verify current state**

Read `application/models/Category_model.php`. Confirm it has `get_all_with_parent()`, `get_parents()`, `get_dropdown()` and nothing else.

- [ ] **Step 2: Add the four methods**

Replace the closing `}` of the class with the following, then close `}`:

```php
    public function slug_exists($slug, $exclude_id = 0)
    {
        $this->db->where('slug', $slug);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function make_unique_slug($name, $exclude_id = 0)
    {
        $this->load->helper('url');
        $base = url_title(strtolower($name), '-', TRUE);
        $slug = $base;
        $i    = 2;
        while ($this->slug_exists($slug, $exclude_id)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function create($data)
    {
        $data['slug'] = $this->make_unique_slug($data['name']);
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if (!empty($data['name'])) {
            $data['slug'] = $this->make_unique_slug($data['name'], $id);
        }
        $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        $cat = $this->db->where('id', $id)->get($this->table)->row();
        if ($cat && !empty($cat->image) && file_exists(FCPATH . $cat->image)) {
            @unlink(FCPATH . $cat->image);
        }
        $this->db->where('id', $id)->delete($this->table);
    }
```

- [ ] **Step 3: Commit**

```bash
git add application/models/Category_model.php
git commit -m "feat: add create/update/delete/slug methods to Category_model"
```

---

### Task 2: Add category routes

**Files:**
- Modify: `application/config/routes.php`

**Interfaces:**
- Produces: URL patterns that map to Admin controller methods added in Task 3

- [ ] **Step 1: Add routes after the existing `admin/contact-messages` lines**

Open `application/config/routes.php`. After line:
```php
$route['admin/contact-messages/(:num)']       = 'admin/view_message/$1';
```

Insert:
```php
$route['admin/categories']                    = 'admin/categories';
$route['admin/categories/new']                = 'admin/category_form';
$route['admin/categories/edit/(:num)']        = 'admin/category_form/$1';
$route['admin/categories/save']               = 'admin/category_save';
$route['admin/categories/delete/(:num)']      = 'admin/category_delete/$1';
```

- [ ] **Step 2: Commit**

```bash
git add application/config/routes.php
git commit -m "feat: add admin category routes"
```

---

### Task 3: Add category methods to Admin controller

**Files:**
- Modify: `application/controllers/Admin.php`

**Interfaces:**
- Consumes: `Category_model::get_all_with_parent()`, `create()`, `update()`, `delete()` (Task 1)
- Produces:
  - `Admin::categories()` — renders list view
  - `Admin::category_form($id = NULL)` — renders create/edit form
  - `Admin::category_save()` — handles POST, image upload
  - `Admin::category_delete($id)` — handles POST delete

- [ ] **Step 1: Load category_model in the constructor**

In `Admin::__construct()`, find:
```php
$this->load->model(['user_model', 'store_model', 'product_model', 'order_model', 'review_model', 'coupon_model', 'contact_model']);
```
Replace with:
```php
$this->load->model(['user_model', 'store_model', 'product_model', 'order_model', 'review_model', 'coupon_model', 'contact_model', 'category_model']);
```

- [ ] **Step 2: Add the four methods at the end of the class (before the closing `}`)**

```php
    // --- Categories ---

    public function categories()
    {
        $this->_render('admin/categories', [
            'page_title'  => 'Manage Categories',
            'categories'  => $this->category_model->get_all_with_parent(),
        ]);
    }

    public function category_form($id = NULL)
    {
        $category = $id ? $this->category_model->find($id) : NULL;
        if ($id && !$category) {
            show_404();
        }
        $this->_render('admin/category_form', [
            'page_title' => $id ? 'Edit Category' : 'New Category',
            'category'   => $category,
        ]);
    }

    public function category_save()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|max_length[100]');
        $this->form_validation->set_rules('sort_order', 'Sort Order', 'integer');

        $id = (int) $this->input->post('id');

        if (!$this->form_validation->run()) {
            return $this->category_form($id ?: NULL);
        }

        $data = [
            'name'       => $this->input->post('name'),
            'sort_order' => (int) $this->input->post('sort_order'),
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $upload_path = FCPATH . 'assets/img/categories/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, TRUE);
            }
            $this->load->library('upload', [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|gif|webp',
                'max_size'      => 2048,
                'file_name'     => 'cat-' . uniqid(),
            ]);
            if ($this->upload->do_upload('image')) {
                $info = $this->upload->data();
                $data['image'] = 'assets/img/categories/' . $info['file_name'];

                // Delete old image on edit
                if ($id) {
                    $old = $this->category_model->find($id);
                    if ($old && !empty($old->image) && file_exists(FCPATH . $old->image)) {
                        @unlink(FCPATH . $old->image);
                    }
                }
            } else {
                $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                return redirect('admin/categories' . ($id ? '/edit/' . $id : '/new'));
            }
        }

        if ($id) {
            $this->category_model->update($id, $data);
            $this->session->set_flashdata('success', 'Category updated.');
        } else {
            $this->category_model->create($data);
            $this->session->set_flashdata('success', 'Category created.');
        }

        redirect('admin/categories');
    }

    public function category_delete($id)
    {
        $this->category_model->delete($id);
        $this->session->set_flashdata('success', 'Category deleted.');
        redirect('admin/categories');
    }
```

- [ ] **Step 3: Commit**

```bash
git add application/controllers/Admin.php
git commit -m "feat: add category CRUD methods to Admin controller"
```

---

### Task 4: Create admin categories list view

**Files:**
- Create: `application/views/admin/categories.php`

**Interfaces:**
- Consumes: `$categories` — array of objects with `id, name, slug, image, sort_order`

- [ ] **Step 1: Create the view**

Create `application/views/admin/categories.php`:

```php
<div class="dl-page-header">
    <h2>Manage Categories</h2>
    <a href="<?= base_url('admin/categories/new') ?>" class="btn btn-primary">+ New Category</a>
</div>

<?php if (empty($categories)): ?>
<div class="dl-empty-state">
    <div class="dl-empty-state-icon">🗂️</div>
    <h3>No categories yet</h3>
    <p>Add your first category to organise products on the shop.</p>
    <a href="<?= base_url('admin/categories/new') ?>" class="btn btn-primary">Add Category</a>
</div>
<?php else: ?>
<div class="table-responsive">
<table class="dl-orders-table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Sort Order</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $cat): ?>
    <tr>
        <td>
            <?php if (!empty($cat->image)): ?>
                <img src="<?= base_url($cat->image) ?>" alt="<?= htmlspecialchars($cat->name) ?>"
                     style="width:48px;height:48px;object-fit:contain;border-radius:6px;background:var(--bg-subtle);">
            <?php else: ?>
                <span style="font-size:1.8rem;line-height:1;">🗂️</span>
            <?php endif; ?>
        </td>
        <td style="font-weight:800;"><?= htmlspecialchars($cat->name) ?></td>
        <td style="color:var(--text-muted);font-size:0.85rem;font-family:ui-monospace,'Cascadia Code',monospace;"><?= htmlspecialchars($cat->slug) ?></td>
        <td style="color:var(--text-muted);"><?= (int) $cat->sort_order ?></td>
        <td style="display:flex;gap:8px;align-items:center;">
            <a href="<?= base_url('admin/categories/edit/' . $cat->id) ?>" class="dl-action-btn dl-action-btn--edit">Edit</a>
            <?php echo form_open('admin/categories/delete/' . $cat->id, ['style' => 'display:inline;']); ?>
            <?= csrf_field() ?>
            <button type="submit" class="dl-action-btn dl-action-btn--danger"
                    onclick="return confirm('Delete <?= htmlspecialchars(addslashes($cat->name)) ?>? This cannot be undone.')">
                Delete
            </button>
            <?php echo form_close(); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
```

- [ ] **Step 2: Commit**

```bash
git add application/views/admin/categories.php
git commit -m "feat: add admin categories list view"
```

---

### Task 5: Create admin category form view

**Files:**
- Create: `application/views/admin/category_form.php`

**Interfaces:**
- Consumes: `$category` — stdObject or NULL (NULL = create mode)

- [ ] **Step 1: Create the view**

Create `application/views/admin/category_form.php`:

```php
<div class="dl-page-header">
    <h2><?= $category ? 'Edit Category' : 'New Category' ?></h2>
    <a href="<?= base_url('admin/categories') ?>" class="dl-back-link">All Categories</a>
</div>

<?php echo form_open_multipart('admin/categories/save'); ?>
<?= csrf_field() ?>
<?php if ($category): ?>
    <input type="hidden" name="id" value="<?= $category->id ?>">
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="dl-form-card">
            <div class="dl-form-card-header">Category Details</div>
            <div class="dl-form-card-body">
                <div class="row g-4">
                    <div class="col-sm-8">
                        <label class="form-label" style="font-weight:800;">Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars(set_value('name', $category ? $category->name : '')) ?>"
                               placeholder="e.g. Toys &amp; Games" required maxlength="100">
                        <?php if (form_error('name')): ?>
                        <p style="color:var(--danger);font-size:0.8rem;font-weight:700;margin-top:4px;"><?= form_error('name') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label" style="font-weight:800;">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" min="0"
                               value="<?= set_value('sort_order', $category ? $category->sort_order : '0') ?>">
                        <p class="dl-form-hint">Lower numbers appear first.</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-weight:800;">Image</label>
                        <?php if ($category && !empty($category->image)): ?>
                        <div style="margin-bottom:12px;">
                            <img src="<?= base_url($category->image) ?>" alt="Current image"
                                 style="width:80px;height:80px;object-fit:contain;border-radius:8px;background:var(--bg-subtle);padding:6px;border:1px solid var(--border);">
                            <p class="dl-form-hint" style="margin-top:4px;">Upload a new image to replace the current one.</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <p class="dl-form-hint">JPG, PNG, GIF, or WebP. Max 2 MB. Recommended: square, at least 200×200 px.</p>
                    </div>
                </div>
            </div>
            <div class="dl-form-card-footer">
                <a href="<?= base_url('admin/categories') ?>" class="dl-action-btn dl-action-btn--edit">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <?= $category ? 'Save Changes' : 'Create Category' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>
```

- [ ] **Step 2: Commit**

```bash
git add application/views/admin/category_form.php
git commit -m "feat: add admin category form view"
```

---

### Task 6: Add Categories link to admin nav

**Files:**
- Modify: `application/views/partials/nav.php`

- [ ] **Step 1: Add the nav link**

In `nav.php`, find the admin subnav block. After:
```php
<a href="<?= base_url('admin/coupons') ?>" class="<?= dl_nav_active('admin/coupons', $current_uri) ?>">Coupons</a>
```

Insert:
```php
<a href="<?= base_url('admin/categories') ?>" class="<?= dl_nav_active('admin/categories', $current_uri) ?>">Categories</a>
```

- [ ] **Step 2: Commit**

```bash
git add application/views/partials/nav.php
git commit -m "feat: add Categories link to admin subnav"
```

---

### Task 7: Create categories upload directory

**Files:**
- Create: `assets/img/categories/.gitkeep`

The `category_save()` method creates the directory at runtime if missing, but adding a `.gitkeep` ensures the directory exists in the repo so the first upload doesn't require the runtime mkdir.

- [ ] **Step 1: Create the directory and gitkeep**

```bash
mkdir -p assets/img/categories
touch assets/img/categories/.gitkeep
```

- [ ] **Step 2: Commit**

```bash
git add assets/img/categories/.gitkeep
git commit -m "chore: add categories image upload directory"
```

---

### Task 8: Smoke test

- [ ] **Step 1: Start the local server and log in as admin**

Navigate to `http://localhost/ci3-ecomm/admin/categories`. Confirm the empty state renders with a "+ New Category" button.

- [ ] **Step 2: Create a category without an image**

Click "+ New Category". Fill in Name = "Toys & Games", Sort Order = 1. Submit. Confirm redirect to list, success flash, and the row appears with the 🗂️ fallback icon and slug `toys-games`.

- [ ] **Step 3: Create a category with an image**

Click "+ New Category". Fill in Name = "Books", upload a valid PNG under 2 MB. Submit. Confirm the image thumbnail appears in the list.

- [ ] **Step 4: Edit the category**

Click Edit on "Toys & Games". Change name to "Toys & Learning". Submit. Confirm slug updates to `toys-learning` in the list.

- [ ] **Step 5: Verify shop front-end**

Navigate to `http://localhost/ci3-ecomm/` (home page). Confirm the "Top Categories" section shows "Books" with its uploaded image, and "Toys & Learning" with the fallback emoji.

- [ ] **Step 6: Delete a category**

Click Delete on one category. Confirm it disappears from the list and from the home page categories strip.

- [ ] **Step 7: Check the admin subnav**

Confirm "Categories" appears in the admin navigation bar and highlights correctly when on `/admin/categories`.
