<?php
// ═══════════════════════════════════════════════════════════════════
// Notch Technology — Routes
// ═══════════════════════════════════════════════════════════════════

$admin = ADMIN_PREFIX;

// ─── Store Routes ──────────────────────────────────────────────────

// Home
$router->get('', 'StoreController@home');
$router->get('products', 'StoreController@products');
$router->get('products/{slug}', 'StoreController@product');
$router->get('collections/{slug}', 'StoreController@collection');
$router->get('brands/{slug}', 'StoreController@brand');
$router->get('search', 'StoreController@search');
$router->get('about', 'StoreController@about');
$router->get('contact', 'StoreController@contact');
$router->post('contact', 'StoreController@contactSubmit');

// Auth — Customer
$router->get('login', 'AuthController@loginForm');
$router->post('login', 'AuthController@login');
$router->get('register', 'AuthController@registerForm');
$router->post('register', 'AuthController@register');
$router->get('logout', 'AuthController@logout');
$router->get('forgot-password', 'AuthController@forgotForm');
$router->post('forgot-password', 'AuthController@forgot');

// Customer Account
$router->get('account', 'AccountController@dashboard');
$router->get('account/orders', 'AccountController@orders');
$router->get('account/orders/{id}', 'AccountController@orderDetail');
$router->get('account/addresses', 'AccountController@addresses');
$router->post('account/addresses', 'AccountController@addAddress');
$router->post('account/addresses/delete', 'AccountController@deleteAddress');
$router->get('account/wishlist', 'AccountController@wishlist');
$router->get('account/profile', 'AccountController@profile');
$router->post('account/profile', 'AccountController@updateProfile');

// Cart
$router->get('cart', 'CartController@index');
$router->post('cart/add', 'CartController@add');
$router->post('cart/update', 'CartController@update');
$router->post('cart/remove', 'CartController@remove');
$router->post('cart/clear', 'CartController@clear');
$router->post('cart/discount', 'CartController@applyDiscount');
$router->get('cart/count', 'CartController@count');

// Checkout
$router->get('checkout', 'CheckoutController@index');
$router->post('checkout', 'CheckoutController@process');
$router->get('checkout/success/{order_number}', 'CheckoutController@success');

// Payment
$router->get('payment/callback', 'PaymentController@callback');
$router->post('payment/callback', 'PaymentController@callback');
$router->get('payment/success', 'PaymentController@success');
$router->get('payment/failed', 'PaymentController@failed');

// Wishlist (Ajax)
$router->post('wishlist/toggle', 'WishlistController@toggle');

// ─── Admin Routes ──────────────────────────────────────────────────

// Auth
$router->get("{$admin}/login", 'AdminAuthController@loginForm');
$router->post("{$admin}/login", 'AdminAuthController@login');
$router->get("{$admin}/logout", 'AdminAuthController@logout');

// Dashboard
$router->get($admin, 'DashboardController@index');
$router->get("{$admin}/dashboard", 'DashboardController@index');

// Products
$router->get("{$admin}/products", 'AdminProductController@index');
$router->get("{$admin}/products/create", 'AdminProductController@create');
$router->post("{$admin}/products/create", 'AdminProductController@store');
$router->get("{$admin}/products/{id}/edit", 'AdminProductController@edit');
$router->post("{$admin}/products/{id}/edit", 'AdminProductController@update');
$router->post("{$admin}/products/{id}/delete", 'AdminProductController@delete');
$router->post("{$admin}/products/{id}/image-delete", 'AdminProductController@deleteImage');

// Collections
$router->get("{$admin}/collections", 'AdminCollectionController@index');
$router->get("{$admin}/collections/create", 'AdminCollectionController@create');
$router->post("{$admin}/collections/create", 'AdminCollectionController@store');
$router->get("{$admin}/collections/{id}/edit", 'AdminCollectionController@edit');
$router->post("{$admin}/collections/{id}/edit", 'AdminCollectionController@update');
$router->post("{$admin}/collections/{id}/delete", 'AdminCollectionController@delete');

// Brands
$router->get("{$admin}/brands", 'AdminBrandController@index');
$router->post("{$admin}/brands/create", 'AdminBrandController@store');
$router->post("{$admin}/brands/{id}/edit", 'AdminBrandController@update');
$router->post("{$admin}/brands/{id}/delete", 'AdminBrandController@delete');

// Orders
$router->get("{$admin}/orders", 'AdminOrderController@index');
$router->get("{$admin}/orders/{id}", 'AdminOrderController@show');
$router->post("{$admin}/orders/{id}/status", 'AdminOrderController@updateStatus');
$router->post("{$admin}/orders/{id}/notes", 'AdminOrderController@updateNotes');

// Customers
$router->get("{$admin}/customers", 'AdminCustomerController@index');
$router->get("{$admin}/customers/{id}", 'AdminCustomerController@show');
$router->post("{$admin}/customers/{id}/toggle", 'AdminCustomerController@toggle');

// Discounts
$router->get("{$admin}/discounts", 'AdminDiscountController@index');
$router->post("{$admin}/discounts/create", 'AdminDiscountController@store');
$router->post("{$admin}/discounts/{id}/toggle", 'AdminDiscountController@toggle');
$router->post("{$admin}/discounts/{id}/delete", 'AdminDiscountController@delete');

// Analytics
$router->get("{$admin}/analytics", 'AdminAnalyticsController@index');
$router->get("{$admin}/analytics/export", 'AdminAnalyticsController@export');

// Settings
$router->get("{$admin}/settings", 'AdminSettingsController@index');
$router->post("{$admin}/settings", 'AdminSettingsController@update');
$router->get("{$admin}/settings/shipping", 'AdminSettingsController@shipping');
$router->post("{$admin}/settings/shipping", 'AdminSettingsController@updateShipping');
$router->post("{$admin}/settings/shipping/{id}/delete", 'AdminSettingsController@deleteShipping');

// Reviews
$router->get("{$admin}/reviews", 'AdminReviewController@index');
$router->post("{$admin}/reviews/{id}/approve", 'AdminReviewController@approve');
$router->post("{$admin}/reviews/{id}/delete", 'AdminReviewController@delete');

// Updater
$router->get("{$admin}/updater", 'UpdaterController@index');
$router->post("{$admin}/updater/upload", 'UpdaterController@upload');
$router->post("{$admin}/updater/apply/{version}", 'UpdaterController@apply');
$router->post("{$admin}/updater/rollback/{version}", 'UpdaterController@rollback');
