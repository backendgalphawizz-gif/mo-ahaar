@php
    $storeSetting = $globalStoreSetting ?? null;
    $faviconUrl = !empty($storeSetting) && !empty($storeSetting->favicon)
        ? asset('public/uploads/settings/' . $storeSetting->favicon)
        : asset('public/assets/images/favicon.png');
    $faviconVersion = !empty($storeSetting) && !empty($storeSetting->updated_at)
        ? $storeSetting->updated_at->timestamp
        : '1';
@endphp
<link rel="icon" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
<link rel="shortcut icon" href="{{ $faviconUrl }}?v={{ $faviconVersion }}">
