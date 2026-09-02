<?php

/** @var App\Core\Router $router */

use App\Middleware\AdminMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\CsrfMiddleware;

// ----------------------------------------------------
// Storefront Public Routes (Everful Wholesale Architecture)
// ----------------------------------------------------
$router->get('/', 'Web\HomeController@index');
$router->get('/catalog', 'Web\CatalogController@index');
$router->get('/product/{slug}/{variant}', 'Web\ProductDetailController@show');
$router->get('/product/{slug}', 'Web\ProductDetailController@show');
$router->get('/cart', 'Web\CartViewController@index');

// Multi-Product B2B Inquiry Routes
$router->get('/inquiry', 'Web\InquiryViewController@index');
$router->get('/api/inquiry', 'Api\InquiryApiController@getInquiry');
$router->post('/api/inquiry/add', 'Api\InquiryApiController@addItem');
$router->post('/api/inquiry/toggle', 'Api\InquiryApiController@toggleItem');
$router->post('/api/inquiry/update', 'Api\InquiryApiController@updateItem');
$router->post('/api/inquiry/remove', 'Api\InquiryApiController@removeItem');
$router->post('/api/inquiry/submit', 'Api\InquiryApiController@submit');

// High-Scale Ephemeral REST Cart & Checkout APIs
$router->get('/api/cart', 'Api\CartApiController@getCart');
$router->post('/api/cart/add', 'Api\CartApiController@addItem');
$router->post('/api/cart/update', 'Api\CartApiController@updateItem');
$router->post('/api/checkout/process', 'Api\CheckoutApiController@process');
$router->post('/api/currency/set', 'Api\CurrencyApiController@setPreference');
$router->get('/api/currency/map', 'Api\CurrencyApiController@getMap');



// Customer Auth Routes (Session-based)
$router->get('/login', 'AuthController@customerLogin');
$router->post('/login', 'AuthController@customerLogin', [CsrfMiddleware::class]);
$router->get('/signup', 'AuthController@customerSignup');
$router->post('/signup', 'AuthController@customerSignup', [CsrfMiddleware::class]);
$router->get('/forgot-password', 'AuthController@customerForgotPassword');
$router->post('/forgot-password', 'AuthController@processCustomerForgotPassword', [CsrfMiddleware::class]);
$router->get('/reset-password', 'AuthController@customerResetPassword');
$router->post('/reset-password', 'AuthController@processCustomerResetPassword', [CsrfMiddleware::class]);
$router->get('/logout', 'AuthController@customerLogout');
$router->get('/account', 'AuthController@account');

// Wishlist Routes (AJAX & Page View)
$router->get('/wishlist', 'Web\WishlistController@index');
$router->post('/wishlist/toggle', 'Web\WishlistController@toggle');
$router->get('/wishlist/status', 'Web\WishlistController@status');

// Cart & Checkout Actions
$router->get('/cart', 'Web\CartController@index');
$router->get('/cart/data', 'Web\CartController@data');
$router->post('/cart/add', 'Web\CartController@add');
$router->post('/cart/update', 'Web\CartController@update');
$router->post('/cart/remove', 'Web\CartController@remove');

$router->get('/checkout', 'Web\CheckoutController@index');
$router->post('/checkout/create-order', 'Web\CheckoutController@createOrder');
$router->post('/checkout/razorpay-verify', 'Web\CheckoutController@verifyRazorpay');
$router->get('/checkout/success/{id}', 'Web\CheckoutController@success');

// Cart Coupon Actions
$router->post('/cart/apply-coupon', 'CartController@applyCoupon', [CsrfMiddleware::class]);
$router->post('/cart/remove-coupon', 'CartController@removeCoupon', [CsrfMiddleware::class]);

// Blog & Articles Routes
$router->get('/blog', 'BlogFrontendController@index');
$router->get('/blog/{slug}', 'BlogFrontendController@show');

// Dedicated Reviews Page
$router->get('/reviews', 'Web\ReviewsController@index');

// Support & CMS Pages (Web Storefront)
$router->get('/support', 'Web\SupportController@index');
$router->get('/help-center', 'Web\SupportController@index');
$router->get('/faqs', 'Web\SupportController@index');
$router->get('/contact-us', 'Web\SupportController@contact');
$router->get('/contact-support', 'Web\SupportController@contact');
$router->post('/api/support/contact', 'Web\SupportController@submitContact');

