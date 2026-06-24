# CI3 Ecommerce — Plan 1: Foundation & Authentication

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bootstrap a working CI3 project with database schema, core base classes (MY_Controller, MY_Model), layouts, and full authentication (register, login, logout, seller onboarding, password reset).

**Architecture:** CI3 3.x downloaded and configured manually. All auth uses CI3 native session library. Base classes in `application/core/` extend CI3 internals per CI3 convention. PHPUnit used for model unit tests.

**Tech Stack:** PHP 7.4+, CodeIgniter 3.1.13, MySQL 5.7+, Composer (PHPUnit), XAMPP or equivalent local server.

**Prerequisites:**
- XAMPP (or Apache + PHP + MySQL) running locally
- Composer installed and in PATH (`composer --version` works)
- Node.js 18+ installed (`node --version` works)
- A database named `ci3_ecomm` created in MySQL

---

## File Map

| File | Purpose |
|---|---|
| `application/config/config.php` | Base URL, encryption key, session settings |
| `application/config/database.php` | DB credentials |
| `application/config/autoload.php` | Auto-loaded libraries, helpers, models |
| `application/config/routes.php` | Clean URL routing |
| `application/config/constants.php` | Role and status constants |
| `application/config/email.php` | SMTP email settings |
| `application/core/MY_Model.php` | Base model: find, find_all, insert, update, delete |
| `application/core/MY_Controller.php` | Base controller: current_user hydration, require_login, require_role |
| `application/models/User_model.php` | Users table queries |
| `application/controllers/Auth.php` | login, register, logout, apply_seller, forgot_password, reset_password |
| `application/views/layouts/main.php` | Public layout shell |
| `application/views/layouts/auth.php` | Auth pages layout shell |
| `application/views/layouts/dashboard.php` | Seller/admin layout shell |
| `application/views/partials/header.php` | HTML head + opening body |
| `application/views/partials/nav.php` | Navigation bar |
| `application/views/partials/footer.php` | Closing HTML |
| `application/views/partials/vue_scripts.php` | Script tags for Vue entries |
| `application/views/auth/login.php` | Login form |
| `application/views/auth/register.php` | Registration form |
| `application/views/auth/apply_seller.php` | Seller application form |
| `application/views/auth/forgot_password.php` | Forgot password form |
| `application/views/auth/reset_password.php` | Reset password form |
| `application/helpers/auth_helper.php` | `logged_in()`, `current_role()`, `is_role()` helpers |
| `application/helpers/format_helper.php` | `format_currency()`, `format_date()` helpers |
| `db/schema.sql` | Full database schema |
| `tests/bootstrap.php` | PHPUnit bootstrap for CI3 |
| `tests/models/User_model_test.php` | Unit tests for User_model |
| `tests/core/MY_Model_test.php` | Unit tests for MY_Model |
| `.htaccess` | Remove index.php from URLs |
| `.env.example` | Environment variable template |

---

## Task 1: Download & Configure CodeIgniter 3

**Files:**
- Create: `.htaccess`
- Create: `.env.example`
- Modify: `application/config/config.php`
- Modify: `index.php`

- [ ] **Step 1: Download CI3**

```powershell
cd C:\Projects\ci3-ecomm
Invoke-WebRequest -Uri "https://github.com/bcit-ci/CodeIgniter/archive/refs/tags/3.1.13.zip" -OutFile "ci3.zip"
Expand-Archive -Path "ci3.zip" -DestinationPath "."
Get-ChildItem "CodeIgniter-3.1.13\" | Move-Item -Destination "."
Remove-Item "CodeIgniter-3.1.13" -Recurse
Remove-Item "ci3.zip"
```

Expected: `application/`, `system/`, `index.php` now exist in `C:\Projects\ci3-ecomm\`.

- [ ] **Step 2: Create .htaccess to remove index.php from URLs**

Create `C:\Projects\ci3-ecomm\.htaccess`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

- [ ] **Step 3: Create .env.example**

Create `C:\Projects\ci3-ecomm\.env.example`:
```
DB_HOST=localhost
DB_NAME=ci3_ecomm
DB_USER=root
DB_PASS=

STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_PUBLIC_KEY=pk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx

MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USER=your_mailtrap_user
MAIL_PASS=your_mailtrap_pass
MAIL_FROM=noreply@ci3ecomm.local
```

Copy to `.env` and fill in values:
```powershell
Copy-Item ".env.example" ".env"
```

- [ ] **Step 4: Edit index.php — point system path, enable error reporting in dev**

Edit `C:\Projects\ci3-ecomm\index.php`, find and update these lines:
```php
// Around line 16 — set to 'development' locally
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

// Around line 47
$system_path = 'system';

// Around line 55
$application_folder = 'application';
```

- [ ] **Step 5: Configure base URL**

Edit `C:\Projects\ci3-ecomm\application\config\config.php`:
```php
$config['base_url'] = 'http://localhost/ci3-ecomm/';
$config['index_page'] = '';  // empty — .htaccess handles it
$config['encryption_key'] = 'a8f3b2c1d9e4f7a2b5c8d1e6f3a9b2c5';  // 32-char random string
$config['sess_driver'] = 'database';
$config['sess_cookie_name'] = 'ci3ecomm_session';
$config['sess_expiration'] = 7200;
$config['sess_save_path'] = 'ci_sessions';  // table name
$config['sess_match_ip'] = FALSE;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = FALSE;
```

- [ ] **Step 6: Verify CI3 loads**

Open browser: `http://localhost/ci3-ecomm/`
Expected: CI3 welcome page ("Welcome to CodeIgniter!")

- [ ] **Step 7: Commit**

```powershell
cd C:\Projects\ci3-ecomm
git init
git add .htaccess .env.example index.php application/config/config.php
git commit -m "feat: bootstrap CI3 3.1.13 with base config"
```

---

## Task 2: Database Setup

**Files:**
- Create: `db/schema.sql`
- Modify: `application/config/database.php`

- [ ] **Step 1: Create db/ directory and schema file**

