<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\CustomersController;
use App\Http\Controllers\API\CustomerApp\AddressController as CustomerAddressController;
use App\Http\Controllers\API\CustomerApp\AuthController as CustomerAuthController;
use App\Http\Controllers\API\CustomerApp\CartController as CustomerCartController;
use App\Http\Controllers\API\CustomerApp\CheckoutController as CustomerCheckoutController;
use App\Http\Controllers\API\CustomerApp\HomeController as CustomerHomeController;
use App\Http\Controllers\API\CustomerApp\LocationController as CustomerLocationController;
use App\Http\Controllers\API\CustomerApp\OrdersController as CustomerOrdersController;
use App\Http\Controllers\API\CustomerApp\NotificationController as CustomerNotificationController;
use App\Http\Controllers\API\CustomerApp\PaymentController as CustomerPaymentController;
use App\Http\Controllers\API\CustomerApp\ProfileController as CustomerProfileController;
use App\Http\Controllers\API\CustomerApp\ProductBrowsingController as CustomerProductBrowsingController;
use App\Http\Controllers\API\CustomerApp\ProductReviewController as CustomerProductReviewController;
use App\Http\Controllers\API\CustomerApp\VideoController as CustomerVideoController;
use App\Http\Controllers\API\CustomerApp\RatingsReviewController as CustomerRatingsReviewController;
use App\Http\Controllers\API\CustomerApp\TicketController as CustomerTicketController;
use App\Http\Controllers\API\CustomerApp\LanguageController as CustomerLanguageController;
use App\Http\Controllers\API\MobileAuthController;
use App\Http\Controllers\API\DriverApp\AuthController as DriverAuthController;
use App\Http\Controllers\API\DriverApp\DeliveryController as DriverDeliveryController;
use App\Http\Controllers\API\DriverApp\HomeController as DriverHomeController;
use App\Http\Controllers\API\DriverApp\NotificationController as DriverNotificationController;

Route::prefix('users')->group(function () {
    Route::get('/', [UsersController::class, 'index'])->name('users.index');
});

Route::prefix('customers')->group(function () {
    Route::get('/', [CustomersController::class, 'index'])->name('customers.index');
});

Route::prefix('orders')->group(function () {
    Route::get('/order-invoice/{id}', [CustomerOrdersController::class, 'downloadOrderInvoicePdf'])
        ->name('orders.invoice');
});

