<?php

require_once __DIR__ . '/Router.php';

$router = new Router();

// =============================================================================
// PUBLIC ROUTES (Storefront & Auth)
// =============================================================================

$router->get('/', 'HomeController@index');
$router->get('home', 'HomeController@index');

// Storefront Cart & Auth AJAX Routes
$router->post('cart/add', 'Storefront/CartController@add');
$router->get('cart/count', 'Storefront/CartController@count');

// Wishlist AJAX (public — returns 401 JSON if not logged in, for header heart icon)
$router->post('wishlist/add', 'Storefront/WishlistController@add');
$router->get('wishlist/count', 'Storefront/WishlistController@count');

$router->get('login', 'Storefront/AuthController@showLoginForm');
$router->get('register', 'Storefront/AuthController@showLoginForm');
$router->post('auth/login', 'Storefront/AuthController@login');
$router->post('auth/register', 'Storefront/AuthController@register');
$router->get('auth/status', 'Storefront/AuthController@status');
$router->get('auth/logout', 'Storefront/AuthController@logout');

// Category & Product Public Catalog Routes
$router->get('category/{slug}', 'Storefront/CategoryController@show');
$router->get('categories',      'Storefront/CategoryController@index');
$router->get('product/{slug}',  'Storefront/ProductController@show');

// Product Search & Autocomplete API
$router->get('search',                 'Storefront/SearchController@index');
$router->get('api/search/suggestions', 'Storefront/SearchController@suggestions');

// Offers & Promotions Public Route
$router->get('offers',                 'Storefront/OfferController@index');
$router->get('offers/{id}',            'Storefront/OfferController@show');
$router->get('offer/{id}',             'Storefront/OfferController@show');
$router->get('deals',                  'Storefront/OfferController@index');

// SEO Routes (Dynamic XML Sitemap & Robots.txt)
$router->get('sitemap.xml',            'Storefront/SitemapController@index');
$router->get('robots.txt',             'Storefront/SitemapController@robots');

// Static Informational Pages
$router->get('about',                  'Storefront/PageController@about');
$router->get('contact',                'Storefront/PageController@contact');
$router->post('contact/submit',        'Storefront/PageController@contactSubmit');
$router->get('terms',                  'Storefront/PageController@terms');
$router->get('privacy',                'Storefront/PageController@privacy');
$router->get('faq',                    'Storefront/PageController@faq');

// =============================================================================
// PROTECTED CUSTOMER ROUTES (Requires CustomerAuthMiddleware)
// =============================================================================

$router->group(fn() => \App\Middleware\CustomerAuthMiddleware::check(), function() use ($router) {
    $router->get('user/dashboard',              'Storefront/UserController@dashboard');
    $router->get('user/orders',                 'Storefront/UserController@orders');
    $router->get('user/orders/invoice/{id}',    'Storefront/UserController@invoice');
    $router->post('api/user/cancel-order',      'Storefront/UserController@cancelOrder');

    // Wishlist (full page + protected AJAX actions)
    $router->get('wishlist',                   'Storefront/WishlistController@index');
    $router->post('wishlist/remove',           'Storefront/WishlistController@remove');
    $router->post('wishlist/move-to-cart',     'Storefront/WishlistController@moveToCart');

    // Shopping Cart (full page + protected AJAX actions)
    $router->get('cart',                       'Storefront/CartPageController@index');
    $router->post('cart/update-qty',           'Storefront/CartPageController@updateQty');
    $router->post('cart/remove-item',          'Storefront/CartPageController@removeItem');

    // Customer Profile & Password
    $router->get('user/profile',               'Storefront/UserController@profile');
    $router->post('user/profile/update',       'Storefront/UserController@updateProfile');
    $router->post('user/password/update',      'Storefront/UserController@changePassword');

    // Customer Address Management
    $router->get('user/addresses',              'Storefront/UserController@addresses');
    $router->post('user/addresses/store',       'Storefront/UserController@storeAddress');
    $router->post('user/addresses/update',      'Storefront/UserController@updateAddress');
    $router->post('user/addresses/delete',      'Storefront/UserController@deleteAddress');
    $router->post('user/addresses/set-default', 'Storefront/UserController@setDefaultAddress');

    // Customer Product Reviews Management
    $router->get('user/reviews',                'Storefront/UserController@reviews');
    $router->post('user/reviews/store',         'Storefront/UserController@storeReview');
    $router->post('user/reviews/update',        'Storefront/UserController@updateReview');
    $router->post('user/reviews/delete',        'Storefront/UserController@deleteReview');

    // Profile Picture Upload & Removal
    $router->post('user/avatar/upload',         'Storefront/UserController@uploadAvatar');
    $router->post('user/avatar/remove',         'Storefront/UserController@removeAvatar');

    // Customer Help Center & Support
    $router->get('user/help',                   'Storefront/UserController@help');
    $router->get('help',                        'Storefront/UserController@help');
    $router->post('user/help/ticket',           'Storefront/UserController@submitTicket');

    // Checkout & Order Placement
    $router->get('checkout',                        'Storefront/CheckoutController@index');
    $router->post('checkout/place-order',           'Storefront/CheckoutController@placeOrder');
    $router->get('checkout/success/{id}',           'Storefront/CheckoutController@success');

    // PhonePe Payment Gateway Routes
    $router->get('payment/phonepe/initiate/{id}',   'Storefront/PhonePeController@initiate');
    $router->get('payment/phonepe/simulator/{id}',  'Storefront/PhonePeController@simulator');
    $router->post('payment/phonepe/callback',       'Storefront/PhonePeController@callback');
    $router->get('payment/phonepe/redirect/{id}',   'Storefront/PhonePeController@callback');
});

