@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'Banner Management',
            'subtitle' => 'Manage promotional banners on the customer app',
            'actionUrl' => route('admin.banners.create'),
            'actionLabel' => 'Add Banner',
        ])

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card dashboard-card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.banners.index') }}" class="figma-toolbar">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Status: All</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Apply</button>
                    <span class="toolbar-spacer"></span>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Sl No.</th>
                                <th>Banner Image</th>
                                <th>Title</th>
                                <th>Created Date &amp; Time</th>
                                <th>Toggle Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $banner)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <img src="{{ asset('public/uploads/banners/' . $banner->banner_image) }}" alt="banner" class="banner-thumb">
                                    </td>
                                    <td>{{ $banner->title ?: '-' }}</td>
                                    <td>
                                        <div>{{ optional($banner->created_at)->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ optional($banner->created_at)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        @include('admin.partials.ajax-status-toggle', [
                                            'url' => route('admin.banners.toggle-status', $banner->id),
                                            'checked' => (int) $banner->status === 1,
                                        ])
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 table-action-icons">
                                            <a href="{{ route('admin.banners.edit', $banner->id) }}" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <a href="javascript:void(0)" class="delete-banner-btn text-danger" data-form-id="delete-banner-form-{{ $banner->id }}" data-banner-name="{{ $banner->title ?: 'this banner' }}" title="Delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                            <form id="delete-banner-form-{{ $banner->id }}" method="POST" action="{{ route('admin.banners.delete', $banner->id) }}" class="d-none">
                                                @csrf
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No banners found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.banner-thumb { width: 72px; height: 42px; object-fit: cover; border-radius: 8px; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-banner-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var formId = this.getAttribute('data-form-id');
            var name = this.getAttribute('data-banner-name') || 'this banner';
            var form = document.getElementById(formId);
            if (!form) return;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Banner?',
                    text: 'Are you sure you want to delete ' + name + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc3545'
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            } else if (confirm('Delete ' + name + '?')) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