$router->get('/shipping-policy', 'Web\SupportController@shipping');
$router->get('/refund-policy', 'Web\SupportController@refund');
$router->get('/cancellation-policy', 'Web\SupportController@cancellation');
$router->get('/terms-and-conditions', 'Web\SupportController@terms');
$router->get('/privacy-policy', 'Web\SupportController@privacy');
$router->get('/payment-policy', 'Web\SupportController@payment');
$router->get('/about-us', fn() => (new App\Controllers\PageController())->show('about-us'));
$router->get('/page/{slug}', 'PageController@show');
$router->post('/wholesale/inquire', 'WholesaleController@submit');

// RFQ (Request For Quote) Public Submission & Product Fetch APIs
$router->post('/api/rfq/submit', 'Api\RfqApiController@submit');
$router->get('/api/rfq/product-details', 'Api\RfqApiController@getProductDetails');
$router->get('/api/rfq/search-products', 'Api\RfqApiController@searchProducts');

// Visual Search & Image Similarity API
$router->get('/api/visual-search', 'Api\VisualSearchController@search');
$router->post('/api/visual-search', 'Api\VisualSearchController@search');
$router->post('/visual_search.php', 'Api\VisualSearchController@search');
$router->get('/visual_search.php', 'Api\VisualSearchController@search');
$router->get('/api/visual-search/reindex', 'Api\VisualSearchController@reindex');

// Storefront Customer Product Review Submission
$router->post('/product/review/add', 'ReviewController@submitStorefront', [CsrfMiddleware::class]);

// API Autocomplete, Heartbeat & Refresh Token
$router->get('/api/search', 'ApiController@searchAutocomplete');
$router->get('/api/scooter-models', 'ApiController@getModelsByBrand');
$router->get('/api/heartbeat', 'HeartbeatController@update');
$router->post('/api/heartbeat', 'HeartbeatController@update');
$router->post('/api/refresh-token', 'AuthController@refreshToken');
$router->get('/api/refresh-token', 'AuthController@refreshToken');

// ----------------------------------------------------
// Admin Employee Auth Routes
// ----------------------------------------------------
$router->get('/admin/login', 'AuthController@login');
$router->post('/admin/login', 'AuthController@processLogin', [CsrfMiddleware::class]);
$router->get('/admin/forgot-password', 'AuthController@adminForgotPassword');
$router->post('/admin/forgot-password', 'AuthController@processAdminForgotPassword', [CsrfMiddleware::class]);
$router->get('/admin/reset-password', 'AuthController@adminResetPassword');
$router->post('/admin/reset-password', 'AuthController@processAdminResetPassword', [CsrfMiddleware::class]);
$router->get('/admin/logout', 'AuthController@logout');

// ----------------------------------------------------
// Admin Protected Panel Routes (Guarded by RBAC & Session)
// ----------------------------------------------------
$router->get('/admin', fn() => (new App\Core\Response())->redirect(url('admin/dashboard')));
$router->get('/admin/dashboard', 'Admin\DashboardController@index', [AdminMiddleware::class]);