// Admin Authentication Routes
$router->get('admin/login', 'Admin/AuthController@showLoginForm');
$router->post('admin/login', 'Admin/AuthController@login');
$router->post('admin/logout', 'Admin/AuthController@logout');

// =============================================================================
// PROTECTED ADMIN ROUTES (Requires AdminAuthMiddleware)
// =============================================================================

$router->group(fn() => \App\Middleware\AdminAuthMiddleware::check(), function() use ($router) {
    // Admin Dashboard & Analytics
    $router->get('admin', 'Admin/DashboardController@index');
    $router->get('admin/dashboard', 'Admin/DashboardController@index');
    $router->get('admin/search-analytics', 'Admin/SearchAnalyticsController@index');

    // Category Management System
    $router->get('admin/categories', 'Admin/CategoryController@index');
    $router->get('admin/categories/detail/{id}', 'Admin/CategoryController@detail');
    $router->post('admin/categories/store', 'Admin/CategoryController@store');
    $router->post('admin/categories/update', 'Admin/CategoryController@update');
    $router->post('admin/categories/toggle-active', 'Admin/CategoryController@toggleActive');
    $router->post('admin/categories/delete', 'Admin/CategoryController@delete');

    // Order Management System
    $router->get('admin/orders', 'Admin/OrderController@index');
    $router->get('admin/orders/detail/{id}', 'Admin/OrderController@detail');
    $router->post('admin/orders/update-status', 'Admin/OrderController@updateStatus');
    $router->get('admin/orders/export', 'Admin/OrderController@export');

    // Product & Variant Management System
    $router->get('admin/products', 'Admin/ProductController@index');
    $router->get('admin/products/create', 'Admin/ProductController@create');
    $router->get('admin/products/edit/{id}', 'Admin/ProductController@edit');
    $router->get('admin/products/detail/{id}', 'Admin/ProductController@detail');
    $router->post('admin/products/upload-media', 'Admin/ProductController@uploadMedia');
    $router->post('admin/products/store', 'Admin/ProductController@store');
    $router->post('admin/products/update', 'Admin/ProductController@update');
    $router->post('admin/products/toggle-active', 'Admin/ProductController@toggleActive');
    $router->post('admin/products/toggle-stock', 'Admin/ProductController@toggleStock');
    $router->post('admin/products/toggle-featured', 'Admin/ProductController@toggleFeatured');
    $router->post('admin/products/delete', 'Admin/ProductController@delete');
    $router->post('admin/products/restore', 'Admin/ProductController@restore');
    $router->post('admin/products/bulk-action', 'Admin/ProductController@bulkAction');

    // Variant Routes
    $router->get('admin/products/variants/{id}', 'Admin/ProductController@variants');
    $router->post('admin/products/variants/store', 'Admin/ProductController@storeVariant');
    $router->post('admin/products/variants/update', 'Admin/ProductController@updateVariant');
    $router->post('admin/products/variants/delete', 'Admin/ProductController@deleteVariant');
    $router->post('admin/products/variants/toggle-active', 'Admin/ProductController@toggleVariantActive');

    // User Management System
    $router->get('admin/users', 'Admin/UserController@index');
    $router->get('admin/users/view/{id}', 'Admin/UserController@view');
    $router->get('admin/users/detail/{id}', 'Admin/UserController@detail');
    $router->post('admin/users/store', 'Admin/UserController@store');
    $router->post('admin/users/update', 'Admin/UserController@update');
    $router->post('admin/users/toggle-active', 'Admin/UserController@toggleActive');
    $router->post('admin/users/delete', 'Admin/UserController@delete');

    // Reviews & Ratings Management System
    $router->get('admin/reviews', 'Admin/ReviewController@index');
    $router->get('admin/reviews/detail/{id}', 'Admin/ReviewController@detail');
    $router->post('admin/reviews/update-status', 'Admin/ReviewController@updateStatus');
    $router->post('admin/reviews/delete', 'Admin/ReviewController@delete');
    $router->post('admin/reviews/bulk-action', 'Admin/ReviewController@bulkAction');

    // Coupons & Offers Management System
    $router->get('admin/coupons', 'Admin/CouponController@index');
    $router->get('admin/coupons/detail/{id}', 'Admin/CouponController@couponDetail');
    $router->post('admin/coupons/store', 'Admin/CouponController@storeCoupon');
    $router->post('admin/coupons/update', 'Admin/CouponController@updateCoupon');
    $router->post('admin/coupons/toggle-active', 'Admin/CouponController@toggleCouponActive');
    $router->post('admin/coupons/delete', 'Admin/CouponController@deleteCoupon');
    $router->get('admin/coupons/offer-detail/{id}', 'Admin/CouponController@offerDetail');
    $router->post('admin/coupons/offer-store', 'Admin/CouponController@storeOffer');
    $router->post('admin/coupons/offer-update', 'Admin/CouponController@updateOffer');
    $router->post('admin/coupons/offer-toggle-active', 'Admin/CouponController@toggleOfferActive');
    $router->post('admin/coupons/offer-delete', 'Admin/CouponController@deleteOffer');

    // Banner Management System
    $router->get('admin/banners', 'Admin/BannerController@index');
    $router->get('admin/banners/detail/{id}', 'Admin/BannerController@detail');
    $router->post('admin/banners/store', 'Admin/BannerController@store');
    $router->post('admin/banners/update', 'Admin/BannerController@update');
    $router->post('admin/banners/toggle-active', 'Admin/BannerController@toggleActive');
    $router->post('admin/banners/delete', 'Admin/BannerController@delete');

    // Media Management System
    $router->get('admin/media', 'Admin/MediaController@index');
    $router->get('admin/media/api', 'Admin/MediaController@pickerApi');
    $router->post('admin/media/upload', 'Admin/MediaController@upload');
    $router->get('admin/media/detail/{id}', 'Admin/MediaController@detail');
    $router->post('admin/media/update', 'Admin/MediaController@update');
    $router->post('admin/media/delete', 'Admin/MediaController@delete');
    $router->post('admin/media/bulk-delete', 'Admin/MediaController@bulkDelete');


    // Payments Management System
    $router->get('admin/payments', 'Admin/PaymentController@index');
    $router->get('admin/payments/detail/{id}', 'Admin/PaymentController@detail');
    $router->post('admin/payments/update-status', 'Admin/PaymentController@updateStatus');

    // Shipments & Logistics System
    $router->get('admin/shipments', 'Admin/ShipmentController@index');
    $router->get('admin/shipments/detail/{id}', 'Admin/ShipmentController@detail');
    $router->post('admin/shipments/update', 'Admin/ShipmentController@update');

    // System Settings Module
    $router->get('admin/settings', 'Admin/SettingController@index');
    $router->post('admin/settings/update', 'Admin/SettingController@update');
    $router->post('admin/settings/upload-branding', 'Admin/SettingController@uploadBranding');

    // Audit Log System
    $router->get('admin/audit', 'Admin/AuditLogController@index');
    $router->get('admin/audit/detail/{id}', 'Admin/AuditLogController@detail');
    $router->get('admin/audit/export', 'Admin/AuditLogController@export');

    // Notification Center Module
    $router->get('admin/notifications', 'Admin/NotificationController@index');
    $router->get('admin/notifications/unread-feed', 'Admin/NotificationController@unreadFeed');
    $router->post('admin/notifications/mark-read', 'Admin/NotificationController@markAsRead');
    $router->post('admin/notifications/mark-all-read', 'Admin/NotificationController@markAllRead');
    $router->post('admin/notifications/delete', 'Admin/NotificationController@delete');
    $router->post('admin/notifications/bulk', 'Admin/NotificationController@bulkAction');
    $router->post('admin/notifications/send', 'Admin/NotificationController@sendBroadcast');

    // Units Management System
    $router->get('admin/units', 'Admin/UnitController@index');
    $router->post('admin/units/store', 'Admin/UnitController@store');
    $router->post('admin/units/update', 'Admin/UnitController@update');
    $router->post('admin/units/delete', 'Admin/UnitController@delete');

    // Product Tags System
    $router->get('admin/tags', 'Admin/TagController@index');
    $router->post('admin/tags/store', 'Admin/TagController@store');
    $router->post('admin/tags/update', 'Admin/TagController@update');
    $router->post('admin/tags/toggle-active', 'Admin/TagController@toggleActive');
    $router->post('admin/tags/delete', 'Admin/TagController@delete');

    // Inventory & Stock Movement System
    $router->get('admin/inventory', 'Admin/InventoryController@index');
    $router->post('admin/inventory/record', 'Admin/InventoryController@record');

    // Role & Permissions Matrix (RBAC)
    $router->get('admin/roles', 'Admin/RoleController@index');
    $router->get('admin/roles/permissions', 'Admin/RoleController@getRolePermissions');
    $router->post('admin/roles/update-permissions', 'Admin/RoleController@updatePermissions');
    $router->post('admin/roles/store', 'Admin/RoleController@storeRole');

    // Returns & Refunds Operations
    $router->get('admin/returns', 'Admin/ReturnController@index');
    $router->post('admin/returns/update-status', 'Admin/ReturnController@updateStatus');

    // Global Search System
    $router->get('admin/search', 'Admin/SearchController@index');
    $router->get('admin/search/api', 'Admin/SearchController@api');
});

// =============================================================================
// RESOLVE ROUTE
// =============================================================================
$router->resolve();