Route::prefix('customer-app')->middleware('set.customer.locale')->name('customer-app.')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/signup/request-otp', [CustomerAuthController::class, 'requestSignupOtp'])->name('signup.request-otp');
        Route::post('/signup/verify-otp', [CustomerAuthController::class, 'verifySignupOtp'])->name('signup.verify-otp');
        Route::post('/signup', [CustomerAuthController::class, 'signup'])->name('signup');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login');
        Route::get('/registration-content', [CustomerAuthController::class, 'registrationContent'])->name('registration-content');
        Route::post('/request-otp', [CustomerAuthController::class, 'requestOtp'])->name('request-otp');
        Route::post('/verify-otp', [CustomerAuthController::class, 'verify'])->name('verify-otp');
    });

    Route::prefix('language')->group(function () {
        Route::get('/supported', [CustomerLanguageController::class, 'supported'])->name('language.supported');
    });

    Route::middleware(['inject.bearer', 'auth:sanctum'])->prefix('auth')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });

    Route::middleware('auth:sanctum')->prefix('language')->group(function () {
        Route::get('/current', [CustomerLanguageController::class, 'current'])->name('language.current');
        Route::post('/update', [CustomerLanguageController::class, 'update'])->name('language.update');
    });

    Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
        Route::get('/', [CustomerProfileController::class, 'show'])->name('profile.show');
        Route::post('/update', [CustomerProfileController::class, 'update'])->name('profile.update');
    });

    Route::middleware('auth:sanctum')->prefix('addresses')->group(function () {
        Route::get('/', [CustomerAddressController::class, 'index'])->name('addresses.index');
        Route::post('/', [CustomerAddressController::class, 'store'])->name('addresses.store');
        Route::get('/{addressId}', [CustomerAddressController::class, 'show'])->name('addresses.show');
        Route::post('/{addressId}', [CustomerAddressController::class, 'update'])->name('addresses.update');
        Route::delete('/{addressId}', [CustomerAddressController::class, 'destroy'])->name('addresses.destroy');
    });

    Route::middleware('auth:sanctum')->prefix('home')->group(function () {
        Route::get('/dashboard', [CustomerHomeController::class, 'dashboard'])->name('home.dashboard');
        /** Same payload as dashboard — home screen (banners + featured products). */
        Route::get('/screen', [CustomerHomeController::class, 'dashboard'])->name('home.screen');
    });

    Route::middleware('auth:sanctum')->prefix('location')->group(function () {
        Route::post('/enable', [CustomerLocationController::class, 'enable'])->name('location.enable');
        Route::get('/nearby-products', [CustomerLocationController::class, 'nearbyProducts'])->name('location.nearby-products');
    });

    Route::middleware('auth:sanctum')->prefix('videos')->group(function () {
        Route::get('/feed', [CustomerVideoController::class, 'feed'])->name('videos.feed');
        Route::post('/like', [CustomerVideoController::class, 'toggleLike'])->name('videos.like');
        Route::post('/share', [CustomerVideoController::class, 'share'])->name('videos.share');
    });

    Route::middleware('auth:sanctum')->prefix('products')->group(function () {
        Route::get('/search', [CustomerProductBrowsingController::class, 'search'])->name('products.search');
        Route::get('/categories', [CustomerProductBrowsingController::class, 'categories'])->name('products.categories');
        // Category details by category id
        Route::get('/categories/{categoryId}', [CustomerProductBrowsingController::class, 'categoryDetails'])->name('products.category-details');
        // Sub-category details by sub-category id
        Route::get('/sub-categories/{subCategoryId}', [CustomerProductBrowsingController::class, 'subCategoryDetails'])->name('products.sub-category-details');
        Route::get('/by-user-type', [CustomerProductBrowsingController::class, 'productsByUserType'])->name('products.by-user-type');
        Route::get('/detail/{productId}', [CustomerProductBrowsingController::class, 'productDetail'])->name('products.detail');
    });

    Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
        Route::get('/history', [CustomerOrdersController::class, 'history'])->name('orders.history');
        Route::get('/', [CustomerOrdersController::class, 'index'])->name('orders.index');
        Route::get('/{orderId}', [CustomerOrdersController::class, 'show'])->name('orders.show');
        Route::get('/{orderId}/tracking', [CustomerOrdersController::class, 'tracking'])->name('orders.tracking');
        Route::post('/{orderId}/cancel', [CustomerOrdersController::class, 'cancel'])->name('orders.cancel');

    });

    Route::middleware('auth:sanctum')->prefix('reviews')->group(function () {
        Route::post('/products/{productId}', [CustomerProductReviewController::class, 'store'])->name('reviews.products.store');
        Route::get('/products/{productId}', [CustomerProductReviewController::class, 'index'])->name('reviews.products.index');
        Route::get('/my', [CustomerProductReviewController::class, 'myReviews'])->name('reviews.my');
    });

    Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
        Route::get('/', [CustomerNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [CustomerNotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/{notificationId}/read', [CustomerNotificationController::class, 'markRead'])->name('notifications.mark-read');
        Route::post('/read-all', [CustomerNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    });

    Route::middleware('auth:sanctum')->prefix('tickets')->group(function () {
        Route::get('/', [CustomerTicketController::class, 'index'])->name('tickets.index');
        Route::post('/', [CustomerTicketController::class, 'store'])->name('tickets.store');
        Route::post('/{ticketId}/reply', [CustomerTicketController::class, 'reply'])->name('tickets.reply');
    });

    Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
        Route::get('/', [CustomerCartController::class, 'index'])->name('cart.index');
        Route::post('/add', [CustomerCartController::class, 'add'])->name('cart.add');
        Route::post('/update', [CustomerCartController::class, 'update'])->name('cart.update');
        Route::post('/remove', [CustomerCartController::class, 'remove'])->name('cart.remove');
        Route::post('/clear', [CustomerCartController::class, 'clear'])->name('cart.clear');
    });

    Route::middleware('auth:sanctum')->prefix('checkout')->group(function () {
        Route::get('/summary', [CustomerCheckoutController::class, 'summary'])->name('checkout.summary');
        Route::post('/create-order', [CustomerCheckoutController::class, 'createOrder'])->name('checkout.create-order');
        Route::post('/place-order', [CustomerCheckoutController::class, 'placeOrder'])->name('checkout.place-order');
    });

    Route::middleware('auth:sanctum')->prefix('payment')->group(function () {
        Route::post('/razorpay/create-order', [CustomerPaymentController::class, 'createRazorpayOrder'])->name('payment.razorpay.create-order');
        Route::post('/razorpay/verify', [CustomerPaymentController::class, 'verifyRazorpayPayment'])->name('payment.razorpay.verify');
        Route::post('/update-status', [CustomerPaymentController::class, 'updatePaymentStatus'])->name('payment.update-status');
    });

    Route::middleware('auth:sanctum')->prefix('feedback')->group(function () {
        Route::post('/', [CustomerRatingsReviewController::class, 'submitFeedback'])->name('feedback.submit');
        Route::get('/', [CustomerRatingsReviewController::class, 'getFeedback'])->name('feedback.index');
    });
});