// Admin B2B Inquiry Manager
$router->get('/admin/inquiries', 'Admin\InquiryController@index', [AdminMiddleware::class]);
$router->get('/admin/inquiries/{id}', 'Admin\InquiryController@show', [AdminMiddleware::class]);
$router->post('/admin/inquiries/update-status/{id}', 'Admin\InquiryController@updateStatus', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/inquiries/update-notes/{id}', 'Admin\InquiryController@updateNotes', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/inquiries/delete/{id}', 'Admin\InquiryController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin RFQ (Request For Quote) Manager
$router->get('/admin/rfq', 'Admin\RfqController@index', [AdminMiddleware::class]);
$router->get('/admin/rfq/export-csv', 'Admin\RfqController@exportCsv', [AdminMiddleware::class]);
$router->get('/admin/rfq/{id}', 'Admin\RfqController@show', [AdminMiddleware::class]);
$router->post('/admin/rfq/update-status/{id}', 'Admin\RfqController@updateStatus', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/rfq/delete/{id}', 'Admin\RfqController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);

// Live Analytics
$router->get('/admin/analytics', 'Admin\AnalyticsController@index', [AdminMiddleware::class]);
$router->get('/admin/analytics/user-orders/{id}', 'Admin\AnalyticsController@userOrders', [AdminMiddleware::class]);

// Notification Settings
$router->get('/admin/notification-settings', 'Admin\NotificationSettingsController@index', [AdminMiddleware::class]);
$router->post('/admin/notification-settings/update', 'Admin\NotificationSettingsController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/notification-settings/test', 'Admin\NotificationSettingsController@testSend', [AdminMiddleware::class]);

// Spare Parts Specs & Compatibility Manager
$router->get('/admin/product-compatibility', 'Admin\ProductCompatibilityController@index', [AdminMiddleware::class]);
$router->post('/admin/product-compatibility/update/{id}', 'Admin\ProductCompatibilityController@update', [AdminMiddleware::class, CsrfMiddleware::class]);

// Announcement Bar Manager
$router->get('/admin/announcement', 'Admin\AnnouncementController@index', [AdminMiddleware::class]);
$router->post('/admin/announcement/update', 'Admin\AnnouncementController@update', [AdminMiddleware::class, CsrfMiddleware::class]);

// Navigation Links Manager (Manage Top Navigation)
$router->get('/admin/navigation', 'Admin\NavigationController@index', [AdminMiddleware::class]);
$router->post('/admin/navigation/store', 'Admin\NavigationController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/navigation/update/{id}', 'Admin\NavigationController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/navigation/delete/{id}', 'Admin\NavigationController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/navigation/delete/{id}', 'Admin\NavigationController@delete', [AdminMiddleware::class]);
$router->post('/admin/navigation/toggle-status/{id}', 'Admin\NavigationController@toggleStatus', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/navigation/toggle-status/{id}', 'Admin\NavigationController@toggleStatus', [AdminMiddleware::class]);
$router->get('/admin/navigation/move/{id}/{direction}', 'Admin\NavigationController@move', [AdminMiddleware::class]);
$router->post('/admin/navigation/reorder', 'Admin\NavigationController@reorder', [AdminMiddleware::class, CsrfMiddleware::class]);

// Hero Banner Manager
$router->get('/admin/banners', 'Admin\BannerController@index', [AdminMiddleware::class]);
$router->post('/admin/banners/store', 'Admin\BannerController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/banners/edit/{id}', 'Admin\BannerController@edit', [AdminMiddleware::class]);
$router->post('/admin/banners/update/{id}', 'Admin\BannerController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/banners/toggle/{id}', 'Admin\BannerController@toggleStatus', [AdminMiddleware::class]);
$router->get('/admin/banners/delete/{id}', 'Admin\BannerController@delete', [AdminMiddleware::class]);
$router->post('/admin/banners/delete/{id}', 'Admin\BannerController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);

// Homepage Compare Products Manager
$router->get('/admin/homepage-compare', 'Admin\HomepageCompareController@index', [AdminMiddleware::class]);
$router->post('/admin/homepage-compare/add', 'Admin\HomepageCompareController@add', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/homepage-compare/remove', 'Admin\HomepageCompareController@remove', [AdminMiddleware::class, CsrfMiddleware::class]);

// Homepage Featured Categories Manager (EverfulWholesale UI)
$router->get('/admin/featured-categories', 'Admin\FeaturedCategoryController@index', [AdminMiddleware::class]);
$router->post('/admin/featured-categories/store-category', 'Admin\FeaturedCategoryController@storeCategory', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/featured-categories/update-category/{id}', 'Admin\FeaturedCategoryController@updateCategory', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/featured-categories/delete-category/{id}', 'Admin\FeaturedCategoryController@deleteCategory', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/featured-categories/reorder-categories', 'Admin\FeaturedCategoryController@reorderCategories', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/featured-categories/store-subcategory', 'Admin\FeaturedCategoryController@storeSubcategory', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/featured-categories/update-subcategory/{id}', 'Admin\FeaturedCategoryController@updateSubcategory', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/featured-categories/delete-subcategory/{id}', 'Admin\FeaturedCategoryController@deleteSubcategory', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/featured-categories/reorder-subcategories', 'Admin\FeaturedCategoryController@reorderSubcategories', [AdminMiddleware::class, CsrfMiddleware::class]);

// Public API Endpoints for Featured Categories
$router->get('/api/featured-categories', 'Admin\FeaturedCategoryController@apiIndex');

// ============================================================
// Product Collection Cards Manager
// ============================================================
$router->get('/admin/collection-cards', 'Admin\CollectionCardController@index', [AdminMiddleware::class]);
$router->post('/admin/collection-cards/store', 'Admin\CollectionCardController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/collection-cards/update/{id}', 'Admin\CollectionCardController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/collection-cards/delete/{id}', 'Admin\CollectionCardController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/collection-cards/delete/{id}', 'Admin\CollectionCardController@delete', [AdminMiddleware::class]);
$router->post('/admin/collection-cards/update-products/{id}', 'Admin\CollectionCardController@updateProducts', [AdminMiddleware::class]);
$router->get('/admin/collection-cards/search-products', 'Admin\CollectionCardController@searchProducts', [AdminMiddleware::class]);
$router->post('/admin/collection-cards/reorder', 'Admin\CollectionCardController@reorder', [AdminMiddleware::class, CsrfMiddleware::class]);

// Public API for Collection Cards (storefront)
$router->get('/api/collection-cards', 'Admin\CollectionCardController@apiIndex');

// Homepage Sections Manager (Featured, Deals, Best Sellers, New Arrivals, Flash Sale)
$router->get('/admin/homepage-sections', 'Admin\HomepageSectionsController@index', [AdminMiddleware::class]);
$router->get('/admin/homepage-sections/search-products', 'Admin\HomepageSectionsController@searchProducts', [AdminMiddleware::class]);
$router->post('/admin/homepage-sections/update/{key}', 'Admin\HomepageSectionsController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/homepage-sections/update-promo', 'Admin\HomepageSectionsController@updatePromo', [AdminMiddleware::class, CsrfMiddleware::class]);

// API Endpoints for Homepage Sections
$router->get('/api/homepage-sections', 'Admin\HomepageSectionsController@apiIndex');
$router->get('/api/homepage-sections/{key}', 'Admin\HomepageSectionsController@apiShow');

// Homepage Videos Manager
$router->get('/admin/videos', 'Admin\VideoController@index', [AdminMiddleware::class]);
$router->post('/admin/videos/store', 'Admin\VideoController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/videos/update/{id}', 'Admin\VideoController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/videos/delete/{id}', 'Admin\VideoController@delete', [AdminMiddleware::class]);

// Google Customer Reviews Manager
$router->get('/admin/google-reviews', 'Admin\GoogleReviewController@index', [AdminMiddleware::class]);
$router->post('/admin/google-reviews/store', 'Admin\GoogleReviewController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/google-reviews/update/{id}', 'Admin\GoogleReviewController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/google-reviews/delete/{id}', 'Admin\GoogleReviewController@delete', [AdminMiddleware::class]);

// Customer Testimonials & Reviews Manager (Everful Style)
$router->get('/admin/testimonials', 'Admin\TestimonialController@index', [AdminMiddleware::class]);
$router->post('/admin/testimonials/store', 'Admin\TestimonialController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/testimonials/update/{id}', 'Admin\TestimonialController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/testimonials/delete/{id}', 'Admin\TestimonialController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/testimonials/delete/{id}', 'Admin\TestimonialController@delete', [AdminMiddleware::class]);
$router->post('/admin/testimonials/toggle-status/{id}', 'Admin\TestimonialController@toggleStatus', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/testimonials/toggle-status/{id}', 'Admin\TestimonialController@toggleStatus', [AdminMiddleware::class]);
$router->post('/admin/testimonials/toggle-featured/{id}', 'Admin\TestimonialController@toggleFeatured', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/testimonials/toggle-featured/{id}', 'Admin\TestimonialController@toggleFeatured', [AdminMiddleware::class]);

// Discount Coupons & Offers Manager
$router->get('/admin/coupons', 'Admin\CouponController@index', [AdminMiddleware::class]);
$router->get('/admin/coupons/create', 'Admin\CouponController@create', [AdminMiddleware::class]);
$router->post('/admin/coupons/store', 'Admin\CouponController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/coupons/edit/{id}', 'Admin\CouponController@edit', [AdminMiddleware::class]);
$router->post('/admin/coupons/update/{id}', 'Admin\CouponController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/coupons/delete/{id}', 'Admin\CouponController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/coupons/toggle/{id}', 'Admin\CouponController@toggleStatus', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/coupons/usage/{id}', 'Admin\CouponController@usage', [AdminMiddleware::class]);

// Products Management
$router->get('/admin/products', 'Admin\ProductController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('products.view'))->execute()]);
$router->get('/admin/products/create', 'Admin\ProductController@create', [AdminMiddleware::class, fn() => (new PermissionMiddleware('products.add'))->execute()]);
$router->post('/admin/products/store', 'Admin\ProductController@store', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('products.add'))->execute()]);
$router->get('/admin/products/edit/{id}', 'Admin\ProductController@edit', [AdminMiddleware::class, fn() => (new PermissionMiddleware('products.edit'))->execute()]);
$router->post('/admin/products/update/{id}', 'Admin\ProductController@update', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('products.edit'))->execute()]);
$router->post('/admin/products/toggle-flag', 'Admin\ProductController@toggleFlag', [AdminMiddleware::class]);
$router->get('/admin/products/delete/{id}', 'Admin\ProductController@delete', [AdminMiddleware::class, fn() => (new PermissionMiddleware('products.delete'))->execute()]);
$router->post('/admin/products/delete/{id}', 'Admin\ProductController@delete', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('products.delete'))->execute()]);

// Gallery Image AJAX Endpoints
$router->post('/admin/products/gallery-upload/{id}', 'Admin\ProductController@galleryUpload', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/products/gallery-delete/{id}', 'Admin\ProductController@galleryDelete', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/products/gallery-set-primary/{id}', 'Admin\ProductController@gallerySetPrimary', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/products/gallery-reorder/{id}', 'Admin\ProductController@galleryReorder', [AdminMiddleware::class, CsrfMiddleware::class]);

// Product Variants, Specs & Search AJAX Endpoints
$router->get('/admin/products/search-api', 'Admin\ProductController@searchApi', [AdminMiddleware::class]);
$router->post('/admin/products/{id}/variants/save', 'Admin\ProductController@saveVariant', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/products/variants/delete/{variantId}', 'Admin\ProductController@deleteVariant', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/products/{id}/specs/save', 'Admin\ProductController@saveSpecs', [AdminMiddleware::class, CsrfMiddleware::class]);



// Brands Management
$router->get('/admin/brands', 'Admin\BrandController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('brands.view'))->execute()]);
$router->post('/admin/brands/store', 'Admin\BrandController@store', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('brands.add'))->execute()]);
$router->post('/admin/brands/update/{id}', 'Admin\BrandController@update', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('brands.edit'))->execute()]);
$router->get('/admin/brands/delete/{id}', 'Admin\BrandController@delete', [AdminMiddleware::class, fn() => (new PermissionMiddleware('brands.delete'))->execute()]);
$router->post('/admin/brands/delete/{id}', 'Admin\BrandController@delete', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('brands.delete'))->execute()]);
$router->post('/admin/brands/toggle-status/{id}', 'Admin\BrandController@toggleStatus', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('brands.edit'))->execute()]);
$router->post('/admin/brands/reorder', 'Admin\BrandController@reorder', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('brands.edit'))->execute()]);
$router->post('/admin/brands/bulk-action', 'Admin\BrandController@bulkAction', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('brands.edit'))->execute()]);


