# CI3 Ecommerce — Multi-Vendor Marketplace Design Spec

**Date:** 2026-06-23  
**Stack:** CodeIgniter 3, Vue 3, Vite, MySQL, Stripe  
**Goal:** Build a full-featured multi-vendor marketplace while learning and adhering to CI3 best practices and conventions.

---

## 1. Architecture Overview

**Approach:** Strict CI3 MVC with Vue as progressive enhancement.

- CI3 handles all routing, authentication, data access, and HTML rendering via Controllers, Models, and Views.
- Vue 3 mounts as isolated components inside specific CI3 views (product gallery, live search, cart widget, checkout form, review list).
- Vite builds Vue components into `assets/js/`. CI3 views load them via `<script type="module">`.
- No SPA router. No full frontend takeover. CI3 MVC is the primary pattern throughout.

---

## 2. Project Structure

```
ci3-ecomm/
├── application/
│   ├── config/
│   │   ├── autoload.php
│   │   ├── config.php
│   │   ├── database.php
│   │   ├── routes.php
│   │   ├── payment.php
│   │   ├── shipping.php
│   │   └── constants.php
│   ├── controllers/
│   │   ├── Auth.php
│   │   ├── Shop.php
│   │   ├── Cart.php
│   │   ├── Order.php
│   │   ├── Seller.php
│   │   ├── Admin.php
│   │   └── Api.php
│   ├── models/
│   │   ├── User_model.php
│   │   ├── Product_model.php
│   │   ├── Category_model.php
│   │   ├── Order_model.php
│   │   ├── Order_item_model.php
│   │   ├── Cart_model.php
│   │   ├── Review_model.php
│   │   ├── Coupon_model.php
│   │   └── Store_model.php
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── main.php
│   │   │   ├── dashboard.php
│   │   │   └── auth.php
│   │   ├── partials/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   ├── nav.php
│   │   │   └── vue_scripts.php
│   │   ├── shop/
│   │   ├── cart/
│   │   ├── order/
│   │   ├── seller/
│   │   ├── admin/
│   │   └── auth/
│   ├── libraries/
│   │   ├── Payment.php
│   │   ├── Shipping.php
│   │   └── Upload.php
│   ├── helpers/
│   │   ├── auth_helper.php
│   │   └── format_helper.php
│   └── core/
│       ├── MY_Controller.php
│       └── MY_Model.php
├── assets/
│   ├── css/
│   ├── js/
│   └── uploads/
├── vue-src/
│   ├── src/
│   │   ├── components/
│   │   │   ├── ProductGallery.vue
│   │   │   ├── CartWidget.vue
│   │   │   ├── LiveSearch.vue
│   │   │   ├── CheckoutForm.vue
│   │   │   └── ReviewList.vue
│   │   └── entries/
│   │       ├── product.js
│   │       ├── cart.js
│   │       ├── checkout.js
│   │       └── search.js
│   ├── vite.config.js
│   └── package.json
├── index.php
└── .htaccess
```

---

## 3. Database Schema

### Users & Stores
```sql
users       id, email, password, full_name, phone, avatar, role (admin|seller|buyer), status (active|banned), created_at
stores      id, user_id (FK), name, slug, description, logo, banner, status (pending|active|suspended), created_at
```

### Products & Catalog
```sql
categories      id, parent_id (self-ref), name, slug, image, sort_order
products        id, store_id (FK), category_id (FK), name, slug, description, price, sale_price, stock, weight, status (draft|active|inactive), created_at
product_images  id, product_id (FK), image_path, is_primary, sort_order
product_tags    id, product_id (FK), tag
```

### Cart & Orders
```sql
carts           id, user_id (nullable FK), session_id, created_at
cart_items      id, cart_id (FK), product_id (FK), quantity, price_snapshot
orders          id, user_id (FK), store_id (FK), status (pending|paid|processing|shipped|delivered|cancelled|refunded), subtotal, shipping_cost, discount, total, shipping_address (JSON), notes, created_at
order_items     id, order_id (FK), product_id (FK), product_name_snapshot, quantity, unit_price
```

### Payments & Shipping
```sql
payments    id, order_id (FK), gateway (stripe), gateway_ref, amount, status (pending|paid|failed|refunded), paid_at, payload (JSON)
shipments   id, order_id (FK), provider, tracking_number, status (pending|picked_up|in_transit|delivered), shipped_at, delivered_at
```

### Reviews & Coupons
```sql
reviews         id, product_id (FK), user_id (FK), order_id (FK), rating (1-5), body, status (pending|approved|rejected), created_at
coupons         id, code, type (percent|fixed), value, min_order, max_uses, used_count, expires_at, status (active|inactive)
coupon_uses     id, coupon_id (FK), user_id (FK), order_id (FK), used_at
```