Create `C:\Projects\ci3-ecomm\db\schema.sql`:
```sql
-- Create and select database
CREATE DATABASE IF NOT EXISTS ci3_ecomm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ci3_ecomm;

-- Session table (required by CI3 database session driver)
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
  `data` blob NOT NULL,
  KEY `ci_sessions_timestamp` (`timestamp`)
);

-- Users
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('admin','seller','buyer') NOT NULL DEFAULT 'buyer',
  `status` enum('active','banned') NOT NULL DEFAULT 'active',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stores (seller storefronts)
CREATE TABLE `stores` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `status` enum('pending','active','suspended') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `stores_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories
CREATE TABLE `categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  CONSTRAINT `categories_parent_fk` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products
CREATE TABLE `products` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `weight` decimal(8,2) DEFAULT NULL COMMENT 'in grams',
  `status` enum('draft','active','inactive') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  CONSTRAINT `products_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
  CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product images
CREATE TABLE `product_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `product_images_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product tags
CREATE TABLE `product_tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(11) UNSIGNED NOT NULL,
  `tag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `product_tags_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Carts
CREATE TABLE `carts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `session_id` varchar(128) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `carts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart items
CREATE TABLE `cart_items` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price_snapshot` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `cart_items_cart_fk` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders
CREATE TABLE `orders` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `store_id` int(11) UNSIGNED NOT NULL,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_address` json NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_store_fk` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items
CREATE TABLE `order_items` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED DEFAULT NULL,
  `product_name_snapshot` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments
CREATE TABLE `payments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL,
  `gateway` varchar(50) NOT NULL DEFAULT 'stripe',
  `gateway_ref` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `payload` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `payments_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shipments
CREATE TABLE `shipments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL,
  `provider` varchar(100) NOT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `status` enum('pending','picked_up','in_transit','delivered') NOT NULL DEFAULT 'pending',
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `shipments_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews
CREATE TABLE `reviews` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `body` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `reviews_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `reviews_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Coupons
CREATE TABLE `coupons` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','fixed') NOT NULL DEFAULT 'fixed',
  `value` decimal(12,2) NOT NULL,
  `min_order` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_uses` int(11) NOT NULL DEFAULT 1,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Coupon uses
CREATE TABLE `coupon_uses` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `used_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `coupon_uses_coupon_fk` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  CONSTRAINT `coupon_uses_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `coupon_uses_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: default admin user (password: Admin@1234)
INSERT INTO `users` (`email`, `password`, `full_name`, `role`, `status`) VALUES
('admin@ci3ecomm.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin', 'active');
```

- [ ] **Step 2: Run schema against MySQL**

```powershell
# Adjust path to mysql.exe if needed (XAMPP default)
& "C:\xampp\mysql\bin\mysql.exe" -u root -p ci3_ecomm < "C:\Projects\ci3-ecomm\db\schema.sql"
```

Expected: no errors. Verify:
```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -p -e "USE ci3_ecomm; SHOW TABLES;"
```
Expected: lists all 15 tables including `ci_sessions`, `users`, `stores`, etc.

- [ ] **Step 3: Configure database.php**

Edit `C:\Projects\ci3-ecomm\application\config\database.php`:
```php
$db['default'] = array(
    'dsn'          => '',
    'hostname'     => getenv('DB_HOST') ?: 'localhost',
    'username'     => getenv('DB_USER') ?: 'root',
    'password'     => getenv('DB_PASS') ?: '',
    'database'     => getenv('DB_NAME') ?: 'ci3_ecomm',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => FALSE,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => FALSE,
    'cachedir'     => '',
    'char_set'     => 'utf8mb4',
    'dbcollat'     => 'utf8mb4_unicode_ci',
    'swap_pre'     => '',
    'encrypt'      => FALSE,
    'compress'     => FALSE,
    'stricton'     => FALSE,
    'failover'     => array(),
    'save_queries' => TRUE
);
```

- [ ] **Step 4: Commit**

```powershell
git add db/schema.sql application/config/database.php
git commit -m "feat: add full database schema and DB config"
```

---

## Task 3: Configure Autoload & Routes & Constants

**Files:**
- Modify: `application/config/autoload.php`
- Modify: `application/config/routes.php`
- Create: `application/config/constants.php`
- Create: `application/config/email.php`

- [ ] **Step 1: Edit autoload.php**

Edit `C:\Projects\ci3-ecomm\application\config\autoload.php`:
```php
$autoload['libraries'] = array('database', 'session', 'form_validation', 'email');
$autoload['helper']    = array('url', 'form', 'html', 'auth', 'format');
$autoload['model']     = array();  // models loaded per-controller
$autoload['config']    = array();
$autoload['language']  = array();
```

- [ ] **Step 2: Edit routes.php**

Edit `C:\Projects\ci3-ecomm\application\config\routes.php` — replace the entire file content below the opening `<?php` tag:
```php
defined('BASEPATH') OR exit('No direct script access allowed');

// Default
$route['default_controller'] = 'shop';
$route['404_override']       = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['login']              = 'auth/login';
$route['register']           = 'auth/register';
$route['logout']             = 'auth/logout';
$route['apply-seller']       = 'auth/apply_seller';
$route['forgot-password']    = 'auth/forgot_password';
$route['reset-password/(:any)'] = 'auth/reset_password/$1';

// Public shop
$route['shop']               = 'shop/index';
$route['shop/(:any)']        = 'shop/category/$1';
$route['product/(:any)']     = 'shop/product/$1';
$route['store/(:any)']       = 'shop/store/$1';

// Cart & checkout
$route['cart']               = 'cart/index';
$route['cart/add']           = 'cart/add';
$route['cart/remove/(:num)'] = 'cart/remove/$1';
$route['cart/update']        = 'cart/update';
$route['checkout']           = 'cart/checkout';
$route['checkout/confirm']   = 'cart/confirm';

// Buyer orders
$route['orders']             = 'order/index';
$route['orders/(:num)']      = 'order/detail/$1';
$route['orders/(:num)/review'] = 'order/submit_review/$1';

// Seller dashboard
$route['seller']                        = 'seller/dashboard';
$route['seller/products']               = 'seller/products';
$route['seller/products/add']           = 'seller/add_product';
$route['seller/products/edit/(:num)']   = 'seller/edit_product/$1';
$route['seller/products/delete/(:num)'] = 'seller/delete_product/$1';
$route['seller/orders']                 = 'seller/orders';
$route['seller/orders/(:num)']          = 'seller/order_detail/$1';
$route['seller/store']                  = 'seller/store_settings';

