<?php
    $userRole = auth()->user()->role_type ?? null;
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
?>

<div class="sidebar-wrapper">
    <div id="sidebarEffect"></div>
    <div>
        <div class="logo-wrapper logo-wrapper-center">
            <a href="<?php echo e(route($isVendorPanel ? 'vendor.dashboard' : 'admin.dashboard')); ?>" data-bs-original-title="" title="">
                <?php
                    $logoUrl = !empty($globalStoreSetting) && !empty($globalStoreSetting->logo)
                        ? asset('public/uploads/settings/' . $globalStoreSetting->logo)
                        : asset('public/uploads/settings/moaahar-logo.png');
                ?>
                <img class="img-fluid for-white" src="<?php echo e($logoUrl); ?>" alt="logo">
            </a>
            <div class="back-btn ms-3">
                <i class="fa fa-angle-left"></i>
            </div>
            <div class="toggle-sidebar">
                <i class="ri-apps-line status_toggle middle sidebar-toggle"></i>
            </div>
        </div>
        <div class="logo-icon-wrapper">
            <a href="<?php echo e(route($isVendorPanel ? 'vendor.dashboard' : 'admin.dashboard')); ?>">
                <img class="img-fluid main-logo main-white" src="<?php echo e($logoUrl); ?>" alt="logo">
                <img class="img-fluid main-logo main-dark" src="<?php echo e($logoUrl); ?>" alt="logo">
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
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route($isVendorPanel ? 'vendor.dashboard' : 'admin.dashboard')); ?>">
                            <i class="ri-dashboard-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <?php if(!$isVendorPanel): ?>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.customers')); ?>">
                            <i class="ri-user-3-line"></i>
                            <span>User Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.vendors')); ?>">
                            <i class="ri-store-3-line"></i>
                            <span>Vendor Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.delivery.index')); ?>">
                            <i class="ri-truck-line"></i>
                            <span>Delivery Management</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route($isVendorPanel ? 'vendor.orders' : 'admin.orders')); ?>">
                            <i class="ri-shopping-cart-2-line"></i>
                            <span><?php echo e($isVendorPanel ? 'Orders' : 'Order Management'); ?></span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route($isVendorPanel ? 'vendor.products' : 'admin.products')); ?>">
                            <i class="ri-store-3-line"></i>
                            <span><?php echo e($isVendorPanel ? 'Food Management' : 'Product Management'); ?></span>
                        </a>
                    </li>

                    <?php if(!$isVendorPanel): ?>
                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.payments.status')); ?>">
                            <i class="ri-bank-card-line"></i>
                            <span>Payment Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.payments.settlements')); ?>">
                            <i class="ri-wallet-3-line"></i>
                            <span>Payment Requests</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.discount-offers.index')); ?>">
                            <i class="ri-coupon-3-line"></i>
                            <span>Promo Code</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.banners.index')); ?>">
                            <i class="ri-image-2-line"></i>
                            <span>Banner Management</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.reports.revenue')); ?>">
                            <i class="ri-bar-chart-box-line"></i>
                            <span>Reports & Analytics</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.notifications.index')); ?>">
                            <i class="ri-notification-3-line"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e($isVendorPanel ? route('vendor.dashboard') : route('admin.static-pages.index')); ?>">
                            <i class="ri-file-list-3-line"></i>
                            <span><?php echo e($isVendorPanel ? 'Pages' : 'Static Pages'); ?></span>
                        </a>
                    </li>

                    

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('logout')); ?>">
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
    .sidebar-wrapper { background: #ffffff; border-right: 1px solid #eceef2; width: 210px; }
    .logo-wrapper-center { min-height: 56px; padding: 8px 10px !important; }
    .logo-wrapper-center .for-white { max-height: 28px; width: auto; }
    .logo-icon-wrapper { display: none; }
    #sidebar-menu .sidebar-list { margin: 1px 8px; }
    #sidebar-menu .sidebar-link.link-nav {
        border-radius: 6px;
        padding: 7px 9px !important;
        color: #212529 !important;
        font-size: 11px;
        font-weight: 500;
        min-height: 30px;
        display: flex !important;
        align-items: center;
    }
    #sidebar-menu .sidebar-link.link-nav i { color: #111827 !important; font-size: 13px; margin-right: 7px; }
    #sidebar-menu .sidebar-submenu li a.active,
    #sidebar-menu .sidebar-link.sidebar-title.active,
    #sidebar-menu .sidebar-link.link-nav.active {
        background: #ed1c24 !important;
        color: #ffffff !important;
        font-weight: 600;
    }
    #sidebar-menu .sidebar-link.link-nav.active i { color: #ffffff !important; }
    #sidebar-menu .sidebar-list:hover .sidebar-link.link-nav:not(.active) {
        background: #f6f7f9;
        color: #111827 !important;
    }
    #sidebar-menu .sidebar-link.link-nav span { line-height: 1.1; }
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
<?php /**PATH C:\xampp\htdocs\Projects\mo-ahaar\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>