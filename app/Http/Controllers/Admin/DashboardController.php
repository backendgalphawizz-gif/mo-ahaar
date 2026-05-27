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
use App\Models\Vendor;
use App\Models\Venue;
use App\Models\MobileRecharge;
use App\Models\FastagRecharge;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private function adminSearchLinks(): array
    {
        return [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'keywords' => ['dashboard', 'home']],
            ['route' => 'admin.customers', 'label' => 'All Customers', 'keywords' => ['customer management', 'all customers', 'customers', 'customer']],
            ['route' => 'admin.add-customer', 'label' => 'Add Customer', 'keywords' => ['add customer']],
            ['route' => 'admin.bookings.index', 'label' => 'All Bookings', 'keywords' => ['booking management', 'all bookings', 'bookings', 'booking']],
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
            ['route' => 'admin.static-pages.index', 'label' => 'Static Pages', 'keywords' => ['static pages', 'all pages']],
            ['route' => 'admin.notifications.index', 'label' => 'Send Notifications', 'keywords' => ['notification management', 'send notifications', 'notifications']],
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
        $totalProducts = 0;
        $totalOrders = 0;
        $totalRevenue = 0;
        $recentOrders = collect();
        $ordersTrendLabels = [];
        $ordersTrendData = [];
        $revenueTrendData = [];

        try {
            if (Schema::hasTable('customers')) {
                $totalUsers = (int) Customers::count();
            }
            if (Schema::hasTable('products')) {
                $totalProducts = (int) Product::count();
            }
            if (Schema::hasTable('orders')) {
                $totalOrders = (int) Orders::count();
                $totalRevenue = (float) Orders::sum('total_amount');
                $recentOrders = Orders::orderByDesc('order_id')->limit(5)->get();
            }
        } catch (\Exception $e) {
            // Keep dashboard usable even if some tables are missing/incomplete.
            $categories = $categories instanceof Collection ? $categories : collect();
            $bestSellingProducts = $bestSellingProducts instanceof Collection ? $bestSellingProducts : collect();
            $recentOrders = $recentOrders instanceof Collection ? $recentOrders : collect();
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalProducts',
            'totalOrders',
            'totalRevenue',
            'recentOrders'
        ));
    }
}