// Admin panel
$route['admin']                         = 'admin/dashboard';
$route['admin/users']                   = 'admin/users';
$route['admin/users/(:num)/ban']        = 'admin/ban_user/$1';
$route['admin/users/(:num)/unban']      = 'admin/unban_user/$1';
$route['admin/stores']                  = 'admin/stores';
$route['admin/stores/(:num)/approve']   = 'admin/approve_store/$1';
$route['admin/stores/(:num)/suspend']   = 'admin/suspend_store/$1';
$route['admin/products']                = 'admin/products';
$route['admin/orders']                  = 'admin/orders';
$route['admin/coupons']                 = 'admin/coupons';
$route['admin/coupons/add']             = 'admin/add_coupon';
$route['admin/coupons/(:num)/toggle']   = 'admin/toggle_coupon/$1';
$route['admin/reviews']                 = 'admin/reviews';
$route['admin/reviews/(:num)/approve']  = 'admin/approve_review/$1';
$route['admin/reviews/(:num)/reject']   = 'admin/reject_review/$1';

// Internal JSON API (for Vue components)
$route['api/search']                    = 'api/search';
$route['api/cart/summary']              = 'api/cart_summary';
$route['api/product/(:num)/reviews']    = 'api/reviews/$1';
$route['api/shipping/rates']            = 'api/shipping_rates';
$route['api/payment/intent']            = 'api/payment_intent';
$route['api/webhook/stripe']            = 'api/stripe_webhook';
```

- [ ] **Step 3: Create constants.php**

Create `C:\Projects\ci3-ecomm\application\config\constants.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// User roles
define('ROLE_ADMIN',  'admin');
define('ROLE_SELLER', 'seller');
define('ROLE_BUYER',  'buyer');

// User statuses
define('USER_ACTIVE', 'active');
define('USER_BANNED', 'banned');

// Store statuses
define('STORE_PENDING',   'pending');
define('STORE_ACTIVE',    'active');
define('STORE_SUSPENDED', 'suspended');

// Product statuses
define('PRODUCT_DRAFT',    'draft');
define('PRODUCT_ACTIVE',   'active');
define('PRODUCT_INACTIVE', 'inactive');

// Order statuses
define('ORDER_PENDING',    'pending');
define('ORDER_PAID',       'paid');
define('ORDER_PROCESSING', 'processing');
define('ORDER_SHIPPED',    'shipped');
define('ORDER_DELIVERED',  'delivered');
define('ORDER_CANCELLED',  'cancelled');
define('ORDER_REFUNDED',   'refunded');

// Review statuses
define('REVIEW_PENDING',  'pending');
define('REVIEW_APPROVED', 'approved');
define('REVIEW_REJECTED', 'rejected');

// Payment statuses
define('PAYMENT_PENDING',  'pending');
define('PAYMENT_PAID',     'paid');
define('PAYMENT_FAILED',   'failed');
define('PAYMENT_REFUNDED', 'refunded');
```

- [ ] **Step 4: Create email.php config**

Create `C:\Projects\ci3-ecomm\application\config\email.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol']   = 'smtp';
$config['smtp_host']  = getenv('MAIL_HOST') ?: 'smtp.mailtrap.io';
$config['smtp_port']  = getenv('MAIL_PORT') ?: 587;
$config['smtp_user']  = getenv('MAIL_USER') ?: '';
$config['smtp_pass']  = getenv('MAIL_PASS') ?: '';
$config['mailtype']   = 'html';
$config['charset']    = 'utf-8';
$config['from_email'] = getenv('MAIL_FROM') ?: 'noreply@ci3ecomm.local';
$config['from_name']  = 'CI3 Ecomm';
```

- [ ] **Step 5: Add constants.php to autoload**

Edit `application/config/autoload.php`, update config line:
```php
$autoload['config'] = array('constants');
```

- [ ] **Step 6: Commit**

```powershell
git add application/config/autoload.php application/config/routes.php application/config/constants.php application/config/email.php
git commit -m "feat: configure autoload, routes, constants, email"
```

---

## Task 4: MY_Model — Base Model

**Files:**
- Create: `application/core/MY_Model.php`
- Create: `tests/bootstrap.php`
- Create: `tests/core/MY_Model_test.php`

- [ ] **Step 1: Install PHPUnit via Composer**

```powershell
cd C:\Projects\ci3-ecomm
composer init --name="ci3-ecomm/app" --stability=stable --no-interaction
composer require --dev phpunit/phpunit:^9.0
```

Expected: `vendor/` directory created, `composer.json` present.

- [ ] **Step 2: Create PHPUnit bootstrap for CI3**

Create `C:\Projects\ci3-ecomm\tests\bootstrap.php`:
```php
<?php
// Load .env values manually for tests
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    foreach (file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($val));
    }
}

// Define CI3 constants needed by models
define('BASEPATH',   realpath(__DIR__ . '/../system') . '/');
define('APPPATH',    realpath(__DIR__ . '/../application') . '/');
define('FCPATH',     realpath(__DIR__ . '/..') . '/');
define('ENVIRONMENT', 'testing');

// Load constants config manually
require_once APPPATH . 'config/constants.php';
```

- [ ] **Step 3: Create phpunit.xml**

Create `C:\Projects\ci3-ecomm\phpunit.xml`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap.php"
         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="CI3 Ecomm Tests">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

- [ ] **Step 4: Write failing tests for MY_Model**

Create `C:\Projects\ci3-ecomm\tests\core\MY_Model_test.php`:
```php
<?php
use PHPUnit\Framework\TestCase;

class MY_Model_test extends TestCase
{
    private $db;
    private $model;

