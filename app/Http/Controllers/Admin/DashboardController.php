<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\Product;
use App\Models\Customers;
use App\Models\Users;
use App\Models\Vendor;
use App\Models\Venue;
use App\Models\MobileRecharge;
use App\Models\FastagRecharge;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private function isVendorPanel(): bool
    {
        return (int) session('role_type') === 3;
    }

    private function currentVendorId(): ?int
    {
        $vendorId = session('vendor_id');
        return $vendorId ? (int) $vendorId : null;
    }

    private function adminSearchLinks(): array
    {
        return [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'keywords' => ['dashboard', 'home']],
            ['route' => 'admin.customers', 'label' => 'User Management', 'keywords' => ['user management', 'users', 'customers', 'customer']],
            ['route' => 'admin.vendors', 'label' => 'Vendor Management', 'keywords' => ['vendor management', 'vendors', 'vendor']],
            ['route' => 'admin.add-vendor', 'label' => 'Add Vendor', 'keywords' => ['add vendor']],
            ['route' => 'admin.delivery.index', 'label' => 'Delivery Management', 'keywords' => ['delivery management', 'delivery', 'drivers']],
            ['route' => 'admin.products', 'label' => 'Products', 'keywords' => ['products manage', 'products', 'product']],
            ['route' => 'admin.add-product', 'label' => 'Add Product', 'keywords' => ['add products', 'add product']],
            ['route' => 'admin.categories', 'label' => 'Category List', 'keywords' => ['category list', 'categories', 'category']],
            ['route' => 'admin.sub-category', 'label' => 'Sub Category', 'keywords' => ['sub category']],
            ['route' => 'admin.orders', 'label' => 'Order List', 'keywords' => ['orders manage', 'order list', 'orders', 'order']],
            ['route' => 'admin.add-order', 'label' => 'Add Order', 'keywords' => ['add order']],
            ['route' => 'admin.payments.commission-settings', 'label' => 'Set Commission Percentage', 'keywords' => ['commission', 'set commission percentage']],
            ['route' => 'admin.payments.settlements', 'label' => 'Approve Payout Requests', 'keywords' => ['approve payout requests', 'payout', 'settlements', 'settlement']],
            ['route' => 'admin.payments.status', 'label' => 'Payment Status Tracking', 'keywords' => ['payment status', 'payments', 'earnings']],
            ['route' => 'admin.reports.orders', 'label' => 'Order Reports', 'keywords' => ['order reports']],
            ['route' => 'admin.reports.revenue', 'label' => 'Revenue Reports', 'keywords' => ['revenue reports', 'revenue report']],
            ['route' => 'admin.profile.edit', 'label' => 'Profile Setting', 'keywords' => ['profile setting', 'profile settings', 'profile']],
            ['route' => 'admin.settings.store.edit', 'label' => 'Store Settings', 'keywords' => ['store settings', 'store setting']],
            ['route' => 'admin.banners.index', 'label' => 'Banners Setting', 'keywords' => ['banners setting', 'banners', 'banner']],
            ['route' => 'admin.notifications.index', 'label' => 'Notifications', 'keywords' => ['notification management', 'send notifications', 'notifications']],
            ['route' => 'admin.discount-offers.index', 'label' => 'Promo Code', 'keywords' => ['promo code', 'discount offers', 'coupons']],
            ['route' => 'admin.static-pages.index', 'label' => 'Static Pages', 'keywords' => ['static pages', 'all pages']],
        ];
    }

    private function scoreAdminSearchLink(string $normalizedQuery, array $link): int
    {
        $score = 0;
        $normalizedLabel = strtolower((string) ($link['label'] ?? ''));

        if ($normalizedLabel === $normalizedQuery) {
            $score += 120;
        }

        if (str_contains($normalizedLabel, $normalizedQuery)) {
            $score += 80;
        }

        if (str_contains($normalizedQuery, $normalizedLabel)) {
            $score += 40;
        }

        foreach ($link['keywords'] ?? [] as $keyword) {
            $normalizedKeyword = strtolower((string) $keyword);

            if ($normalizedKeyword === $normalizedQuery) {
                $score += 100;
                continue;
            }

            if (str_contains($normalizedKeyword, $normalizedQuery) || str_contains($normalizedQuery, $normalizedKeyword)) {
                $score += 35;
            }
        }

        return $score;
    }

    private function iconForAdminRoute(string $routeName): string
    {
        if (str_starts_with($routeName, 'admin.vendors')) {
            return 'ri-store-2-line';
        }

        if (str_starts_with($routeName, 'admin.customers')) {
            return 'ri-user-3-line';
        }

        if (str_starts_with($routeName, 'admin.venues')) {
            return 'ri-store-3-line';
        }

        if (str_starts_with($routeName, 'admin.bookings')) {
            return 'ri-calendar-check-line';
        }

        if (str_starts_with($routeName, 'admin.products') || str_starts_with($routeName, 'admin.categories') || str_starts_with($routeName, 'admin.sub-')) {
            return 'ri-shopping-bag-3-line';
        }

        if (str_starts_with($routeName, 'admin.orders')) {
            return 'ri-archive-line';
        }

        if (str_starts_with($routeName, 'admin.mobile-recharge') || str_starts_with($routeName, 'admin.fastag') || str_starts_with($routeName, 'admin.gas-booking')) {
            return 'ri-price-tag-3-line';
        }

        if (str_starts_with($routeName, 'admin.payments')) {
            return 'ri-bank-card-line';
        }

        if (str_starts_with($routeName, 'admin.reports')) {
            return 'ri-bar-chart-box-line';
        }

        if (str_starts_with($routeName, 'admin.settings') || str_starts_with($routeName, 'admin.profile')) {
            return 'ri-settings-3-line';
        }

        if (str_starts_with($routeName, 'admin.banners')) {
            return 'ri-image-2-line';
        }

        if (str_starts_with($routeName, 'admin.static-pages')) {
            return 'ri-file-list-3-line';
        }

        if (str_starts_with($routeName, 'admin.notifications')) {
            return 'ri-notification-3-line';
        }

        if ($routeName === 'admin.dashboard') {
            return 'ri-home-5-line';
        }

        return 'ri-search-line';
    }

    public function searchSuggestions(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        if ($query === '') {
            return response()->json(['items' => []]);
        }

        $normalizedQuery = strtolower((string) preg_replace('/\s+/', ' ', $query));
        $items = [];

        foreach ($this->adminSearchLinks() as $link) {
            if (!Route::has($link['route'])) {
                continue;
            }

            $score = $this->scoreAdminSearchLink($normalizedQuery, $link);
            if ($score <= 0) {
                continue;
            }

            $items[] = [
                'label' => $link['label'],
                'url' => route($link['route']),
                'icon' => $this->iconForAdminRoute($link['route']),
                'score' => $score,
            ];
        }

        usort($items, function (array $a, array $b) {
            if ($a['score'] === $b['score']) {
                return strcmp($a['label'], $b['label']);
            }

            return $b['score'] <=> $a['score'];
        });

        $items = array_slice($items, 0, 8);
        $items = array_map(function (array $item) {
            unset($item['score']);
            return $item;
        }, $items);

        return response()->json(['items' => $items]);
    }

    public function globalSearch(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return redirect()->route('admin.dashboard');
        }

        $normalizedQuery = strtolower((string) preg_replace('/\s+/', ' ', $query));

        $bestMatchRoute = null;
        $bestMatchScore = -1;

        foreach ($this->adminSearchLinks() as $link) {
            if (!Route::has($link['route'])) {
                continue;
            }

            $score = $this->scoreAdminSearchLink($normalizedQuery, $link);
            if ($score > $bestMatchScore) {
                $bestMatchScore = $score;
                $bestMatchRoute = $link['route'];
            }
        }

        if (!empty($bestMatchRoute) && $bestMatchScore > 0) {
            return redirect()->route($bestMatchRoute);
        }

        return redirect()->route('admin.dashboard')
            ->with('error', 'No matching admin menu link found for "' . $query . '".');
    }

    public function index()
    {
        $totalUsers = 0;
        $totalVendors = 0;
        $activeVendors = 0;
        $activeUsers = 0;
        $pendingApprovals = 0;
        $totalProducts = 0;
        $totalOrders = 0;
        $totalRevenue = 0;
        $recentOrders = collect();
        $salesChartLabels = [];
        $salesChartData = [];

        try {
            if ($this->isVendorPanel()) {
                $vendorId = $this->currentVendorId();
                if (!$vendorId) {
                    return redirect('/vendor/login')->with('error', 'Vendor session is invalid. Please login again.');
                }

                if (Schema::hasTable('products')) {
                    $totalProducts = (int) Product::where('vendor_id', $vendorId)->count();
                }
                if (Schema::hasTable('orders')) {
                    $ordersQuery = Orders::where('vendor_id', $vendorId);
                    $totalOrders = (int) (clone $ordersQuery)->count();
                    $totalRevenue = (float) (clone $ordersQuery)->where('payment_status', 'paid')->sum('total_amount');
                    $recentOrders = (clone $ordersQuery)->orderByDesc('order_id')->limit(5)->get();
                }

                return view('admin.dashboard', compact(
                    'totalUsers',
                    'totalVendors',
                    'activeVendors',
                    'activeUsers',
                    'pendingApprovals',
                    'totalProducts',
                    'totalOrders',
                    'totalRevenue',
                    'recentOrders',
                    'salesChartLabels',
                    'salesChartData'
                ));
            }

            if (Schema::hasTable('customers')) {
                $totalUsers = (int) Customers::count();
            }
            if (Schema::hasTable('users')) {
                $activeUsers = (int) Users::where('role_type', Users::CUSTOMER_APP_ROLE_TYPE)->where('status', '1')->count();
            }
            if (Schema::hasTable('vendors')) {
                $totalVendors = (int) Vendor::count();
                $activeVendors = (int) Vendor::where('approval_status', 'approved')->count();
                $pendingApprovals = (int) Vendor::where('approval_status', 'pending')->count();
            }
            if (Schema::hasTable('products')) {
                $totalProducts = (int) Product::count();
            }
            if (Schema::hasTable('orders')) {
                $totalOrders = (int) Orders::count();
                $totalRevenue = (float) Orders::where('payment_status', 'paid')->sum('total_amount');
                $recentOrders = Orders::orderByDesc('order_id')->limit(5)->get();

                $monthlySales = Orders::selectRaw('MONTH(created_at) as month_num, SUM(total_amount) as total')
                    ->where('payment_status', 'paid')
                    ->whereYear('created_at', now()->year)
                    ->groupBy('month_num')
                    ->orderBy('month_num')
                    ->get()
                    ->keyBy('month_num');

                $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                for ($m = 1; $m <= 4; $m++) {
                    $salesChartLabels[] = $monthNames[$m - 1];
                    $salesChartData[] = (float) ($monthlySales[$m]->total ?? 0);
                }
            }
        } catch (\Exception $e) {
            $recentOrders = $recentOrders instanceof \Illuminate\Support\Collection ? $recentOrders : collect();
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalVendors',
            'activeVendors',
            'activeUsers',
            'pendingApprovals',
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'recentOrders',
            'salesChartLabels',
            'salesChartData'
        ));
    }
}