**Key decisions:**
- `price_snapshot` and `product_name_snapshot` on order items capture values at purchase time.
- `shipping_address` stored as JSON on the order row.
- `payload` on payments stores raw Stripe webhook response for audit.
- Guest carts use `session_id`; merged into user cart on login.
- One order per store — multi-store checkout creates multiple orders.
- Self-referencing `parent_id` on categories for unlimited nesting.

---

## 4. Authentication & Role System

**Method:** CI3 native session library. No JWT.

**Roles:** `admin`, `seller`, `buyer`. Guests are unauthenticated users.

### Session Data on Login
```php
$this->session->set_userdata([
    'user_id'   => $user->id,
    'role'      => $user->role,
    'full_name' => $user->full_name,
    'store_id'  => $store->id,  // sellers only, null otherwise
]);
```

### MY_Controller.php (application/core/)
Base controller all others extend. Hydrates `$this->current_user` on every request.
Provides `require_login()` and `require_role($role)` guard methods.

### Role Guards
- `Seller.php` and `Admin.php` call `require_role()` in constructor — all methods protected.
- `Shop.php` and `Cart.php` call `require_login()` only on methods that need it (browsing is guest-accessible).

### Auth Flows
| Action | Method | Notes |
|---|---|---|
| Register | `Auth::register()` | Role defaults to buyer |
| Login | `Auth::login()` | CI3 Form Validation + `password_verify()` |
| Logout | `Auth::logout()` | `sess_destroy()` |
| Seller onboarding | `Auth::apply_seller()` | Creates store with `status=pending` |
| Password reset | `Auth::forgot_password()` | Token in DB + CI3 Email library |

### Guest Cart Merge
On login, `Cart_model::merge_guest_cart($session_id, $user_id)` transfers guest cart items to the user cart.

---

## 5. Controllers & Routing

### Route Conventions (application/config/routes.php)
Clean slug-based URLs. No `index.php` in URL (handled by `.htaccess`).

```php
$route['default_controller']          = 'shop';
$route['login']                       = 'auth/login';
$route['register']                    = 'auth/register';
$route['logout']                      = 'auth/logout';
$route['product/(:any)']              = 'shop/product/$1';
$route['store/(:any)']                = 'shop/store/$1';
$route['cart']                        = 'cart/index';
$route['checkout']                    = 'cart/checkout';
$route['checkout/confirm']            = 'cart/confirm';
$route['orders']                      = 'order/index';
$route['orders/(:num)']               = 'order/detail/$1';
$route['seller']                      = 'seller/dashboard';
$route['seller/products']             = 'seller/products';
$route['seller/products/add']         = 'seller/add_product';
$route['seller/products/edit/(:num)'] = 'seller/edit_product/$1';
$route['seller/orders']               = 'seller/orders';
$route['admin']                       = 'admin/dashboard';
$route['admin/users']                 = 'admin/users';
$route['admin/stores']                = 'admin/stores';
$route['admin/coupons']               = 'admin/coupons';
$route['api/search']                  = 'api/search';
$route['api/cart/summary']            = 'api/cart_summary';
$route['api/product/(:num)/reviews']  = 'api/reviews/$1';
$route['api/shipping/rates']          = 'api/shipping_rates';
$route['api/payment/intent']          = 'api/payment_intent';
$route['api/webhook/stripe']          = 'api/stripe_webhook';
```

### Controller Responsibilities
- **Auth.php** — login, register, logout, seller application, password reset. Open to all roles.
- **Shop.php** — public storefront. Homepage, category listing, product detail, store page, search. Guest-accessible.
- **Cart.php** — cart management and checkout. Stripe integration. Mounts CheckoutForm.vue.
- **Order.php** — buyer order history and detail. Requires login.
- **Seller.php** — product CRUD, order fulfillment, store settings. Requires `seller` role.
- **Admin.php** — platform management: users, stores, products, orders, coupons. Requires `admin` role.
- **Api.php** — JSON-only endpoints for Vue components. Returns `application/json`, no views.

### Standard View Loading Pattern
```php
public function product($slug) {
    $data['product']      = $this->product_model->find_by_slug($slug);
    $data['content_view'] = 'shop/product_detail';
    $this->load->view('layouts/main', $data);
}
```
Layout view composes header + content + footer via `$this->load->view()`.

---

## 6. Models & Query Builder Conventions

### MY_Model.php (application/core/)
Base model providing `find()`, `find_all()`, `insert()`, `update()`, `delete()`.
All models extend `MY_Model` and declare `protected $table`.

### Query Builder Rules
- Always use `->where()`, never raw WHERE strings.
- Chain fluently: `->where()->order_by()->limit()->get()`.
- `->result()` for multiple rows, `->row()` for single row.
- `->result_array()` only in `Api.php` for JSON responses.
- Transactions via `$this->db->trans_start()` / `$this->db->trans_complete()` for all multi-step writes.
- Never use raw SQL strings anywhere in the application.

