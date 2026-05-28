@php
    $userRole = auth()->user()->role_type ?? null;
@endphp

<div class="sidebar-wrapper">
    <div id="sidebarEffect"></div>
    <div>
        <div class="logo-wrapper logo-wrapper-center">
            <a href="{{ route('admin.dashboard') }}" data-bs-original-title="" title="">
                @php
                    $logoUrl = !empty($globalStoreSetting) && !empty($globalStoreSetting->logo)
                        ? asset('public/uploads/settings/' . $globalStoreSetting->logo)
                        : asset('public/assets/images/logo/full-white.png');
                @endphp
                <img class="img-fluid for-white" src="{{ $logoUrl }}" alt="logo">
            </a>
            <div class="back-btn ms-3">
                <i class="fa fa-angle-left"></i>
            </div>
            <div class="toggle-sidebar">
                <i class="ri-apps-line status_toggle middle sidebar-toggle"></i>
            </div>
        </div>
        <div class="logo-icon-wrapper">
            <a href="{{ route('admin.dashboard') }}">
                <img class="img-fluid main-logo main-white" src="{{ $logoUrl }}" alt="logo">
                <img class="img-fluid main-logo main-dark" src="{{ $logoUrl }}" alt="logo">
            </a>
        </div>
        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow">
                <i data-feather="arrow-left"></i>
            </div>

            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn"></li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.dashboard') }}">
                            <i class="ri-dashboard-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.customers') }}">
                            <i class="ri-user-3-line"></i>
                            <span>User Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.vendors') }}">
                            <i class="ri-store-3-line"></i>
                            <span>Vendor Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.delivery.index') }}">
                            <i class="ri-truck-line"></i>
                            <span>Delivery Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.orders') }}">
                            <i class="ri-shopping-cart-2-line"></i>
                            <span>Order Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-store-3-line"></i>
                            <span>Product Management</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{ route('admin.products') }}">Food List</a></li>
                            <li><a href="{{ route('admin.add-product') }}">Add Food</a></li>
                            <li><a href="{{ route('admin.categories') }}">Categories</a></li>
                            <li><a href="{{ route('admin.sub-category') }}">Sub Categories</a></li>
                            <li><a href="{{ route('admin.product-reviews') }}">Product Reviews</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.payments.status') }}">
                            <i class="ri-bank-card-line"></i>
                            <span>Payment Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.payments.settlements') }}">
                            <i class="ri-wallet-3-line"></i>
                            <span>Payment Requests</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.discount-offers.index') }}">
                            <i class="ri-coupon-3-line"></i>
                            <span>Promo Code</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.banners.index') }}">
                            <i class="ri-image-2-line"></i>
                            <span>Banner Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.reports.revenue') }}">
                            <i class="ri-bar-chart-box-line"></i>
                            <span>Reports & Analytics</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.notifications.index') }}">
                            <i class="ri-notification-3-line"></i>
                            <span>Notifications</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.static-pages.index') }}">
                            <i class="ri-file-list-3-line"></i>
                            <span>Static Pages</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.profile.edit') }}">
                            <i class="ri-user-settings-line"></i>
                            <span>Admin Profile</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('logout') }}">
                            <i class="ri-logout-box-r-line"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="right-arrow" id="right-arrow">
                <i data-feather="arrow-right"></i>
            </div>
        </nav>
    </div>
</div>

<style>
    #sidebar-menu .sidebar-submenu li a.active,
    #sidebar-menu .sidebar-link.sidebar-title.active,
    #sidebar-menu .sidebar-link.link-nav.active {
        color: #f7bf57 !important;
        font-weight: 600;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
        var links = Array.prototype.slice.call(
            document.querySelectorAll('#sidebar-menu .sidebar-submenu a, #sidebar-menu a.link-nav')
        );

        var matchedLink = null;
        var bestMatchLength = 0;

        links.forEach(function (link) {
            var href = link.getAttribute('href');
            if (!href || href === 'javascript:void(0)' || href.charAt(0) === '#') {
                return;
            }

            var linkPath = '';
            try {
                linkPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            } catch (e) {
                return;
            }

            if (currentPath === linkPath || (linkPath !== '/admin' && currentPath.startsWith(linkPath))) {
                if (linkPath.length >= bestMatchLength) {
                    matchedLink = link;
                    bestMatchLength = linkPath.length;
                }
            }
        });

        if (!matchedLink) {
            return;
        }

        matchedLink.classList.add('active');

        var submenu = matchedLink.closest('.sidebar-submenu');
        if (submenu) {
            submenu.style.display = 'block';

            var parentList = submenu.closest('.sidebar-list');
            if (parentList) {
                parentList.classList.add('active');
                var parentTitle = parentList.querySelector(':scope > .sidebar-link.sidebar-title');
                if (parentTitle) {
                    parentTitle.classList.add('active');
                }
            }
            return;
        }

        var singleMenu = matchedLink.closest('.sidebar-list');
        if (singleMenu) {
            singleMenu.classList.add('active');
        }
    });
</script>
