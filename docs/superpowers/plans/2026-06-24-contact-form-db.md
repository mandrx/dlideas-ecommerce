# Contact Form — Database Storage Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Store contact form submissions in the database and show a flash toast on success/failure.

**Architecture:** Standard CI3 form POST flow — form submits to a new `Pages::contact_submit()` endpoint, validated via `form_validation`, saved via `Contact_model`, then redirects back to `/contact` with a flash message rendered by the existing `partials/flash_messages.php` partial.

**Tech Stack:** CodeIgniter 3, MySQL, PHP, CI3 `form_validation`, CI3 sessions/flashdata.

## Global Constraints

- CI3 conventions: models extend `MY_Model`, use `$this->db` query builder
- Flash keys used app-wide: `success` (green alert) and `error` (red alert)
- `partials/flash_messages.php` is already included in `layouts/main.php` — no layout changes needed
- CSRF protection is already enabled in CI3 config — forms must include `<?= $this->security->get_csrf_field() ?>`
- All user output must be passed through `htmlspecialchars()`

---

### Task 1: Add `contact_messages` table to schema and database

**Files:**
- Modify: `db/schema.sql`

**Interfaces:**
- Produces: table `contact_messages(id, name, email, subject, message, ip_address, created_at)`

- [ ] **Step 1: Add table DDL to schema.sql**

Append to the end of `db/schema.sql` (before any final comments):

```sql
-- Contact messages
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `subject` varchar(100) NOT NULL DEFAULT 'General Enquiry',
  `message` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 2: Create the table in the running database**

Run in MySQL (or via your MySQL client connected to `ci3_ecomm`):

```sql
USE ci3_ecomm;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `subject` varchar(100) NOT NULL DEFAULT 'General Enquiry',
  `message` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Verify: `SHOW TABLES LIKE 'contact_messages';` — should return 1 row.

- [ ] **Step 3: Commit**

```bash
git add db/schema.sql
git commit -m "feat: add contact_messages table to schema"
```

---

### Task 2: Create `Contact_model`

**Files:**
- Create: `application/models/Contact_model.php`

**Interfaces:**
- Consumes: table `contact_messages` from Task 1
- Produces: `Contact_model::save(array $data): bool` — inserts a row, returns true on success

- [ ] **Step 1: Create the model file**

`application/models/Contact_model.php`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contact_model extends MY_Model
{
    protected $table   = 'contact_messages';
    protected $primary = 'id';

    public function save(array $data)
    {
        return $this->db->insert($this->table, $data);
    }
}
```

- [ ] **Step 2: Verify CI3 can load the model**

Open a browser to any page on `http://localhost:8080` (to boot CI3), then confirm no PHP fatal errors in the CI3 log at `application/logs/`.

- [ ] **Step 3: Commit**

```bash
git add application/models/Contact_model.php
git commit -m "feat: add Contact_model"
```

---

### Task 3: Add route and `contact_submit` controller method

**Files:**
- Modify: `application/config/routes.php`
- Modify: `application/controllers/Pages.php`

**Interfaces:**
- Consumes: `Contact_model::save(array $data): bool` from Task 2
- Produces: POST endpoint at `/contact/submit` that validates, saves, and redirects

- [ ] **Step 1: Add route**

In `application/config/routes.php`, add after the existing pages routes (look for the comment block near the bottom or after `$route['our-story']`):

```php
$route['contact/submit'] = 'pages/contact_submit';
```

- [ ] **Step 2: Add `contact_submit()` method to Pages controller**

In `application/controllers/Pages.php`, add this method before the closing `}`:

```php
public function contact_submit()
{
    if ($this->input->server('REQUEST_METHOD') !== 'POST') {
        redirect('contact');
    }

    $this->load->library('form_validation');
    $this->form_validation->set_rules('name',    'Name',    'required|max_length[150]');
    $this->form_validation->set_rules('email',   'Email',   'required|valid_email');
    $this->form_validation->set_rules('message', 'Message', 'required|max_length[2000]');

    if (!$this->form_validation->run()) {
        $this->session->set_flashdata('error', validation_errors(' ', ' '));
        redirect('contact');
        return;
    }

    $this->load->model('Contact_model');
    $saved = $this->Contact_model->save([
        'name'       => $this->input->post('name'),
        'email'      => $this->input->post('email'),
        'subject'    => $this->input->post('subject') ?: 'General Enquiry',
        'message'    => $this->input->post('message'),
        'ip_address' => $this->input->ip_address(),
    ]);

    if ($saved) {
        $this->session->set_flashdata('success', "Thanks! We'll get back to you within 1–2 business days.");
    } else {
        $this->session->set_flashdata('error', 'Something went wrong. Please try again.');
    }

    redirect('contact');
}
```

- [ ] **Step 3: Commit**

```bash
git add application/config/routes.php application/controllers/Pages.php
git commit -m "feat: add contact_submit endpoint"
```

---

### Task 4: Update contact view — standard POST form

**Files:**
- Modify: `application/views/pages/contact.php`

**Interfaces:**
- Consumes: POST endpoint `/contact/submit` from Task 3
- Consumes: flash messages rendered automatically by `partials/flash_messages.php` (already in `layouts/main.php`)

- [ ] **Step 1: Replace the `<form>` tag and button**

Find this line in `application/views/pages/contact.php`:

```html
<form class="dl-contact-form" id="contactForm" novalidate>
```

Replace with:

```html
<form class="dl-contact-form" id="contactForm" method="POST" action="<?= site_url('contact/submit') ?>">
    <?= $this->security->get_csrf_field() ?>
```

- [ ] **Step 2: Replace the submit button text**

Find:

```html
                    Open in Email App
```

Replace with:

```html
                    Send Message
```

- [ ] **Step 3: Remove the `<script>` block at the bottom of the file**

Delete the entire block from `<script>` to `</script>` (lines 114–131 in the original file):

```html
<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    ...
});
</script>
```

- [ ] **Step 4: Remove the mailto note paragraph**

Find and delete:

```html
                <p class="dl-contact-form-note">
                    This sends via your email client. Direct email: <a href="mailto:mandrx@gmail.com">mandrx@gmail.com</a>
                </p>
```

- [ ] **Step 5: Test the golden path**

1. Visit `http://localhost:8080/contact`
2. Fill in name, email, subject, message — click **Send Message**
3. Should redirect back to `/contact` with a green success toast
4. Run in MySQL: `SELECT * FROM contact_messages ORDER BY id DESC LIMIT 1;` — confirm the row exists with correct data

- [ ] **Step 6: Test validation**

1. Submit the form with empty name — expect red error toast listing "The Name field is required."
2. Submit with an invalid email — expect red error toast listing "The Email field must contain a valid email address."

- [ ] **Step 7: Commit**

```bash
git add application/views/pages/contact.php
git commit -m "feat: convert contact form to standard POST with flash toast"
```