---

## 7. Vue Integration & Vite Setup

### Mount Pattern
CI3 views declare DOM mount points with `data-*` attributes carrying initial data.
Vue entry files find elements by ID and mount components onto them.

```php
// In CI3 view:
<div id="product-gallery"
     data-images="<?= htmlspecialchars(json_encode($images), ENT_QUOTES) ?>">
</div>
<?php $this->load->view('partials/vue_scripts', ['scripts' => ['product']]) ?>
```

```js
// In vue-src/src/entries/product.js:
const el = document.getElementById('product-gallery')
if (el) createApp(ProductGallery, { images: JSON.parse(el.dataset.images) }).mount(el)
```

### Vite Build
- Multiple entry points, one per page context.
- Output to `assets/js/` with stable filenames (`[name].js`).
- No content-hashed filenames — CI3 views reference files by predictable names.

### Data Flow
- **Static/initial data** — passed CI3 controller → view → Vue via `data-*` + `JSON.parse()`.
- **Dynamic data** — Vue fetches from `Api.php` via `fetch()`.
- **Form submissions** — standard HTML POST to CI3 controllers. Vue handles optimistic UI only.

### Vue Components
| Component | Page | Data source |
|---|---|---|
| `LiveSearch.vue` | All pages (nav) | `GET /api/search?q=` |
| `ProductGallery.vue` | Product detail | Props from CI3 view |
| `ReviewList.vue` | Product detail | `GET /api/product/:id/reviews` |
| `CartWidget.vue` | Cart page | `GET /api/cart/summary` |
| `CheckoutForm.vue` | Checkout | Props + Stripe.js |

---

## 8. Payment — Stripe

### Library: application/libraries/Payment.php
Wraps Stripe PHP SDK. Loaded via `$this->load->library('payment')`.
Config loaded from `application/config/payment.php` — secrets from environment variables only.

### Checkout Flow
1. Buyer reaches `/checkout` → `Cart::checkout()` renders `CheckoutForm.vue`
2. Vue calls `POST /api/payment/intent` → `Api.php` creates Stripe PaymentIntent
3. Vue confirms payment via Stripe.js (card data never touches our server)
4. Stripe redirects to `/checkout/confirm?payment_intent=xxx`
5. `Cart::confirm()` verifies intent via `Payment::retrieve_payment_intent()`
6. On success: create order, redeem coupon if any, clear cart, redirect to confirmation

### Webhook Handler
`Api::stripe_webhook()` verifies Stripe signature, handles `payment_intent.succeeded` to mark payment and update order status. Raw payload stored in `payments.payload` for audit.

---

## 9. Shipping Abstraction

### Library: application/libraries/Shipping.php
Loads a provider class from `application/third_party/shipping/` based on `$config['shipping_provider']`.

**Provider contract** — every provider implements:
- `get_rates($origin, $destination, $weight)` → array of rate options
- `get_tracking($tracking_number)` → tracking status

Swap providers by changing one config value. Default: `Flat_rate_provider` for development.

---

## 10. Reviews System

- Buyers can only review a product tied to a `delivered` order they placed.
- `Review_model::can_review($user_id, $product_id)` enforces this with a JOIN query.
- Reviews submitted with `status = pending`.
- Admin approves/rejects via Admin panel.
- Average rating computed via `AVG(rating)` Query Builder aggregate on approved reviews.

---

## 11. Coupons System

- `Coupon_model::validate($code, $user_id, $subtotal)` checks: existence, active status, expiry, usage limit, minimum order.
- Returns discount amount or error message.
- `Coupon_model::redeem()` increments `used_count` and inserts `coupon_uses` record in a transaction.
- Applied at checkout before order creation.

---

## 12. Full Feature Summary

| Area | Feature |
|---|---|
| Auth | Register, Login, Logout (all roles) |
| Auth | Seller onboarding with admin approval |
| Auth | Password reset via email token |
| Shop | Homepage, category listing, product detail |
| Shop | Seller storefront page |
| Shop | Live search (Vue) |
| Shop | Product image gallery (Vue) |
| Cart | Guest + buyer cart, session merge on login |
| Cart | Add, remove, update items |
| Checkout | Stripe payment (PaymentIntent flow) |
| Checkout | Coupon code application |
| Checkout | Shipping rate selection |
| Orders | Buyer order history and detail |
| Orders | Status tracking (pending → delivered) |
| Seller | Product CRUD with image upload |
| Seller | Order fulfillment + tracking number entry |
| Seller | Store profile management |
| Admin | User management (ban/unban) |
| Admin | Store approval queue |
| Admin | Platform-wide order view |
| Admin | Coupon creation and management |
| Reviews | Verified purchase reviews only |
| Reviews | Admin moderation queue |
| Reviews | Star rating + average display |