// Categories Management
$router->get('/admin/categories', 'Admin\CategoryController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('categories.view'))->execute()]);
$router->get('/admin/categories/get/{id}', 'Admin\CategoryController@get', [AdminMiddleware::class, fn() => (new PermissionMiddleware('categories.view'))->execute()]);
$router->post('/admin/categories/store', 'Admin\CategoryController@store', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('categories.add'))->execute()]);
$router->post('/admin/categories/update/{id}', 'Admin\CategoryController@update', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('categories.edit'))->execute()]);
$router->get('/admin/categories/delete/{id}', 'Admin\CategoryController@delete', [AdminMiddleware::class, fn() => (new PermissionMiddleware('categories.delete'))->execute()]);
$router->post('/admin/categories/delete/{id}', 'Admin\CategoryController@delete', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('categories.delete'))->execute()]);
$router->post('/admin/categories/reassign-delete/{id}', 'Admin\CategoryController@reassignAndDelete', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('categories.delete'))->execute()]);

// Sub-categories Management
$router->get('/admin/subcategories', 'Admin\SubcategoryController@index', [AdminMiddleware::class]);
$router->get('/admin/subcategories/get/{id}', 'Admin\SubcategoryController@get', [AdminMiddleware::class]);
$router->get('/admin/subcategories/by-category/{categoryId}', 'Admin\SubcategoryController@getByCategory', [AdminMiddleware::class]);
$router->post('/admin/subcategories/store', 'Admin\SubcategoryController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/subcategories/update/{id}', 'Admin\SubcategoryController@update', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/subcategories/delete/{id}', 'Admin\SubcategoryController@delete', [AdminMiddleware::class]);
$router->post('/admin/subcategories/delete/{id}', 'Admin\SubcategoryController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);


// Orders Management
$router->get('/admin/orders', 'Admin\OrderController@index', [AdminMiddleware::class]);
$router->get('/admin/orders/view/{id}', 'Admin\OrderController@view', [AdminMiddleware::class]);
$router->post('/admin/orders/update-status', 'Admin\OrderController@updateStatus', [AdminMiddleware::class]);
$router->get('/admin/orders/invoice/{id}', 'Admin\OrderController@invoice', [AdminMiddleware::class, fn() => (new PermissionMiddleware('orders.view'))->execute()]);

// Sales & Tax Reports
$router->get('/admin/reports/sales-tax', 'Admin\ReportController@salesTax', [AdminMiddleware::class, fn() => (new PermissionMiddleware('orders.view'))->execute()]);
$router->get('/admin/reports/sales-tax/pdf', 'Admin\ReportController@salesTaxPdf', [AdminMiddleware::class, fn() => (new PermissionMiddleware('orders.view'))->execute()]);
$router->get('/admin/reports/sales-tax/csv', 'Admin\ReportController@salesTaxCsv', [AdminMiddleware::class, fn() => (new PermissionMiddleware('orders.view'))->execute()]);

// Inventory Management
$router->get('/admin/inventory', 'Admin\InventoryController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('inventory.view'))->execute()]);
$router->post('/admin/inventory/update', 'Admin\InventoryController@updateStock', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('inventory.edit'))->execute()]);

// Employees & Roles Management
$router->get('/admin/employees', 'Admin\EmployeeController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('employees.view'))->execute()]);
$router->post('/admin/employees/store', 'Admin\EmployeeController@store', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('employees.add'))->execute()]);
$router->post('/admin/employees/update/{id}', 'Admin\EmployeeController@update', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('employees.edit'))->execute()]);
$router->get('/admin/employees/delete/{id}', 'Admin\EmployeeController@delete', [AdminMiddleware::class, fn() => (new PermissionMiddleware('employees.delete'))->execute()]);
$router->post('/admin/employees/delete/{id}', 'Admin\EmployeeController@delete', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('employees.delete'))->execute()]);


