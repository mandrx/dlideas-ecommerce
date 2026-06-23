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
