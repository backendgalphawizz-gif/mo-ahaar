@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-eye-line me-2"></i>{{ $title }}</h5>
            <a href="{{ route('admin.static-pages.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">Back</a>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <!-- Vendor info removed -->
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body" style="line-height: 1.8;">
                {!! $page->content !!}
            </div>
        </div>
    </div>
</div>
@endsection
