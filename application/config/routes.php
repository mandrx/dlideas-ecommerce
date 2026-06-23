<?php
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
$route['login/post']                    = 'auth/login_post';
$route['register/post']                 = 'auth/register_post';
$route['apply-seller/post']             = 'auth/apply_seller_post';
$route['forgot-password/post']          = 'auth/forgot_password_post';
$route['reset-password/(:any)/post']    = 'auth/reset_password_post/$1';

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
$route['checkout']                      = 'cart/checkout';
$route['cart/save-checkout-session']    = 'cart/save_checkout_session';
$route['checkout/confirm']              = 'cart/confirm';

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
$route['seller/store-settings']         = 'seller/store_settings';
$route['seller/store-settings/save']    = 'seller/save_store_settings';

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
$route['api/coupon/apply']              = 'api/apply_coupon';
$route['api/product/(:num)/reviews/submit'] = 'api/submit_review/$1';
$route['api/product/(:num)/reviews']    = 'api/reviews/$1';
$route['api/shipping/rates']            = 'api/shipping_rates';
$route['api/payment/intent']            = 'api/payment_intent';
$route['api/webhook/stripe']            = 'api/stripe_webhook';

// Image upload API
$route['api/images/upload']             = 'api/upload_image';
$route['api/images/(:num)/delete']      = 'api/delete_image/$1';
$route['api/images/(:num)/set-primary'] = 'api/set_primary_image/$1';
