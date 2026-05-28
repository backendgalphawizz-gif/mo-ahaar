<!DOCTYPE html>
<html lang="en" dir="ltr">

<!-- Mirrored from themes.pixelstrap.com/fastkart/back-end/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 02 Apr 2026 08:04:38 GMT -->
 @include('layouts.head') 

<body class="{{ (int) (session('role_type') ?? 0) === 3 ? 'vendor-panel' : 'admin-panel' }}">
    @php
        $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
        $dashboardRoute = $isVendorPanel ? 'vendor.dashboard' : 'admin.dashboard';
        $defaultLogoUrl = asset('public/uploads/settings/moaahar-logo.png');
        $defaultAvatarUrl = asset('public/assets/images/logo/1.png');
        $logoUrl = !empty($globalStoreSetting) && !empty($globalStoreSetting->logo)
            ? asset('public/uploads/settings/' . $globalStoreSetting->logo)
            : $defaultLogoUrl;
        $profileImageUrl = session('profile_image')
            ? asset('public/uploads/admins/' . session('profile_image'))
            : $defaultAvatarUrl;
    @endphp
    @include('admin.partials.admin-figma-theme')
    <!-- tap on top start -->
    <div class="tap-top">
        <span class="lnr lnr-chevron-up"></span>
    </div>
    <!-- tap on tap end -->

    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        <div class="page-header">
            <div class="header-wrapper m-0">
                <div class="header-logo-wrapper p-0">
                    <div class="logo-wrapper">
                        <a href="{{ route($dashboardRoute) }}">
                            <img class="img-fluid main-logo" src="{{ $logoUrl }}" alt="logo" onerror="this.onerror=null;this.src='{{ $defaultLogoUrl }}';">
                            <img class="img-fluid white-logo" src="{{ $logoUrl }}" alt="logo" onerror="this.onerror=null;this.src='{{ $defaultLogoUrl }}';">
                        </a>
                    </div>
                    <div class="toggle-sidebar">
                        <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
                        <!-- <a href="{{ route($dashboardRoute) }}">
                            <img src="{{ $logoUrl }}" class="img-fluid" alt="logo">
                        </a> -->
                    </div>
                </div>

                @if(!$isVendorPanel)
                <form class="form-inline search-full" action="{{ route('admin.global-search') }}" method="get" data-suggest-url="{{ route('admin.global-search.suggestions') }}">
                    <div class="form-group w-100">
                        <div class="Typeahead Typeahead--twitterUsers">
                            <div class="u-posRelative">
                                <i class="ri-search-line"></i>
                                <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text"
                                    placeholder="Search here..." name="q" value="{{ request('q') }}" title="" autocomplete="off" autofocus>
                                <i class="close-search" data-feather="x"></i>
                                <div class="spinner-border Typeahead-spinner" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                            <div class="Typeahead-menu"></div>
                        </div>
                    </div>
                </form>
                @endif
                <div class="nav-right col-6 pull-right right-header p-0">
                    <ul class="nav-menus">
                        @if(!$isVendorPanel)
                        <li class="notification-box">
                            <a href="{{ route('admin.notifications.index') }}" title="Notifications">
                                <i class="ri-notification-3-line"></i>
                                <span class="dot"></span>
                            </a>
                        </li>
                        @endif
                        <li class="profile-nav onhover-dropdown pe-0 me-0">
                            <div class="media profile-media">
                                <img class="user-profile rounded-circle" src="{{ $profileImageUrl }}" alt="{{ session('name', 'Admin') }}" onerror="this.onerror=null;this.src='{{ $defaultAvatarUrl }}';">
                                <div class="user-name-hide media-body">
                                    <span>{{ session('name', 'Admin User') }}</span>
                                    <p class="mb-0 font-roboto">{{ $isVendorPanel ? 'Vendor' : 'Super Admin' }}</p>
                                </div>
                            </div>
                            <ul class="profile-dropdown onhover-show-div">
                                @if(!$isVendorPanel)
                                <li>
                                    <a href="{{ route('admin.customers') }}">
                                        <i data-feather="users"></i>
                                        <span>Customers</span>
                                    </a>
                                </li>
                                @endif
                                <li>
                                    <a href="{{ route($isVendorPanel ? 'vendor.orders' : 'admin.orders') }}">
                                        <i data-feather="archive"></i>
                                        <span>Orders</span>
                                    </a>
                                </li>
                                @if(!$isVendorPanel)
                                <li>
                                    <a href="{{ route('admin.notifications.index') }}">
                                        <i data-feather="bell"></i>
                                        <span>Notifications</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.tickets.index') }}">
                                        <i data-feather="message-circle"></i>
                                        <span>Support Tickets</span>
                                    </a>
                                </li>
                                @endif
                                <li>
                                    <a href="{{ route($isVendorPanel ? 'vendor.profile' : 'admin.profile.edit') }}">
                                        <i data-feather="settings"></i>
                                        <span>{{ $isVendorPanel ? 'Profile' : 'Settings' }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a data-bs-toggle="modal" data-bs-target="#staticBackdrop"
                                        href="javascript:void(0)">
                                        <i data-feather="log-out"></i>
                                        <span>Log out</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Page Header Ends-->

        <!-- Page Body Start-->
        <div class="page-body-wrapper">
          
            @include('layouts.sidebar')
                  
            @yield('content')
        
        </div>
   
    </div>
   

    <!-- Modal Start -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <h5 class="modal-title" id="staticBackdropLabel">Logging Out</h5>
                    <p>Are you sure you want to log out?</p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="button-box">
                        <button type="button" class="btn btn--no" data-bs-dismiss="modal">No</button>
                        <a href="{{ route('logout') }}" class="btn btn--yes btn-primary">Yes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
     @include('layouts.script') 
        @yield('scripts')
</body>
</html>