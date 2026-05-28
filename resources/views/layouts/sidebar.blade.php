@php
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
    $defaultLogoUrl = asset('public/uploads/settings/moaahar-logo.png');
    $logoUrl = !empty($globalStoreSetting) && !empty($globalStoreSetting->logo)
        ? asset('public/uploads/settings/' . $globalStoreSetting->logo)
        : $defaultLogoUrl;

    $pathMatch = function (string $pattern) {
        $path = request()->path();
        if ($pattern === $path) {
            return true;
        }
        return str_starts_with($path, rtrim($pattern, '/') . '/');
    };

    $isActive = function (array $patterns) use ($pathMatch) {
        foreach ($patterns as $pattern) {
            if ($pathMatch($pattern)) {
                return true;
            }
        }
        return false;
    };

    $userMenuOpen = $isActive(['admin/customers', 'admin/add-customer', 'admin/view-customer', 'admin/edit-customer', 'admin/customers/transactions']);
    $restaurantMenuOpen = $isActive(['admin/vendors', 'admin/add-vendor', 'admin/view-vendor', 'admin/edit-vendor', 'admin/payments/settlements']);
    $deliveryMenuOpen = $isActive(['admin/delivery', 'admin/delivery/wallet-transactions']);
    $productMenuOpen = $isActive(['admin/products', 'admin/add-product', 'admin/edit-product', 'admin/view-product', 'admin/categories', 'admin/sub-category', 'admin/add-category', 'admin/edit-category']);
@endphp

