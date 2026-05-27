<head>
    @php
        $storeSetting = $globalStoreSetting ?? null;
        $siteTitleSetting = !empty($storeSetting) && !empty($storeSetting->site_title) ? $storeSetting->site_title : null;
        $appNameSetting = !empty($storeSetting) && !empty($storeSetting->app_name) ? $storeSetting->app_name : null;
        $metaDescriptionSetting = !empty($storeSetting) && !empty($storeSetting->meta_description) ? $storeSetting->meta_description : null;
        $metaKeywordsSetting = !empty($storeSetting) && !empty($storeSetting->meta_keywords) ? $storeSetting->meta_keywords : null;
        $siteName = trim((string) ($siteTitleSetting ?? $appNameSetting ?? config('app.name', 'Ecommerce')));
        $pageTitle = trim((string) ($title ?? 'Dashboard'));
        $metaDescription = trim((string) ($metaDescriptionSetting ?? 'Fastkart admin is super flexible, powerful, clean and modern responsive bootstrap 5 admin template with unlimited possibilities.'));
        $metaKeywords = trim((string) ($metaKeywordsSetting ?? 'admin template, Fastkart admin template, dashboard template, flat admin template, responsive admin template, web app'));
        $faviconUrl = !empty($storeSetting) && !empty($storeSetting->favicon)
            ? asset('public/uploads/settings/' . $storeSetting->favicon)
            : asset('public/assets/images/favicon.png');
        $faviconVersion = !empty($storeSetting) && !empty($storeSetting->updated_at)
            ? $storeSetting->updated_at->timestamp
            : '1';
        $documentTitle = $pageTitle !== '' && strcasecmp($pageTitle, $siteName) !== 0
            ? $pageTitle . ' | ' . $siteName
            : $siteName;
    @endphp
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="author" content="{{ $siteName }}">
    <link rel="icon" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
    <title>{{ $documentTitle }}</title>

    <!-- Google font-->
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        rel="stylesheet">

    <!-- Linear Icon css -->
    <link rel="stylesheet" href="{{ asset('public/assets/css/linearicon.css') }}">

    <!-- fontawesome css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/vendors/font-awesome.css') }}">

    <!-- Themify icon css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/vendors/themify.css') }}">

    <!-- ratio css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/ratio.css') }}">

    <!-- remixicon css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/remixicon.css') }}">

    <!-- Feather icon css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/vendors/feather-icon.css') }}">

    <!-- Plugins css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/vendors/animate.css') }}">

    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/vendors/bootstrap.css') }}">

    <!-- vector map css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/vector-map.css') }}">

    <!-- Slick Slider Css -->
    <link rel="stylesheet" href="{{ asset('public/assets/css/vendors/slick.css') }}">

    <!-- App css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/style.css') }} ">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/assets/css/customeCss.css') }} ">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>