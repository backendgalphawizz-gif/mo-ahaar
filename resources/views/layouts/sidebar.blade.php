@php
    // Only two roles: 1 = Admin, 2 = Customer
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
            <a href="index.html">
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
                            <i class="ri-home-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.customers') }}">
                            <i class="ri-user-3-line"></i>
                            <span>Customer </span>
                        </a>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-store-3-line"></i>
                            <span>Products </span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li> <a href="{{ route('admin.products') }}">Products</a></li>
                            <li> <a href="{{ route('admin.add-product') }}">Add Products</a></li>
                            <li> <a href="{{ route('admin.categories') }}">Category List</a></li>
                            <li> <a href="{{ route('admin.sub-category') }}">Sub Category</a> </li>
                            <li> <a href="{{ route('admin.product-reviews') }}">Product Reviews</a> </li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-archive-line"></i>
                            <span>Orders </span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{ route('admin.orders') }}">Order List</a></li>
                            <li><a href="{{ route('admin.add-order') }}">Add Order</a></li>
                            <li><a href="{{ route('admin.reports.orders') }}">Order Reports</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="{{ route('admin.tickets.index') }}">
                            <i class="ri-customer-service-2-line"></i>
                            <span>Support Tickets</span>
                        </a>
                    </li>

                    <!-- <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="product-review.html">
                            <i class="ri-star-line"></i>
                            <span>Product Review</span>
                        </a>
                    </li> -->

                    <!-- <li class="sidebar-list">
                        <a class="sidebar-link sidebar-title link-nav" href="support-ticket.html">
                            <i class="ri-phone-line"></i>
                            <span>Support Ticket</span>
                        </a>
                    </li> -->

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-percent-line"></i>
                            <span>GST Taxes</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{ route('admin.gst-taxes.index') }}">All GST Taxes</a></li>
                            <li><a href="{{ route('admin.gst-taxes.create') }}">Add GST Tax</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-price-tag-3-line"></i>
                            <span>Discount Offers</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{ route('admin.discount-offers.index') }}">All Offers</a></li>
                            <li><a href="{{ route('admin.discount-offers.create') }}">Add Offer</a></li>
                        </ul>
                    </li>


                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-price-tag-3-line"></i>
                            <span>Discount Offers</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{ route('admin.discount-offers.index') }}">All Offers</a></li>
                            <li><a href="{{ route('admin.discount-offers.create') }}">Add Offer</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-settings-line"></i>
                            <span>Settings</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li>
                                <a href="{{ route('admin.profile.edit') }}">Profile Setting</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.settings.store.edit') }}">Store Settings</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.settings.payment-methods') }}">Payment Methods</a>
                            </li>
                            <li><a href="{{ route('admin.banners.index') }}">Banners Setting </a></li>
                        </ul>
                    </li>
                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-list-check"></i>
                            <span>Static Pages</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{ route('admin.static-pages.index') }}">All Pages</a></li>
                        </ul>
                    </li>

                    <li class="sidebar-list">
                        <a class="linear-icon-link sidebar-link sidebar-title" href="javascript:void(0)">
                            <i class="ri-notification-3-line"></i>
                            <span>Notification</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{ route('admin.notifications.index') }}">Send Notifications</a></li>
                        </ul>
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

        links.some(function (link) {
            var href = link.getAttribute('href');
            if (!href || href === 'javascript:void(0)' || href.charAt(0) === '#') {
                return false;
            }

            var linkPath = '';
            try {
                linkPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            } catch (e) {
                return false;
            }

            if (linkPath === currentPath) {
                matchedLink = link;
                return true;
            }

            return false;
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