Route::prefix('mobile-auth')->group(function () {
    Route::post('/request-otp', [MobileAuthController::class, 'requestOtp'])->name('mobile-auth.request-otp');
    Route::post('/verify-otp', [MobileAuthController::class, 'verifyOtp'])->name('mobile-auth.verify-otp');
});

Route::prefix('user-app')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login'])->name('user-app.login');
    Route::post('/verify-otp', [MobileAuthController::class, 'verify'])->name('user-app.verify-otp');
});

Route::prefix('driver-app')->name('driver-app.')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', [DriverAuthController::class, 'login'])->name('auth.login');
        Route::post('/forgot-password/request-otp', [DriverAuthController::class, 'requestForgotPasswordOtp'])->name('auth.forgot-password.request-otp');
        Route::post('/forgot-password/verify-otp', [DriverAuthController::class, 'verifyForgotPasswordOtp'])->name('auth.forgot-password.verify-otp');
        Route::post('/forgot-password/resend-otp', [DriverAuthController::class, 'resendForgotPasswordOtp'])->name('auth.forgot-password.resend-otp');
        Route::post('/forgot-password/reset', [DriverAuthController::class, 'resetPassword'])->name('auth.forgot-password.reset');
    });

    Route::middleware(['inject.bearer', 'auth:sanctum', 'driver'])->group(function () {
        Route::post('/auth/logout', [DriverAuthController::class, 'logout'])->name('auth.logout');

        Route::prefix('home')->group(function () {
            Route::get('/dashboard', [DriverHomeController::class, 'dashboard'])->name('home.dashboard');
            Route::get('/new-deliveries', [DriverHomeController::class, 'newDeliveries'])->name('home.new-deliveries');
        });

        Route::prefix('deliveries')->group(function () {
            Route::post('/{assignmentId}/accept', [DriverDeliveryController::class, 'accept'])->name('deliveries.accept');
            Route::post('/{assignmentId}/reject', [DriverDeliveryController::class, 'reject'])->name('deliveries.reject');
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [DriverNotificationController::class, 'index'])->name('notifications.index');
            Route::post('/{notificationId}/read', [DriverNotificationController::class, 'markRead'])->name('notifications.mark-read');
        });
    });
});
