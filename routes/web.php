<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\CustomerManagementController;
use App\Http\Controllers\Admin\VenueBookingManagementController;
use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Admin\RechargeManagementController;
use App\Http\Controllers\Admin\PaymentEarningController;
use App\Http\Controllers\Admin\ReportsAnalyticsController;
use App\Http\Controllers\Admin\StaticPageController as AdminStaticPageController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NotificationManagementController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\TicketManagementController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\GstTaxController;
use App\Http\Controllers\Admin\DiscountOfferController;
use App\Http\Controllers\Admin\VendorManagementController;
use App\Http\Controllers\Admin\DeliveryManagementController;
use App\Http\Controllers\CommonController;

Route::any('/',       [LoginController::class,'index']);
Route::post("/checkLogin" ,   [LoginController::class, 'checkLogin'])->name('admin.login.submit');
Route::any("/logout" ,     [LoginController::class, 'logout'])->name('logout');
Route::get('/vendor/login', [LoginController::class, 'vendorLoginForm'])->name('vendor.login');
Route::post('/vendor/login', [LoginController::class, 'vendorLogin'])->name('vendor.login.submit');
Route::get('/vendor/register', [VendorManagementController::class, 'registerForm'])->name('vendor.register');
Route::post('/vendor/register', [VendorManagementController::class, 'registerSubmit'])->name('vendor.register.submit');

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('forgot.password');
Route::post('/send-otp', [PasswordResetController::class, 'sendOtp'])->name('send.otp');
Route::get('/verify-otp', [PasswordResetController::class, 'showVerifyOtpForm'])->name('verify.otp');
Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp'])->name('verify.otp');
Route::post('/resend-otp', [PasswordResetController::class, 'resendOtp'])->name('resend.otp');
Route::get('/reset-password', [PasswordResetController::class, 'showResetPasswordForm'])->name('reset.password.form');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('reset.password');


Route::get('/get-states/{country_id}', [CommonController::class, 'getStateList'])->name('get-states');
Route::get('/get-cities/{state_id}', [CommonController::class, 'getCityList'])->name('get-cities');
Route::get('/get-sub-categories/{category_id}', [CommonController::class, 'getSubCategory'])->name('get-sub-categories');