$router->get('/admin/roles', 'Admin\RoleController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('employees.view'))->execute()]);
$router->post('/admin/roles/store', 'Admin\RoleController@store', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('employees.add'))->execute()]);
$router->get('/admin/roles/edit/{id}', 'Admin\RoleController@edit', [AdminMiddleware::class, fn() => (new PermissionMiddleware('employees.edit'))->execute()]);
$router->post('/admin/roles/update/{id}', 'Admin\RoleController@update', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('employees.edit'))->execute()]);

// Product Reviews Management
$router->get('/admin/reviews', 'Admin\ReviewController@index', [AdminMiddleware::class]);
$router->post('/admin/reviews/store', 'Admin\ReviewController@store', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/reviews/delete/{id}', 'Admin\ReviewController@delete', [AdminMiddleware::class]);
$router->post('/admin/reviews/delete/{id}', 'Admin\ReviewController@delete', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/reviews/update-status/{id}', 'Admin\ReviewController@updateStatus', [AdminMiddleware::class, CsrfMiddleware::class]);

// Settings & Activity Logs
$router->get('/admin/settings', 'Admin\SettingsController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('settings.view'))->execute()]);
$router->post('/admin/settings/update', 'Admin\SettingsController@update', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('settings.edit'))->execute()]);
$router->get('/admin/settings/payment-shipping', 'Admin\PaymentShippingSettingsController@index', [AdminMiddleware::class]);
$router->post('/admin/settings/payment-shipping/razorpay', 'Admin\PaymentShippingSettingsController@updateRazorpay', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/settings/payment-shipping/razorpay', fn() => header('Location: ' . url('admin/settings/payment-shipping')) . exit);
$router->post('/admin/settings/payment-shipping/shiprocket', 'Admin\PaymentShippingSettingsController@updateShiprocket', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/settings/payment-shipping/shiprocket', fn() => header('Location: ' . url('admin/settings/payment-shipping')) . exit);
$router->post('/admin/settings/payment-shipping/test-razorpay', 'Admin\PaymentShippingSettingsController@testRazorpay', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/settings/payment-shipping/test-shiprocket', 'Admin\PaymentShippingSettingsController@testShiprocket', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/settings/payment-shipping/reveal-secrets', 'Admin\PaymentShippingSettingsController@revealSecrets', [AdminMiddleware::class, CsrfMiddleware::class]);

// Admin Order Actions
$router->post('/admin/orders/retry-shiprocket/{id}', 'Admin\OrderController@retryShiprocket', [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/orders/cancel-shipment/{id}', 'Admin\OrderController@cancelShipment', [AdminMiddleware::class, CsrfMiddleware::class]);

$router->get('/admin/logs', 'Admin\ActivityLogController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('logs.view'))->execute()]);
$router->get('/admin/activity-logs', 'Admin\ActivityLogController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('logs.view'))->execute()]);

// Razorpay & Shiprocket Webhook API Routes
$router->post('/api/checkout/create-order', 'CheckoutApiController@createOrder');
$router->post('/api/checkout/verify-payment', 'CheckoutApiController@verifyPayment');
$router->post('/api/webhooks/razorpay', 'CheckoutApiController@razorpayWebhook');
$router->get('/api/webhooks/razorpay', 'CheckoutApiController@razorpayWebhook');
$router->post('/api/webhooks/shiprocket', 'CheckoutApiController@shiprocketWebhook');
$router->get('/api/webhooks/shiprocket', 'CheckoutApiController@shiprocketWebhook');
$router->get('/api/orders/track', 'CheckoutApiController@trackOrder');
$router->post('/api/orders/track', 'CheckoutApiController@trackOrder');

// Blogs & Articles Management
$router->get('/admin/blogs', 'Admin\BlogController@index', [AdminMiddleware::class, fn() => (new PermissionMiddleware('blogs.view'))->execute()]);
$router->get('/admin/blogs/create', 'Admin\BlogController@create', [AdminMiddleware::class, fn() => (new PermissionMiddleware('blogs.add'))->execute()]);
$router->post('/admin/blogs/store', 'Admin\BlogController@store', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('blogs.add'))->execute()]);
$router->get('/admin/blogs/edit/{id}', 'Admin\BlogController@edit', [AdminMiddleware::class, fn() => (new PermissionMiddleware('blogs.edit'))->execute()]);
$router->post('/admin/blogs/update/{id}', 'Admin\BlogController@update', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('blogs.edit'))->execute()]);
$router->get('/admin/blogs/delete/{id}', 'Admin\BlogController@delete', [AdminMiddleware::class, fn() => (new PermissionMiddleware('blogs.delete'))->execute()]);
$router->post('/admin/blogs/delete/{id}', 'Admin\BlogController@delete', [AdminMiddleware::class, CsrfMiddleware::class, fn() => (new PermissionMiddleware('blogs.delete'))->execute()]);
$router->post('/admin/blogs/upload-image', 'Admin\BlogController@uploadImage', [AdminMiddleware::class, CsrfMiddleware::class]);