<div class="sidebar-wrapper moa-sidebar">
    <div id="sidebarEffect"></div>
    <div>
        <div class="logo-wrapper logo-wrapper-center">
            <a href="{{ route($isVendorPanel ? 'vendor.dashboard' : 'admin.dashboard') }}" title="">
                <img class="img-fluid for-white" src="{{ $logoUrl }}" alt="moaahar" onerror="this.onerror=null;this.src='{{ $defaultLogoUrl }}';">
            </a>
            <div class="back-btn ms-3"><i class="fa fa-angle-left"></i></div>
            <div class="toggle-sidebar">
                <i class="ri-apps-line status_toggle middle sidebar-toggle"></i>
            </div>
        </div>
        <nav class="sidebar-main">
            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn"></li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['admin/dashboard']) ? 'active' : '' }}"
                           href="{{ route($isVendorPanel ? 'vendor.dashboard' : 'admin.dashboard') }}">
                            <i class="ri-dashboard-line"></i><span>Dashboard</span>
                        </a>
                    </li>

                    @if(!$isVendorPanel)
                    <li class="sidebar-list {{ $userMenuOpen ? 'submenu-open' : '' }}">
                        <a class="sidebar-link sidebar-title has-submenu {{ $userMenuOpen ? 'open' : '' }}" href="javascript:void(0)">
                            <i class="ri-user-3-line"></i><span>User Management</span>
                            <i class="ri-arrow-down-s-line submenu-arrow"></i>
                        </a>
                        <ul class="sidebar-submenu" @if($userMenuOpen) style="display:block" @endif>
                            <li><a href="{{ route('admin.customers') }}" class="{{ $isActive(['admin/customers', 'admin/view-customer', 'admin/edit-customer', 'admin/add-customer']) && !$isActive(['admin/customers/transactions']) ? 'active' : '' }}">View Users</a></li>
                            <li><a href="{{ route('admin.customers.transactions') }}" class="{{ $isActive(['admin/customers/transactions']) ? 'active' : '' }}">Transactions</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list {{ $restaurantMenuOpen ? 'submenu-open' : '' }}">
                        <a class="sidebar-link sidebar-title has-submenu {{ $restaurantMenuOpen ? 'open' : '' }}" href="javascript:void(0)">
                            <i class="ri-store-2-line"></i><span>Restaurant Management</span>
                            <i class="ri-arrow-down-s-line submenu-arrow"></i>
                        </a>
                        <ul class="sidebar-submenu" @if($restaurantMenuOpen) style="display:block" @endif>
                            <li><a href="{{ route('admin.vendors') }}" class="{{ $isActive(['admin/vendors', 'admin/add-vendor', 'admin/view-vendor', 'admin/edit-vendor']) ? 'active' : '' }}">View Restaurants</a></li>
                            <li><a href="{{ route('admin.payments.settlements') }}" class="{{ $isActive(['admin/payments/settlements']) ? 'active' : '' }}">Payment Request</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list {{ $deliveryMenuOpen ? 'submenu-open' : '' }}">
                        <a class="sidebar-link sidebar-title has-submenu {{ $deliveryMenuOpen ? 'open' : '' }}" href="javascript:void(0)">
                            <i class="ri-truck-line"></i><span>Delivery Management</span>
                            <i class="ri-arrow-down-s-line submenu-arrow"></i>
                        </a>
                        <ul class="sidebar-submenu" @if($deliveryMenuOpen) style="display:block" @endif>
                            <li><a href="{{ route('admin.delivery.index') }}" class="{{ $isActive(['admin/delivery']) && !$isActive(['admin/delivery/wallet-transactions']) ? 'active' : '' }}">View Drivers</a></li>
                            <li><a href="{{ route('admin.delivery.wallet-transactions') }}" class="{{ $isActive(['admin/delivery/wallet-transactions']) ? 'active' : '' }}">Wallet Transactions</a></li>
                        </ul>
                    </li>
                    @endif

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['admin/orders', 'vendor/orders']) ? 'active' : '' }}"
                           href="{{ route($isVendorPanel ? 'vendor.orders' : 'admin.orders') }}">
                            <i class="ri-shopping-cart-2-line"></i><span>Order Management</span>
                        </a>
                    </li>

                    @if($isVendorPanel)
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('vendor.payments') }}"><i class="ri-bank-card-line"></i><span>Payments</span></a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('vendor.profile') }}"><i class="ri-user-3-line"></i><span>Profile</span></a>
                    </li>
                    @endif

                    <li class="sidebar-list {{ $productMenuOpen ? 'submenu-open' : '' }}">
                        @if($isVendorPanel)
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['vendor/products']) ? 'active' : '' }}" href="{{ route('vendor.products') }}">
                            <i class="ri-restaurant-line"></i><span>Food Management</span>
                        </a>
                        @else
                        <a class="sidebar-link sidebar-title has-submenu {{ $productMenuOpen ? 'open' : '' }}" href="javascript:void(0)">
                            <i class="ri-restaurant-line"></i><span>Product Management</span>
                            <i class="ri-arrow-down-s-line submenu-arrow"></i>
                        </a>
                        <ul class="sidebar-submenu" @if($productMenuOpen) style="display:block" @endif>
                            <li><a href="{{ route('admin.categories') }}" class="{{ $isActive(['admin/categories', 'admin/add-category', 'admin/edit-category']) ? 'active' : '' }}">Categories</a></li>
                            <li><a href="{{ route('admin.products') }}" class="{{ $isActive(['admin/products', 'admin/add-product', 'admin/edit-product', 'admin/view-product']) ? 'active' : '' }}">Products</a></li>
                            <li><a href="{{ route('admin.sub-category') }}" class="{{ $isActive(['admin/sub-category']) ? 'active' : '' }}">Sub Category</a></li>
                        </ul>
                        @endif
                    </li>

                    @if(!$isVendorPanel)
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['admin/payments/status']) ? 'active' : '' }}" href="{{ route('admin.payments.status') }}">
                            <i class="ri-bank-card-line"></i><span>Payment Management</span>
                        </a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['admin/discount-offers']) ? 'active' : '' }}" href="{{ route('admin.discount-offers.index') }}">
                            <i class="ri-coupon-3-line"></i><span>Promo Code</span>
                        </a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['admin/banners']) ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">
                            <i class="ri-image-2-line"></i><span>Banner Management</span>
                        </a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['admin/reports']) ? 'active' : '' }}" href="{{ route('admin.reports.revenue') }}">
                            <i class="ri-bar-chart-box-line"></i><span>Reports &amp; Analytics</span>
                        </a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['admin/notifications']) ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                            <i class="ri-notification-3-line"></i><span>Notifications</span>
                        </a>
                    </li>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav {{ $isActive(['admin/static-pages']) ? 'active' : '' }}" href="{{ route('admin.static-pages.index') }}">
                            <i class="ri-file-list-3-line"></i><span>Static Pages</span>
                        </a>
                    </li>
                    @else
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('vendor.addons.index') }}">
                            <i class="ri-file-list-3-line"></i><span>Add On Lists</span>
                        </a>
                    </li>
                    @endif

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('logout') }}">
                            <i class="ri-logout-box-r-line"></i><span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

