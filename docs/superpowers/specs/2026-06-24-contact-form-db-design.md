# Contact Form — Database Storage

**Date:** 2026-06-24  
**Status:** Approved

## Overview

Replace the current `mailto:` contact form with a real server-side form that stores submissions in the database and shows a flash toast on success/failure.

## Database

New table `contact_messages` added to `db/schema.sql`:

| Column       | Type              | Notes                        |
|--------------|-------------------|------------------------------|
| id           | INT UNSIGNED PK   | Auto-increment               |
| name         | VARCHAR(150)      | Required                     |
| email        | VARCHAR(191)      | Required                     |
| subject      | VARCHAR(100)      | Defaults to "General Enquiry"|
| message      | TEXT              | Required                     |
| ip_address   | VARCHAR(45)       | Captured server-side         |
| created_at   | DATETIME          | DEFAULT CURRENT_TIMESTAMP    |

## Model

`application/models/Contact_model.php` — single method `save(array $data): bool`.  
Inserts into `contact_messages`, returns true/false.

## Controller

`Pages::contact_submit()` — POST only.

1. Load `form_validation` and `Contact_model`
2. Validate: `name` (required, max 150), `email` (required, valid_email), `message` (required, max 2000)
3. On failure: set flash `error` with validation errors, redirect to `/contact`
4. On success: call `Contact_model::save()`, set flash `success` "Thanks! We'll get back to you within 1–2 business days.", redirect to `/contact`
5. On DB error: set flash `error` "Something went wrong. Please try again.", redirect to `/contact`

Route: `$route['contact/submit'] = 'pages/contact_submit';`

## View

`application/views/pages/contact.php`:

- Convert `<form>` to standard POST: `action="<?= site_url('contact/submit') ?>"`, `method="POST"`, add CSRF field
- Remove the `mailto` JavaScript block
- At top of view, render flash toast if `$this->session->flashdata('success')` or `flashdata('error')` is set — reuse existing app toast markup/classes

## Validation Rules

| Field   | Rules                              |
|---------|------------------------------------|
| name    | required, max_length[150]          |
| email   | required, valid_email              |
| message | required, max_length[2000]         |
| subject | none (has safe default via select) |
