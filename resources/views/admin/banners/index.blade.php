@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <h5 class="mb-0">Banner Management</h5>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-theme btn-sm ms-auto">
                <i class="ri-add-line me-1"></i>Add Banner
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Banner Image</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $banner)
                                <tr>
                                    <td>
                                        <img src="{{ asset('public/uploads/banners/' . $banner->banner_image) }}" alt="banner" class="banner-thumb">
                                    </td>
                                    <td>{{ $banner->title ?: '-' }}</td>
                                    <td>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" disabled {{ (int) $banner->status === 1 ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <ul class="d-flex gap-2 mb-0 list-unstyled">
                                            <li>
                                                <a href="{{ route('admin.banners.edit', $banner->id) }}" title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" class="delete-banner-btn" data-form-id="delete-banner-form-{{ $banner->id }}" data-banner-name="{{ $banner->title ?: 'this banner' }}" title="Delete">
                                                    <i class="ri-delete-bin-line text-danger"></i>
                                                </a>
                                                <form id="delete-banner-form-{{ $banner->id }}" method="POST" action="{{ route('admin.banners.delete', $banner->id) }}" class="d-none">
                                                    @csrf
                                                </form>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No banners found.</td></tr>
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
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else if (confirm('Delete ' + name + '?')) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
