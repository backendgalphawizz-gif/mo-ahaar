@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-image-2-line me-2"></i>{{ $title }}</h5>
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

        <div class="card card-table">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table  table-modern align-middle">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Image</th>
                                <th>Title</th>
                                @if(\Illuminate\Support\Facades\Schema::hasColumn('banners', 'banner_type'))
                                <th>Section</th>
                                @endif
                                <th>Link</th>
                                <th>Visible from</th>
                                <th>Visible to</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $banner)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <img src="{{ asset('public/uploads/banners/' . $banner->banner_image) }}" alt="banner" style="width:90px;height:54px;object-fit:cover;border-radius:8px;">
                                    </td>
                                    <td>{{ $banner->title ?: '-' }}</td>
                                    @if(\Illuminate\Support\Facades\Schema::hasColumn('banners', 'banner_type'))
                                    <td><span class="badge bg-light text-dark text-capitalize">{{ $banner->banner_type ?: 'slider' }}</span></td>
                                    @endif
                                    <td>
                                        @if(!empty($banner->button_link))
                                            <a href="{{ $banner->button_link }}" target="_blank" rel="noopener noreferrer" class="small text-truncate d-inline-block" style="max-width: 200px;">{{ $banner->button_link }}</a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $banner->visible_from ? \Illuminate\Support\Carbon::parse($banner->visible_from)->format('M j, Y') : '—' }}</td>
                                    <td class="text-nowrap">{{ $banner->visible_to ? \Illuminate\Support\Carbon::parse($banner->visible_to)->format('M j, Y') : '—' }}</td>
                                    <td>
                                        @if((int) $banner->status === 1)
                                            <span class="badge badge-soft-success">Active</span>
                                        @else
                                            <span class="badge badge-soft-warning">Inactive</span>
                                        @endif
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
                                <tr>
                                    <td colspan="{{ \Illuminate\Support\Facades\Schema::hasColumn('banners', 'banner_type') ? 9 : 8 }}" class="text-center text-muted py-4">No banners found.</td>
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

@section('scripts')
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