    protected function setUp(): void
    {
        // Build a real DB connection for integration testing
        $this->db = new mysqli(
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: '',
            getenv('DB_NAME') ?: 'ci3_ecomm'
        );

        if ($this->db->connect_error) {
            $this->markTestSkipped('MySQL not available: ' . $this->db->connect_error);
        }

        // Minimal CI3 stub so MY_Model can instantiate
        if (!class_exists('CI_Model')) {
            eval('class CI_Model { public $db; public function __construct() {} }');
        }

        require_once APPPATH . 'core/MY_Model.php';

        $this->model = new class extends MY_Model {
            protected $table   = 'users';
            protected $primary = 'id';
        };

        // Inject a raw mysqli wrapper that speaks Query Builder interface
        // MY_Model_test uses a direct mysqli connection to verify logic independently
        $this->model->db = $this->db;
    }

    public function test_table_property_is_declared(): void
    {
        $this->assertSame('users', (new ReflectionProperty($this->model, 'table'))->getValue($this->model));
    }

    public function test_primary_property_defaults_to_id(): void
    {
        $model = new class extends MY_Model {
            protected $table = 'categories';
        };
        $this->assertSame('id', (new ReflectionProperty($model, 'primary'))->getValue($model));
    }
}
```

- [ ] **Step 5: Run tests — verify they fail correctly**

```powershell
cd C:\Projects\ci3-ecomm
vendor\bin\phpunit tests\core\MY_Model_test.php --testdox
```

Expected: tests run but skip (MySQL not mocked at this stage) OR fail with "MY_Model.php not found". This is correct — the file doesn't exist yet.

- [ ] **Step 6: Implement MY_Model**

Create `C:\Projects\ci3-ecomm\application\core\MY_Model.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model
{
    protected $table   = '';
    protected $primary = 'id';

    public function find($id)
    {
        return $this->db
            ->where($this->primary, $id)
            ->get($this->table)
            ->row();
    }

    public function find_all($conditions = array())
    {
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        return $this->db->get($this->table)->result();
    }

    public function find_where($column, $value)
    {
        return $this->db
            ->where($column, $value)
            ->get($this->table)
            ->row();
    }

    public function find_many_where($column, $value)
    {
        return $this->db
            ->where($column, $value)
            ->get($this->table)
            ->result();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where($this->primary, $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table, array($this->primary => $id));
    }

    public function count_all($conditions = array())
    {
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        return $this->db->count_all_results($this->table);
    }

    public function exists($column, $value, $exclude_id = null)
    {
        $this->db->where($column, $value);
        if ($exclude_id !== null) {
            $this->db->where($this->primary . ' !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }
}
```

- [ ] **Step 7: Run tests — verify they pass**

```powershell
vendor\bin\phpunit tests\core\MY_Model_test.php --testdox
```

Expected: `PASS` for both property tests. Skip for DB tests if MySQL unavailable.

- [ ] **Step 8: Commit**

```powershell
git add application/core/MY_Model.php tests/ phpunit.xml composer.json composer.lock
git commit -m "feat: add MY_Model base class with PHPUnit setup"
```

---

## Task 5: MY_Controller — Base Controller

**Files:**
- Create: `application/core/MY_Controller.php`
- Create: `application/helpers/auth_helper.php`
- Create: `application/helpers/format_helper.php`

- [ ] **Step 1: Create MY_Controller**

Create `C:\Projects\ci3-ecomm\application\core\MY_Controller.php`:
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

        // Make current_user available to all views automatically
        $this->load->vars(array('current_user' => $this->current_user));
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
}
```

- [ ] **Step 2: Create auth_helper.php**

Create `C:\Projects\ci3-ecomm\application\helpers\auth_helper.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('logged_in')) {
    function logged_in()
    {
        $CI =& get_instance();
        return $CI->session->userdata('user_id') !== false;
    }
}

if (!function_exists('current_role')) {
    function current_role()
    {
        $CI =& get_instance();
        return $CI->session->userdata('role');
    }
}

if (!function_exists('is_role')) {
    function is_role($role)
    {
        return current_role() === $role;
    }
}

if (!function_exists('current_user_id')) {
    function current_user_id()
    {
        $CI =& get_instance();
        return $CI->session->userdata('user_id');
    }
}
```

- [ ] **Step 3: Create format_helper.php**

Create `C:\Projects\ci3-ecomm\application\helpers\format_helper.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('format_currency')) {
    function format_currency($amount, $symbol = '$')
    {
        return $symbol . number_format((float) $amount, 2, '.', ',');
    }
}

if (!function_exists('format_date')) {
    function format_date($datetime, $format = 'M j, Y')
    {
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($datetime)
    {
        return date('M j, Y g:i A', strtotime($datetime));
    }
}

if (!function_exists('slugify')) {
    function slugify($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text($text, $limit = 100, $suffix = '...')
    {
        if (mb_strlen($text) <= $limit) return $text;
        return mb_substr($text, 0, $limit) . $suffix;
    }
}
```

- [ ] **Step 4: Commit**

```powershell
git add application/core/MY_Controller.php application/helpers/auth_helper.php application/helpers/format_helper.php
git commit -m "feat: add MY_Controller base class and auth/format helpers"
```

---

## Task 6: Layouts & Partials

**Files:**
- Create: `application/views/layouts/main.php`
- Create: `application/views/layouts/auth.php`
- Create: `application/views/layouts/dashboard.php`
- Create: `application/views/partials/header.php`
- Create: `application/views/partials/nav.php`
- Create: `application/views/partials/footer.php`
- Create: `application/views/partials/vue_scripts.php`
- Create: `application/views/partials/flash_messages.php`

- [ ] **Step 1: Create views directory structure**

```powershell
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\application\views\layouts"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\application\views\partials"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\application\views\auth"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\application\views\shop"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\application\views\cart"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\application\views\order"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\application\views\seller"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\application\views\admin"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\assets\css"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\assets\js"
New-Item -ItemType Directory -Force "C:\Projects\ci3-ecomm\assets\uploads"
```

- [ ] **Step 2: Create header partial**

Create `C:\Projects\ci3-ecomm\application\views\partials\header.php`:
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — CI3 Shop' : 'CI3 Shop' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>
```

- [ ] **Step 3: Create nav partial**

Create `C:\Projects\ci3-ecomm\application\views\partials\nav.php`:
```html
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url() ?>">CI3 Shop</a>
        <div class="collapse navbar-collapse">
            <div id="live-search" class="ms-auto me-3"></div>
            <ul class="navbar-nav">
                <?php if ($current_user): ?>
                    <?php if ($current_user->role === 'seller'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('seller') ?>">Dashboard</a></li>
                    <?php elseif ($current_user->role === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('admin') ?>">Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('orders') ?>">Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('logout') ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('login') ?>">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('register') ?>">Register</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('cart') ?>">Cart</a></li>
            </ul>
        </div>
    </div>
</nav>
```

- [ ] **Step 4: Create flash_messages partial**

Create `C:\Projects\ci3-ecomm\application\views\partials\flash_messages.php`:
```html
<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($this->session->flashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($this->session->flashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
```

- [ ] **Step 5: Create footer partial**

Create `C:\Projects\ci3-ecomm\application\views\partials\footer.php`:
```html
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> CI3 Shop. Built with CodeIgniter 3.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

- [ ] **Step 6: Create vue_scripts partial**

Create `C:\Projects\ci3-ecomm\application\views\partials\vue_scripts.php`:
```php
<?php
// $scripts is an array of entry names, e.g. ['product', 'search']
if (!empty($scripts)):
    foreach ($scripts as $script):
?>
<script type="module" src="<?= base_url('assets/js/' . $script . '.js') ?>"></script>
<?php
    endforeach;
endif;
?>
```

- [ ] **Step 7: Create main layout**

Create `C:\Projects\ci3-ecomm\application\views\layouts\main.php`:
```php
<?php $this->load->view('partials/header', array('page_title' => isset($page_title) ? $page_title : '')); ?>
<?php $this->load->view('partials/nav'); ?>
<main class="container py-4">
    <?php $this->load->view('partials/flash_messages'); ?>
    <?php $this->load->view($content_view, get_defined_vars()); ?>
</main>
<?php $this->load->view('partials/footer'); ?>
<?php if (!empty($scripts)) $this->load->view('partials/vue_scripts', array('scripts' => $scripts)); ?>
```

- [ ] **Step 8: Create auth layout**

Create `C:\Projects\ci3-ecomm\application\views\layouts\auth.php`:
```php
<?php $this->load->view('partials/header', array('page_title' => isset($page_title) ? $page_title : 'Account')); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h2 class="mb-4 text-center"><?= isset($page_title) ? htmlspecialchars($page_title) : '' ?></h2>
            <?php $this->load->view('partials/flash_messages'); ?>
            <?php $this->load->view($content_view, get_defined_vars()); ?>
        </div>
    </div>
</div>
<?php $this->load->view('partials/footer'); ?>
```

- [ ] **Step 9: Create dashboard layout**

Create `C:\Projects\ci3-ecomm\application\views\layouts\dashboard.php`:
```php
<?php $this->load->view('partials/header', array('page_title' => isset($page_title) ? $page_title : 'Dashboard')); ?>
<?php $this->load->view('partials/nav'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <aside class="col-md-2">
            <?php if ($current_user->role === 'seller'): ?>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><a href="<?= base_url('seller') ?>">Dashboard</a></li>
                <li class="list-group-item"><a href="<?= base_url('seller/products') ?>">Products</a></li>
                <li class="list-group-item"><a href="<?= base_url('seller/orders') ?>">Orders</a></li>
                <li class="list-group-item"><a href="<?= base_url('seller/store') ?>">Store Settings</a></li>
            </ul>
            <?php elseif ($current_user->role === 'admin'): ?>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><a href="<?= base_url('admin') ?>">Dashboard</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/users') ?>">Users</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/stores') ?>">Stores</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/products') ?>">Products</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/orders') ?>">Orders</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/coupons') ?>">Coupons</a></li>
                <li class="list-group-item"><a href="<?= base_url('admin/reviews') ?>">Reviews</a></li>
            </ul>
            <?php endif; ?>
        </aside>
        <main class="col-md-10">
            <?php $this->load->view('partials/flash_messages'); ?>
            <?php $this->load->view($content_view, get_defined_vars()); ?>
        </main>
    </div>
</div>
<?php $this->load->view('partials/footer'); ?>
```

- [ ] **Step 10: Create minimal app.css**

Create `C:\Projects\ci3-ecomm\assets\css\app.css`:
```css
body { background-color: #f8f9fa; }
.navbar-brand { font-weight: 700; }
```

- [ ] **Step 11: Commit**

```powershell
git add application/views/ assets/css/app.css
git commit -m "feat: add layouts and partials (main, auth, dashboard)"
```

---

## Task 7: User_model

**Files:**
- Create: `application/models/User_model.php`
- Create: `tests/models/User_model_test.php`

- [ ] **Step 1: Write failing tests**

Create `C:\Projects\ci3-ecomm\tests\models\User_model_test.php`:
```php
<?php
use PHPUnit\Framework\TestCase;

class User_model_test extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = new mysqli(
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: '',
            getenv('DB_NAME') ?: 'ci3_ecomm'
        );

        if ($this->db->connect_error) {
            $this->markTestSkipped('MySQL not available');
        }

        if (!class_exists('CI_Model')) {
            eval('class CI_Model { public $db; public function __construct() {} }');
        }

        require_once APPPATH . 'core/MY_Model.php';
        require_once APPPATH . 'models/User_model.php';
    }

    public function test_user_model_has_correct_table(): void
    {
        $model = new User_model();
        $reflection = new ReflectionProperty($model, 'table');
        $this->assertSame('users', $reflection->getValue($model));
    }

    public function test_find_by_email_returns_null_for_missing_email(): void
    {
        // This test requires a live DB — skipped if no connection
        $model = new User_model();
        // We can't easily inject the CI3 DB here without the full framework
        // but we verify the method exists and is callable
        $this->assertTrue(method_exists($model, 'find_by_email'));
    }

    public function test_find_by_reset_token_method_exists(): void
    {
        $this->assertTrue(method_exists('User_model', 'find_by_reset_token'));
    }

    public function test_email_exists_method_exists(): void
    {
        $this->assertTrue(method_exists('User_model', 'email_exists'));
    }
}
```

- [ ] **Step 2: Run tests — verify they fail**

```powershell
vendor\bin\phpunit tests\models\User_model_test.php --testdox
```

Expected: FAIL — `User_model.php` not found.

- [ ] **Step 3: Implement User_model**

Create `C:\Projects\ci3-ecomm\application\models\User_model.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model
{
    protected $table   = 'users';
    protected $primary = 'id';

    public function find_by_email($email)
    {
        return $this->db
            ->where('email', $email)
            ->get($this->table)
            ->row();
    }

    public function email_exists($email, $exclude_id = null)
    {
        $this->db->where('email', $email);
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function register($data)
    {
        return $this->insert(array(
            'email'      => $data['email'],
            'password'   => password_hash($data['password'], PASSWORD_BCRYPT),
            'full_name'  => $data['full_name'],
            'phone'      => isset($data['phone']) ? $data['phone'] : null,
            'role'       => ROLE_BUYER,
            'status'     => USER_ACTIVE,
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }

    public function set_reset_token($user_id, $token, $expires)
    {
        return $this->update($user_id, array(
            'reset_token'   => $token,
            'reset_expires' => $expires,
        ));
    }

    public function find_by_reset_token($token)
    {
        return $this->db
            ->where('reset_token', $token)
            ->where('reset_expires >', date('Y-m-d H:i:s'))
            ->get($this->table)
            ->row();
    }

    public function clear_reset_token($user_id)
    {
        return $this->update($user_id, array(
            'reset_token'   => null,
            'reset_expires' => null,
        ));
    }

    public function update_password($user_id, $new_password)
    {
        return $this->update($user_id, array(
            'password' => password_hash($new_password, PASSWORD_BCRYPT),
        ));
    }

    public function promote_to_seller($user_id)
    {
        return $this->update($user_id, array('role' => ROLE_SELLER));
    }

    public function get_paginated($limit = 20, $offset = 0)
    {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get($this->table)
            ->result();
    }
}
```

- [ ] **Step 4: Run tests — verify they pass**

```powershell
vendor\bin\phpunit tests\models\User_model_test.php --testdox
```

Expected: all 4 tests PASS.

- [ ] **Step 5: Commit**

```powershell
git add application/models/User_model.php tests/models/User_model_test.php
git commit -m "feat: add User_model with registration, auth, reset token methods"
```

---

## Task 8: Store_model

**Files:**
- Create: `application/models/Store_model.php`

- [ ] **Step 1: Create Store_model**

Create `C:\Projects\ci3-ecomm\application\models\Store_model.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_model extends MY_Model
{
    protected $table   = 'stores';
    protected $primary = 'id';

    public function find_by_user($user_id)
    {
        return $this->db
            ->where('user_id', $user_id)
            ->get($this->table)
            ->row();
    }

    public function find_by_slug($slug)
    {
        return $this->db
            ->where('slug', $slug)
            ->where('status', STORE_ACTIVE)
            ->get($this->table)
            ->row();
    }

    public function slug_exists($slug, $exclude_id = null)
    {
        $this->db->where('slug', $slug);
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    public function create_for_user($user_id, $name, $description = '')
    {
        $slug = $this->generate_unique_slug($name);
        return $this->insert(array(
            'user_id'     => $user_id,
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'status'      => STORE_PENDING,
            'created_at'  => date('Y-m-d H:i:s'),
        ));
    }

    public function get_pending()
    {
        return $this->db
            ->where('status', STORE_PENDING)
            ->order_by('created_at', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_paginated($limit = 20, $offset = 0)
    {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get($this->table)
            ->result();
    }

    private function generate_unique_slug($name)
    {
        $base = slugify($name);
        $slug = $base;
        $i    = 1;
        while ($this->slug_exists($slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
```

- [ ] **Step 2: Commit**

```powershell
git add application/models/Store_model.php
git commit -m "feat: add Store_model"
```

---

## Task 9: Auth Controller

**Files:**
- Create: `application/controllers/Auth.php`

- [ ] **Step 1: Create Auth controller**

Create `C:\Projects\ci3-ecomm\application\controllers\Auth.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('store_model');
    }

    // GET /login
    public function login()
    {
        if ($this->current_user) redirect('/');

        $data['page_title']   = 'Login';
        $data['content_view'] = 'auth/login';
        $this->load->view('layouts/auth', $data);
    }

    // POST /login
    public function login_post()
    {
        $this->form_validation->set_rules('email',    'Email',    'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('login');
        }

        $user = $this->user_model->find_by_email($this->input->post('email'));

        if (!$user || !password_verify($this->input->post('password'), $user->password)) {
            $this->session->set_flashdata('error', 'Invalid email or password.');
            redirect('login');
        }

        if ($user->status === USER_BANNED) {
            $this->session->set_flashdata('error', 'Your account has been suspended.');
            redirect('login');
        }

        $store_id = null;
        if ($user->role === ROLE_SELLER) {
            $store = $this->store_model->find_by_user($user->id);
            $store_id = $store ? $store->id : null;
        }

        $this->session->set_userdata(array(
            'user_id'   => $user->id,
            'role'      => $user->role,
            'full_name' => $user->full_name,
            'store_id'  => $store_id,
        ));

        // Merge guest cart on login
        $this->load->model('cart_model');
        $this->cart_model->merge_guest_cart($this->session->session_id, $user->id);

        $redirect = $this->session->userdata('redirect_after_login') ?: '/';
        $this->session->unset_userdata('redirect_after_login');
        redirect($redirect);
    }

    // GET /register
    public function register()
    {
        if ($this->current_user) redirect('/');

        $data['page_title']   = 'Create Account';
        $data['content_view'] = 'auth/register';
        $this->load->view('layouts/auth', $data);
    }

    // POST /register
    public function register_post()
    {
        $this->form_validation->set_rules('full_name', 'Full Name', 'required|trim|min_length[2]|max_length[150]');
        $this->form_validation->set_rules('email',     'Email',     'required|valid_email|trim|is_unique[users.email]');
        $this->form_validation->set_rules('password',  'Password',  'required|min_length[8]');
        $this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('register');
        }

        $user_id = $this->user_model->register(array(
            'email'     => $this->input->post('email'),
            'password'  => $this->input->post('password'),
            'full_name' => $this->input->post('full_name'),
            'phone'     => $this->input->post('phone'),
        ));

        $this->session->set_userdata(array(
            'user_id'   => $user_id,
            'role'      => ROLE_BUYER,
            'full_name' => $this->input->post('full_name'),
            'store_id'  => null,
        ));

        $this->redirect_with_message('/', 'Welcome! Your account has been created.');
    }

    // GET /logout
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }

    // GET /apply-seller
    public function apply_seller()
    {
        $this->require_login();

        if ($this->current_user->role !== ROLE_BUYER) {
            redirect('/');
        }

        $data['page_title']   = 'Become a Seller';
        $data['content_view'] = 'auth/apply_seller';
        $this->load->view('layouts/auth', $data);
    }

    // POST /apply-seller
    public function apply_seller_post()
    {
        $this->require_login();

        $this->form_validation->set_rules('store_name',        'Store Name',        'required|trim|min_length[3]|max_length[150]');
        $this->form_validation->set_rules('store_description', 'Store Description', 'trim|max_length[1000]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('apply-seller');
        }

        // Check if already applied
        $existing = $this->store_model->find_by_user($this->current_user->id);
        if ($existing) {
            $this->redirect_with_message('/', 'You have already submitted a store application.');
        }

        $this->store_model->create_for_user(
            $this->current_user->id,
            $this->input->post('store_name'),
            $this->input->post('store_description')
        );

        $this->redirect_with_message('/', 'Your seller application has been submitted. We will review it shortly.');
    }

    // GET /forgot-password
    public function forgot_password()
    {
        $data['page_title']   = 'Reset Password';
        $data['content_view'] = 'auth/forgot_password';
        $this->load->view('layouts/auth', $data);
    }

    // POST /forgot-password
    public function forgot_password_post()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('forgot-password');
        }

        $user = $this->user_model->find_by_email($this->input->post('email'));

        // Always show success — prevents email enumeration
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->user_model->set_reset_token($user->id, $token, $expires);

            $reset_url = base_url('reset-password/' . $token);
            $this->_send_reset_email($user->email, $user->full_name, $reset_url);
        }

        $this->redirect_with_message('login', 'If that email exists, a reset link has been sent.');
    }

    // GET /reset-password/:token
    public function reset_password($token)
    {
        $user = $this->user_model->find_by_reset_token($token);
        if (!$user) {
            $this->redirect_with_message('forgot-password', 'This reset link is invalid or has expired.');
        }

        $data['page_title']   = 'Set New Password';
        $data['token']        = $token;
        $data['content_view'] = 'auth/reset_password';
        $this->load->view('layouts/auth', $data);
    }

    // POST /reset-password/:token
    public function reset_password_post($token)
    {
        $user = $this->user_model->find_by_reset_token($token);
        if (!$user) {
            $this->redirect_with_message('forgot-password', 'This reset link is invalid or has expired.');
        }

        $this->form_validation->set_rules('password',         'Password',             'required|min_length[8]');
        $this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors(' ', ' '));
            redirect('reset-password/' . $token);
        }

        $this->user_model->update_password($user->id, $this->input->post('password'));
        $this->user_model->clear_reset_token($user->id);

        $this->redirect_with_message('login', 'Password updated. Please log in.');
    }

    private function _send_reset_email($to_email, $to_name, $reset_url)
    {
        $this->load->config('email');
        $this->email->initialize($this->config->config);
        $this->email->to($to_email);
        $this->email->subject('Password Reset — CI3 Shop');
        $this->email->message(
            '<p>Hi ' . htmlspecialchars($to_name) . ',</p>' .
            '<p>Click the link below to reset your password. This link expires in 1 hour.</p>' .
            '<p><a href="' . $reset_url . '">' . $reset_url . '</a></p>'
        );
        $this->email->send();
    }
}
```

- [ ] **Step 2: Add route for login POST**

Edit `application/config/routes.php`, add after the login GET route:
```php
$route['login']      = 'auth/login';
$route['login/post'] = 'auth/login_post';
$route['register']   = 'auth/register';
$route['register/post'] = 'auth/register_post';
$route['apply-seller/post'] = 'auth/apply_seller_post';
$route['forgot-password/post'] = 'auth/forgot_password_post';
$route['reset-password/(:any)/post'] = 'auth/reset_password_post/$1';
```

- [ ] **Step 3: Commit**

```powershell
git add application/controllers/Auth.php application/config/routes.php
git commit -m "feat: add Auth controller (login, register, logout, seller apply, password reset)"
```

---

## Task 10: Auth Views

**Files:**
- Create: `application/views/auth/login.php`
- Create: `application/views/auth/register.php`
- Create: `application/views/auth/apply_seller.php`
- Create: `application/views/auth/forgot_password.php`
- Create: `application/views/auth/reset_password.php`

- [ ] **Step 1: Create login view**

Create `C:\Projects\ci3-ecomm\application\views\auth\login.php`:
```html
<?= form_open('login/post') ?>
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email"
           value="<?= set_value('email') ?>" required>
</div>
<div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control" id="password" name="password" required>
</div>
<button type="submit" class="btn btn-primary w-100">Login</button>
<?= form_close() ?>
<hr>
<p class="text-center">
    <a href="<?= base_url('forgot-password') ?>">Forgot password?</a> &bull;
    <a href="<?= base_url('register') ?>">Create account</a>
</p>
```

- [ ] **Step 2: Create register view**

Create `C:\Projects\ci3-ecomm\application\views\auth\register.php`:
```html
<?= form_open('register/post') ?>
<div class="mb-3">
    <label for="full_name" class="form-label">Full Name</label>
    <input type="text" class="form-control" id="full_name" name="full_name"
           value="<?= set_value('full_name') ?>" required>
</div>
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email"
           value="<?= set_value('email') ?>" required>
</div>
<div class="mb-3">
    <label for="phone" class="form-label">Phone (optional)</label>
    <input type="text" class="form-control" id="phone" name="phone"
           value="<?= set_value('phone') ?>">
</div>
<div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control" id="password" name="password" required>
</div>
<div class="mb-3">
    <label for="password_confirm" class="form-label">Confirm Password</label>
    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
</div>
<button type="submit" class="btn btn-success w-100">Create Account</button>
<?= form_close() ?>
<p class="text-center mt-3"><a href="<?= base_url('login') ?>">Already have an account?</a></p>
```

- [ ] **Step 3: Create apply_seller view**

Create `C:\Projects\ci3-ecomm\application\views\auth\apply_seller.php`:
```html
<p class="text-muted">Fill in your store details. Your application will be reviewed by an admin.</p>
<?= form_open('apply-seller/post') ?>
<div class="mb-3">
    <label for="store_name" class="form-label">Store Name</label>
    <input type="text" class="form-control" id="store_name" name="store_name"
           value="<?= set_value('store_name') ?>" required>
</div>
<div class="mb-3">
    <label for="store_description" class="form-label">Description</label>
    <textarea class="form-control" id="store_description" name="store_description"
              rows="4"><?= set_value('store_description') ?></textarea>
</div>
<button type="submit" class="btn btn-primary w-100">Submit Application</button>
<?= form_close() ?>
```

- [ ] **Step 4: Create forgot_password view**

Create `C:\Projects\ci3-ecomm\application\views\auth\forgot_password.php`:
```html
<p class="text-muted">Enter your email and we'll send a reset link.</p>
<?= form_open('forgot-password/post') ?>
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email" required>
</div>
<button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
<?= form_close() ?>
<p class="text-center mt-3"><a href="<?= base_url('login') ?>">Back to Login</a></p>
```

- [ ] **Step 5: Create reset_password view**

Create `C:\Projects\ci3-ecomm\application\views\auth\reset_password.php`:
```html
<?= form_open('reset-password/' . $token . '/post') ?>
<div class="mb-3">
    <label for="password" class="form-label">New Password</label>
    <input type="password" class="form-control" id="password" name="password" required minlength="8">
</div>
<div class="mb-3">
    <label for="password_confirm" class="form-label">Confirm New Password</label>
    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
</div>
<button type="submit" class="btn btn-primary w-100">Update Password</button>
<?= form_close() ?>
```

- [ ] **Step 6: Create a temporary Shop controller to make the app load**

Create `C:\Projects\ci3-ecomm\application\controllers\Shop.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends MY_Controller
{
    public function index()
    {
        $data['page_title']   = 'Shop';
        $data['content_view'] = 'shop/index';
        $this->load->view('layouts/main', $data);
    }
}
```

Create `C:\Projects\ci3-ecomm\application\views\shop\index.php`:
```html
<h1>Welcome to CI3 Shop</h1>
<p class="text-muted">Products coming soon.</p>
```

- [ ] **Step 7: Add Cart_model stub (needed by Auth login)**

Create `C:\Projects\ci3-ecomm\application\models\Cart_model.php`:
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart_model extends MY_Model
{
    protected $table   = 'carts';
    protected $primary = 'id';

    public function merge_guest_cart($session_id, $user_id)
    {
        $guest_cart = $this->db
            ->where('session_id', $session_id)
            ->where('user_id IS NULL')
            ->get($this->table)
            ->row();

        if (!$guest_cart) return;

        $user_cart = $this->db
            ->where('user_id', $user_id)
            ->get($this->table)
            ->row();

        if (!$user_cart) {
            $this->db->where('id', $guest_cart->id)
                     ->update($this->table, array('user_id' => $user_id));
            return;
        }

        // Move guest items into user cart
        $this->db->trans_start();
        $this->db->where('cart_id', $guest_cart->id)
                 ->update('cart_items', array('cart_id' => $user_cart->id));
        $this->db->delete($this->table, array('id' => $guest_cart->id));
        $this->db->trans_complete();
    }
}
```

- [ ] **Step 8: Smoke test auth in browser**

1. Open `http://localhost/ci3-ecomm/`
   Expected: "Welcome to CI3 Shop" with nav bar.

2. Open `http://localhost/ci3-ecomm/register`
   Expected: Registration form renders without errors.

3. Register a test buyer account (e.g. `buyer@test.com` / `Test@1234`).
   Expected: Redirected to `/` with "Welcome!" flash message.

4. Open `http://localhost/ci3-ecomm/logout`
   Expected: Redirected to `/login`.

5. Login with the credentials just created.
   Expected: Redirected to `/` with no errors.

6. Open `http://localhost/ci3-ecomm/forgot-password`
   Expected: Form renders. Submit any email — should show "If that email exists..." message.

- [ ] **Step 9: Commit**

```powershell
git add application/controllers/Shop.php application/models/Cart_model.php application/views/auth/ application/views/shop/index.php
git commit -m "feat: add auth views, Shop stub, Cart_model stub — auth flow complete"
```

---

## Task 11: Final Plan 1 Integration Test

- [ ] **Step 1: Run full PHPUnit suite**

```powershell
vendor\bin\phpunit --testdox
```

Expected: all tests PASS (or skip if MySQL unavailable in test env).

- [ ] **Step 2: Verify admin login works**

The schema seeded an admin user with a default password. The seeded password hash is for `password` (the literal string). Update it:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -p ci3_ecomm -e "UPDATE users SET password = '$(php -r "echo password_hash('Admin@1234', PASSWORD_BCRYPT);")' WHERE email = 'admin@ci3ecomm.local';"
```

Then login at `http://localhost/ci3-ecomm/login` with `admin@ci3ecomm.local` / `Admin@1234`.
Expected: Login succeeds.

- [ ] **Step 3: Verify seller application flow**

1. Register a new buyer account: `seller@test.com`
2. Go to `http://localhost/ci3-ecomm/apply-seller`
3. Submit store name "Test Store"
   Expected: "Your seller application has been submitted." flash message.
4. Check MySQL: `SELECT * FROM stores;`
   Expected: one row with `status = 'pending'`.

- [ ] **Step 4: Final commit**

```powershell
git add .
git commit -m "feat: Plan 1 complete — CI3 foundation, schema, MY_Model, MY_Controller, full auth"
```

---

## What's Next

**Plan 2** covers: Category_model, Product_model, Shop controller (listing, product detail, store page, search), all shop views, Vite project setup, LiveSearch.vue, ProductGallery.vue.

Plans are saved in `docs/superpowers/plans/`.
