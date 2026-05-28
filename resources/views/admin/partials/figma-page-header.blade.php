@php
    $pageTitle = $title ?? '';
    $pageSubtitle = $subtitle ?? null;
    $actionUrl = $actionUrl ?? null;
    $actionLabel = $actionLabel ?? null;
    $actionIcon = $actionIcon ?? 'ri-add-line';
@endphp
<div class="figma-page-header d-flex flex-wrap align-items-start gap-2 mb-3">
    <div class="flex-grow-1">
        <h4 class="figma-page-title mb-1">{{ $pageTitle }}</h4>
        @if(!empty($pageSubtitle))
            <p class="figma-page-subtitle mb-0">{{ $pageSubtitle }}</p>
        @endif
    </div>
    @if(!empty($actionUrl) && !empty($actionLabel))
        <a href="{{ $actionUrl }}" class="btn btn-theme btn-sm ms-auto flex-shrink-0">
            <i class="{{ $actionIcon }} me-1"></i>{{ $actionLabel }}
        </a>
    @endif
</div>