Route::middleware(['AdminAuth'])->prefix('admin')->group(function(){
        Route::get('/dashboard',[DashboardController::class,'index'])->name('admin.dashboard');
        Route::get('/search', [DashboardController::class, 'globalSearch'])->name('admin.global-search');
        Route::get('/search/suggestions', [DashboardController::class, 'searchSuggestions'])->name('admin.global-search.suggestions');

        // Run Migrations Route
        Route::get('/run-migrations', function() {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $output = \Illuminate\Support\Facades\Artisan::output();
                return response()->json([
                    'success' => true,
                    'message' => 'Migrations executed successfully',
                    'output' => $output
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Migration failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('admin.run-migrations');

        Route::get('/seed-driver-demo', function (\Illuminate\Http\Request $request) {
            try {
                // $fresh = $request->boolean('fresh', false);
                
                // // Clean up existing demo data if fresh parameter is true
                // if ($fresh) {
                //     \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    
                //     // Truncate demo-related tables
                //     \Illuminate\Support\Facades\DB::table('driver_profiles')->truncate();
                //     \Illuminate\Support\Facades\DB::table('driver_wallets')->truncate();
                //     \Illuminate\Support\Facades\DB::table('driver_transactions')->truncate();
                //     \Illuminate\Support\Facades\DB::table('driver_withdrawals')->truncate();
                //     \Illuminate\Support\Facades\DB::table('driver_notifications')->truncate();
                //     \Illuminate\Support\Facades\DB::table('delivery_assignments')->truncate();
                //     \Illuminate\Support\Facades\DB::table('delivery_assignment_invites')->truncate();
                //     \Illuminate\Support\Facades\DB::table('delivery_assignment_rejections')->truncate();
                //     \Illuminate\Support\Facades\DB::table('tickets')->truncate();
                    
                //     \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                // }

                $seeders = [
                    // 'AdminDeliveryPartnerSeeder',
                    // 'DriverAppSeeder',
                    // 'DriverDemoDataSeeder',
                    // 'TicketSampleSeeder',
                    'UserAppDemoDataSeeder',
                    'VendorSeeder',
                ];

                $outputs = [];
                foreach ($seeders as $seeder) {
                    \Illuminate\Support\Facades\Artisan::call('db:seed', [
                        '--class' => 'Database\\Seeders\\' . $seeder
                    ]);
                    $outputs[$seeder] = \Illuminate\Support\Facades\Artisan::output();
                }

                return response()->json([
                    'success' => true,
                    // 'message' => $fresh ? 'All seeders executed successfully (fresh)' : 'All seeders executed successfully',
                    'message' => 'All seeders executed successfully',
                    'seeders_run' => $seeders,
                    // 'fresh' => $fresh,
                    'outputs' => $outputs,
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seeding failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
        })->name('admin.seed-driver-demo');

        // Peoduct Management Routes
        Route::get('/products',[ProductManagementController::class,'products'])->name('admin.products');
        Route::get('/add-product',[ProductManagementController::class,'addProduct'])->name('admin.add-product');
        Route::post('/store-product',[ProductManagementController::class,'storeProduct'])->name('admin.store-product');
        Route::get('/view-product/{id}',[ProductManagementController::class,'viewProduct'])->name('admin.view-product');
        Route::get('/edit-product/{id}',[ProductManagementController::class,'editProduct'])->name('admin.edit-product');
        Route::post('/update-product',[ProductManagementController::class,'updateProduct'])->name('admin.update-product');
        Route::get('/delete-product/{id}',[ProductManagementController::class,'deleteProduct'])->name('admin.delete-product');
        Route::post('/products/{id}/approval-status',[ProductManagementController::class,'updateApprovalStatus'])->name('admin.products.update-approval-status');
        Route::post('/toggle-product-status/{id}',[ProductManagementController::class,'toggleStatus'])->name('admin.products.toggle-status');
        Route::get('/products/export-excel', [ProductManagementController::class, 'exportProductsExcel'])->name('admin.products.export-excel');
        Route::get('/products/export-pdf', [ProductManagementController::class, 'exportProductsPdf'])->name('admin.products.export-pdf');

        // AJAX: Change product category
        Route::post('/products/change-category', [ProductManagementController::class, 'changeProductCategory'])->name('admin.products.change-category');

        // Product Review Management Routes
        Route::get('/product-reviews', [ProductReviewController::class, 'index'])->name('admin.product-reviews');
        Route::post('/product-reviews/{id}/status', [ProductReviewController::class, 'updateStatus'])->name('admin.product-reviews.update-status');
        Route::any('/product-reviews/{id}/delete', [ProductReviewController::class, 'destroy'])->name('admin.product-reviews.delete');

        // Category Management Routes
        Route::get('/categories',[ProductManagementController::class,'categories'])->name('admin.categories');
        Route::get('/add-category',[ProductManagementController::class,'addNewCategory'])->name('admin.add-category');
        Route::post('/store-category',[ProductManagementController::class,'storeCategory'])->name('admin.store-category');
        Route::get('/edit-category/{id}',[ProductManagementController::class,'editCategory'])->name('admin.edit-category');
        Route::post('/update-category',[ProductManagementController::class,'updateCategory'])->name('admin.update-category');
        Route::any('/deleteCategory/{id}', [ProductManagementController::class, 'deleteCategory'])->name('admin.deleteCategory');
        Route::get('/sub-category',[ProductManagementController::class,'subCategories'])->name('admin.sub-category');
        Route::get('/add-sub-category',[ProductManagementController::class,'addSubCategory'])->name('admin.add-sub-category');
        Route::post('/store-sub-category',[ProductManagementController::class,'storeSubCategory'])->name('admin.store-sub-category');
        Route::get('/edit-sub-category/{id}',[ProductManagementController::class,'editSubCategory'])->name('admin.edit-sub-category');
        Route::post('/update-sub-category',[ProductManagementController::class,'updateSubCategory'])->name('admin.update-sub-category');
        Route::any('/deleteSubCategory/{id}', [ProductManagementController::class, 'deleteSubCategory'])->name('admin.deleteSubCategory');

        // Order Management Routes
        Route::get('/orders',[OrderManagementController::class,'orders'])->name('admin.orders');
        Route::get('/add-order',[OrderManagementController::class,'addOrder'])->name('admin.add-order');
        Route::post('/store-order',[OrderManagementController::class,'storeOrder'])->name('admin.store-order');
        Route::get('/edit-order/{id}',[OrderManagementController::class,'editOrder'])->name('admin.edit-order');
        Route::post('/update-order/{id}',[OrderManagementController::class,'updateOrder'])->name('admin.update-order');
        Route::any('/delete-order/{id}',[OrderManagementController::class,'deleteOrder'])->name('admin.delete-order');
        Route::get('/order-details/{id}',[OrderManagementController::class,'orderDetails'])->name('admin.order-details');
        Route::get('/order-tracking/{id}',[OrderManagementController::class,'orderTracking'])->name('admin.order-tracking');
        Route::get('/order-invoice-pdf/{id}',[OrderManagementController::class,'downloadOrderInvoicePdf'])->name('admin.order-invoice-pdf');
        Route::post('/update-order-status/{id}',[OrderManagementController::class,'updateOrderStatus'])->name('admin.update-order-status');
        Route::post('/add-order-tracking/{id}',[OrderManagementController::class,'addOrderTracking'])->name('admin.add-order-tracking');
        Route::post('/update-delivery-status/{id}',[OrderManagementController::class,'updateDeliveryStatus'])->name('admin.update-delivery-status');
        Route::post('/orders/{id}/assign-driver',[OrderManagementController::class,'assignDriver'])->name('admin.orders.assign-driver');
        Route::get('/orders/export-excel', [OrderManagementController::class, 'exportOrdersExcel'])->name('admin.orders.export-excel');
        Route::get('/orders/export-pdf', [OrderManagementController::class, 'exportOrdersPdf'])->name('admin.orders.export-pdf');

        // Customer Management Routes
        Route::get('/customers',[CustomerManagementController::class,'allCustomers'])->name('admin.customers');
        Route::get('/add-customer',[CustomerManagementController::class,'addCustomer'])->name('admin.add-customer');
        Route::post('/store-customer',[CustomerManagementController::class,'storeCustomer'])->name('admin.store-customer');
        Route::get('/view-customer/{id}',[CustomerManagementController::class,'viewCustomer'])->name('admin.view-customer');
        Route::get('/edit-customer/{id}',[CustomerManagementController::class,'editCustomer'])->name('admin.edit-customer');
        Route::post('/update-customer',[CustomerManagementController::class,'updateCustomer'])->name('admin.update-customer');
        Route::any('/delete-customer/{id}',[CustomerManagementController::class,'deleteCustomer'])->name('admin.delete-customer');
        Route::post('/toggle-customer-status/{id}',[CustomerManagementController::class,'toggleStatus'])->name('admin.customers.toggle-status');
        Route::post('/customers/{id}/approve-registration',[CustomerManagementController::class,'approveRegistration'])->name('admin.customers.approve-registration');
        Route::post('/customers/{id}/reject-registration',[CustomerManagementController::class,'rejectRegistration'])->name('admin.customers.reject-registration');
        Route::post('/customers/{id}/verify-gst',[CustomerManagementController::class,'verifyGst'])->name('admin.customers.verify-gst');
        Route::get('/customers/export-excel', [CustomerManagementController::class, 'exportCustomersExcel'])->name('admin.customers.export-excel');
        Route::get('/customers/export-pdf', [CustomerManagementController::class, 'exportCustomersPdf'])->name('admin.customers.export-pdf');

        // Vendor Management Routes
        Route::get('/vendors', [VendorManagementController::class, 'index'])->name('admin.vendors');
        Route::get('/add-vendor', [VendorManagementController::class, 'addVendor'])->name('admin.add-vendor');
        Route::post('/store-vendor', [VendorManagementController::class, 'storeVendor'])->name('admin.store-vendor');
        Route::get('/view-vendor/{id}', [VendorManagementController::class, 'viewVendor'])->name('admin.view-vendor');
        Route::get('/edit-vendor/{id}', [VendorManagementController::class, 'editVendor'])->name('admin.edit-vendor');
        Route::post('/update-vendor/{id}', [VendorManagementController::class, 'updateVendor'])->name('admin.update-vendor');
        Route::post('/vendors/{id}/approval-status', [VendorManagementController::class, 'updateApprovalStatus'])->name('admin.vendors.approval-status');
        Route::post('/vendors/{id}/commission', [VendorManagementController::class, 'updateCommission'])->name('admin.vendors.update-commission');
        Route::post('/vendors/{id}/toggle-block', [VendorManagementController::class, 'toggleBlock'])->name('admin.vendors.toggle-block');
        Route::any('/delete-vendor/{id}', [VendorManagementController::class, 'deleteVendor'])->name('admin.delete-vendor');
        Route::get('/vendors/export-excel', [VendorManagementController::class, 'exportVendorsExcel'])->name('admin.vendors.export-excel');

        // Delivery Management Routes
        Route::get('/delivery', [DeliveryManagementController::class, 'index'])->name('admin.delivery.index');
        Route::get('/delivery/add', [DeliveryManagementController::class, 'addDriver'])->name('admin.delivery.add');
        Route::post('/delivery/store', [DeliveryManagementController::class, 'storeDriver'])->name('admin.delivery.store');
        Route::get('/delivery/view/{id}', [DeliveryManagementController::class, 'viewDriver'])->name('admin.delivery.view');
        Route::get('/delivery/edit/{id}', [DeliveryManagementController::class, 'editDriver'])->name('admin.delivery.edit');
        Route::post('/delivery/update/{id}', [DeliveryManagementController::class, 'updateDriver'])->name('admin.delivery.update');
        Route::post('/delivery/delete/{id}', [DeliveryManagementController::class, 'deleteDriver'])->name('admin.delivery.delete');
        Route::post('/delivery/{id}/approval-status', [DeliveryManagementController::class, 'updateApprovalStatus'])->name('admin.delivery.approval-status');
        Route::post('/delivery/{id}/toggle-status', [DeliveryManagementController::class, 'toggleStatus'])->name('admin.delivery.toggle-status');
        Route::get('/delivery/export-excel', [DeliveryManagementController::class, 'exportDriversExcel'])->name('admin.delivery.export-excel');

        // Payment Management Routes
        Route::get('/payments/commission-settings', [PaymentEarningController::class, 'commissionSettings'])->name('admin.payments.commission-settings');
        Route::post('/payments/commission-settings/{vendorId}', [PaymentEarningController::class, 'updateCommissionPercentage'])->name('admin.payments.update-commission');
        Route::get('/payments/status', [PaymentEarningController::class, 'paymentStatusTracking'])->name('admin.payments.status');
        Route::get('/payments/vendor-transactions', [PaymentEarningController::class, 'vendorTransactions'])->name('admin.payments.vendor-transactions');
        Route::get('/payments/settlements', [PaymentEarningController::class, 'commissionSettlements'])->name('admin.payments.settlements');
        Route::get('/payments/settlements/{id}', [PaymentEarningController::class, 'commissionSettlementDetail'])->name('admin.payments.settlements.show');
        Route::post('/payments/settlements', [PaymentEarningController::class, 'storeCommissionSettlement'])->name('admin.payments.settlements.store');
        Route::post('/payments/settlements/{id}/status', [PaymentEarningController::class, 'updateCommissionSettlementStatus'])->name('admin.payments.settlements.update-status');

        Route::get('/reports/orders', [ReportsAnalyticsController::class, 'orderReports'])->name('admin.reports.orders');
        Route::get('/reports/revenue', [ReportsAnalyticsController::class, 'revenueReports'])->name('admin.reports.revenue');
        Route::get('/reports/recharges', [ReportsAnalyticsController::class, 'rechargeReports'])->name('admin.reports.recharges');
        Route::get('/reports/venue-bookings', [ReportsAnalyticsController::class, 'venueBookingReports'])->name('admin.reports.venue-bookings');
        Route::get('/reports/orders/export-excel', [ReportsAnalyticsController::class, 'exportOrderReportExcel'])->name('admin.reports.orders.export-excel');
        Route::get('/reports/orders/export-pdf', [ReportsAnalyticsController::class, 'exportOrderReportPdf'])->name('admin.reports.orders.export-pdf');

        // Static Pages Management
        Route::get('/static-pages', [AdminStaticPageController::class, 'index'])->name('admin.static-pages.index');
        Route::post('/static-pages/save', [AdminStaticPageController::class, 'saveByContext'])->name('admin.static-pages.save');
        Route::get('/static-pages/edit/{id}', [AdminStaticPageController::class, 'edit'])->name('admin.static-pages.edit');
        Route::post('/static-pages/update/{id}', [AdminStaticPageController::class, 'update'])->name('admin.static-pages.update');

        // Notification Management
        Route::get('/notifications', [NotificationManagementController::class, 'index'])->name('admin.notifications.index');
        Route::get('/notifications/recipients', [NotificationManagementController::class, 'recipients'])->name('admin.notifications.recipients');
        Route::post('/notifications/send', [NotificationManagementController::class, 'store'])->name('admin.notifications.store');

        // Ticket Management
        Route::get('/tickets', [TicketManagementController::class, 'index'])->name('admin.tickets.index');
        Route::get('/tickets/{id}', [TicketManagementController::class, 'show'])->name('admin.tickets.show');
        Route::post('/tickets/{id}/reply', [TicketManagementController::class, 'reply'])->name('admin.tickets.reply');
        Route::post('/tickets/{id}/update', [TicketManagementController::class, 'update'])->name('admin.tickets.update');
        Route::post('/tickets/{id}/internal-note', [TicketManagementController::class, 'internalNote'])->name('admin.tickets.internal-note');

        // Store Settings Management
        Route::get('/settings/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::post('/settings/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
        Route::get('/settings/store', [StoreSettingController::class, 'edit'])->name('admin.settings.store.edit');
        Route::post('/settings/store', [StoreSettingController::class, 'update'])->name('admin.settings.store.update');
        Route::get('/settings/payment-methods', [PaymentGatewayController::class, 'index'])->name('admin.settings.payment-methods');
        Route::post('/settings/payment-methods', [PaymentGatewayController::class, 'update'])->name('admin.settings.payment-methods.update');

        // Banner & Content Management
        Route::get('/banners', [BannerController::class, 'index'])->name('admin.banners.index');
        Route::get('/banners/create', [BannerController::class, 'create'])->name('admin.banners.create');
        Route::post('/banners/store', [BannerController::class, 'store'])->name('admin.banners.store');
        Route::get('/banners/edit/{id}', [BannerController::class, 'edit'])->name('admin.banners.edit');
        Route::post('/banners/update/{id}', [BannerController::class, 'update'])->name('admin.banners.update');
        Route::post('/banners/delete/{id}', [BannerController::class, 'delete'])->name('admin.banners.delete');

        // GST Tax Management
        Route::resource('gst-taxes', GstTaxController::class)->names('admin.gst-taxes');
        Route::post('/gst-taxes/{gst_tax}/toggle-status', [GstTaxController::class, 'toggleStatus'])->name('admin.gst-taxes.toggle-status');

        // Discount Offer Management
        Route::resource('discount-offers', DiscountOfferController::class)->names('admin.discount-offers');
        Route::post('/discount-offers/{discountOffer}/toggle-status', [DiscountOfferController::class, 'toggleStatus'])->name('admin.discount-offers.toggle-status');

    });

Route::middleware(['VendorAuth'])->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/products',[ProductManagementController::class,'products'])->name('products');
        Route::get('/add-product',[ProductManagementController::class,'addProduct'])->name('add-product');
        Route::post('/store-product',[ProductManagementController::class,'storeProduct'])->name('store-product');
        Route::get('/view-product/{id}',[ProductManagementController::class,'viewProduct'])->name('view-product');
        Route::get('/edit-product/{id}',[ProductManagementController::class,'editProduct'])->name('edit-product');
        Route::post('/update-product',[ProductManagementController::class,'updateProduct'])->name('update-product');
        Route::get('/delete-product/{id}',[ProductManagementController::class,'deleteProduct'])->name('delete-product');
        Route::post('/toggle-product-status/{id}',[ProductManagementController::class,'toggleStatus'])->name('products.toggle-status');

        Route::get('/orders',[OrderManagementController::class,'orders'])->name('orders');
        Route::get('/order-details/{id}',[OrderManagementController::class,'orderDetails'])->name('order-details');
        Route::post('/update-order-status/{id}',[OrderManagementController::class,'updateOrderStatus'])->name('update-order-status');

    });