<style>
.moa-sidebar { background: #fff !important; border-right: 1px solid #eceef2; }
.moa-sidebar .logo-wrapper-center { min-height: 60px; padding: 12px 14px !important; border-bottom: 1px solid #f1f3f5; }
.moa-sidebar .logo-wrapper-center .for-white { max-height: 32px; width: auto; }
.moa-sidebar #sidebar-menu .sidebar-list { margin: 2px 10px; }
.moa-sidebar #sidebar-menu .sidebar-link {
    border-radius: 8px; padding: 9px 12px !important; color: #374151 !important;
    font-size: 13px; font-weight: 500; min-height: 40px; display: flex !important; align-items: center;
    text-decoration: none;
}
.moa-sidebar #sidebar-menu .sidebar-link > i:first-of-type { font-size: 18px; margin-right: 10px; color: #4b5563 !important; min-width: 20px; }
.moa-sidebar #sidebar-menu .sidebar-link .submenu-arrow { margin-left: auto; font-size: 16px; color: #9ca3af; transition: transform .2s; }
.moa-sidebar #sidebar-menu .sidebar-link.has-submenu.open .submenu-arrow { transform: rotate(180deg); color: #ed1c24; }

.moa-sidebar #sidebar-menu .sidebar-submenu { display: none; list-style: none; margin: 4px 0 6px; padding: 4px 0 4px 12px; border-left: 2px solid #f3f4f6; }
.moa-sidebar #sidebar-menu .sidebar-submenu li a {
    display: block; padding: 8px 12px; margin: 2px 0; font-size: 12px; font-weight: 500;
    color: #6b7280 !important; border-radius: 6px; text-decoration: none;
}
.moa-sidebar #sidebar-menu .sidebar-submenu li a:hover { background: #f9fafb; color: #111827 !important; }
.moa-sidebar #sidebar-menu .sidebar-submenu li a.active {
    color: #ed1c24 !important; background: #fef2f2; font-weight: 600;
    border-left: 3px solid #ed1c24; padding-left: 9px;
}

.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active {
    background: #ed1c24 !important; color: #fff !important; font-weight: 600;
}
.moa-sidebar #sidebar-menu .sidebar-link.link-nav.active > i { color: #fff !important; }

.moa-sidebar #sidebar-menu .sidebar-list.submenu-open > .sidebar-link.has-submenu.open {
    background: #fef2f2 !important; color: #ed1c24 !important; font-weight: 600;
}
.moa-sidebar #sidebar-menu .sidebar-list.submenu-open > .sidebar-link.has-submenu.open > i:first-of-type { color: #ed1c24 !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.moa-sidebar .sidebar-link.has-submenu').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var list = this.closest('.sidebar-list');
            if (!list) return;
            var submenu = list.querySelector('.sidebar-submenu');
            var willOpen = !list.classList.contains('submenu-open');
            document.querySelectorAll('.moa-sidebar .sidebar-list.submenu-open').forEach(function (item) {
                if (item === list) return;
                item.classList.remove('submenu-open');
                var link = item.querySelector('.has-submenu');
                if (link) link.classList.remove('open');
                var sm = item.querySelector('.sidebar-submenu');
                if (sm) sm.style.display = 'none';
            });
            list.classList.toggle('submenu-open', willOpen);
            this.classList.toggle('open', willOpen);
            if (submenu) submenu.style.display = willOpen ? 'block' : 'none';
        });
    });
});
</script>
