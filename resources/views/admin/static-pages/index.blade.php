@extends('layouts.app')

@section('content')

<style>
    table tbody tr td a .ri-pencil-line {
        color: #e3951d;

    }

    table tbody tr td a.btn-outline-primary:hover .ri-eye-line {
        color: #fff !important;
    }
</style>
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-list-check me-2"></i>{{ $title }}</h5>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.static-pages.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-8">
                        <!-- Vendor selection removed -->
                    </div>
                    <div class="col-md-4 text-md-end">
                        <!-- Vendor load button removed -->
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table  table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">S.No.</th>
                                <th class="ps-3">Title</th>
                                <th>Slug</th>
                                <th>Content Preview</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pages as $page)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $loop->iteration }}</td>
                                <td class="ps-3 fw-semibold">{{ $page->title }}</td>
                                <td><span class="text-muted">{{ $page->slug }}</span></td>
                                <td>
                                    <span class="text-muted">{{
                                        \Illuminate\Support\Str::limit(strip_tags($page->content), 90) }}</span>
                                </td>
                                <td>
                                    @if($page->status)
                                    <span class="badge badge-soft-success">Published</span>
                                    @else
                                    <span class="badge  badge-soft-warning">Draft</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.static-pages.edit', ['id' => $page->static_page_id]) }}"
                                        class=" me-1">
                                        <i class="ri-pencil-line"></i> 
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No static pages found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection