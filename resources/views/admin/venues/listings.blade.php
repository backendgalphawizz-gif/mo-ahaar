@extends('layouts.app')

@section('content')
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card card-table">
                            <div class="card-body">
                                <div class="title-header option-title d-flex align-items-center">
                                    <h5>Venue / Banquet Listing Management</h5>
                                    <a href="{{ route('admin.venues.bookings') }}" class="btn btn-theme ms-auto">Manage Bookings</a>
                                </div>

                                @if(!empty($warning))
                                    <div class="alert alert-warning">{{ $warning }}</div>
                                @endif

                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <div class="card border-0 metric-card metric-card-primary h-100">
                                            <div class="card-body">
                                                <small>Total Listings</small>
                                                <h3>{{ (int) ($summary['total'] ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 metric-card metric-card-success h-100">
                                            <div class="card-body">
                                                <small>Active Listings</small>
                                                <h3>{{ (int) ($summary['active'] ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 metric-card metric-card-danger h-100">
                                            <div class="card-body">
                                                <small>Inactive Listings</small>
                                                <h3>{{ (int) ($summary['inactive'] ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive table-product">
                                    <table class="table all-package theme-table" id="table_id">
                                        <thead>
                                            <tr>
                                                <th>S. No.</th>
                                                
                                                <th>Venue Name</th>
                                                <th>City</th>
                                                <th>Capacity</th>
                                                <th>Price / Booking</th>
                                                <th>Venue Vendor</th>
                                                <th>Listing Status</th>
                                                <th>Created On</th>
                                                <th>Option</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($venues as $venue)
                                                @php
                                                    $isActive = in_array(strtolower((string) $venue->status), ['1', 'active', 'approved', 'enabled'], true);
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <img src="{{ $venue->image ? asset('public/uploads/venues/' . $venue->image) : asset('public/assets/images/product/1.png') }}" alt="venue" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                                                        <span class="ms-2">{{ $venue->venue_name ?: 'N/A' }}</span>
                                                    </td>
                                                    
                                                    <td>{{ $venue->city ?: 'N/A' }}</td>
                                                    <td>{{ $venue->capacity ?: 'N/A' }}</td>
                                                    <td>₹{{ number_format((float) ($venue->price_per_booking ?? 0), 2) }}</td>
                                                    <td>
                                                        <div class="user-name">
                                                            <span>{{ $venue->business_name ?: 'N/A' }}</span>
                                                            <span>{{ $venue->owner_name ?: 'N/A' }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $isActive ? 'bg-success' : 'bg-danger' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                                    </td>
                                                    <td>{{ !empty($venue->created_at) ? \Carbon\Carbon::parse($venue->created_at)->format('d M Y') : 'N/A' }}</td>
                                                    <td>
                                                        <ul>
                                                            <li>
                                                                <a href="{{ route('admin.venues.view', $venue->venue_id) }}" title="View Venue">
                                                                    <i class="ri-eye-line"></i>
                                                                </a>
                                                            </li>

                                                            @if((string) ($venue->vendor_status ?? '0') !== '1')
                                                                <li>
                                                                    <form method="POST" action="{{ route('admin.venues.approve-vendor', $venue->vendor_id) }}" class="d-inline">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-link p-0 border-0 text-success" title="Approve Venue Vendor">
                                                                            <i class="ri-user-follow-line"></i>
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endif

                                                            <li>
                                                                <form method="POST" action="{{ route('admin.venues.toggle-status', $venue->venue_id) }}" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-link p-0 border-0 text-primary" title="Toggle Listing Status">
                                                                        <i class="ri-toggle-line"></i>
                                                                    </button>
                                                                </form>
                                                            </li>

                                                            <li>
                                                                <a href="javascript:void(0)" class="delete-venue" data-url="{{ route('admin.venues.delete', $venue->venue_id) }}" title="Delete Fake Listing">
                                                                    <i class="ri-delete-bin-line text-danger"></i>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center text-muted py-4">No venue listings found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.metric-card { border-radius: 12px; color: #fff; }
.metric-card .card-body { padding: 16px 18px; }
.metric-card small { text-transform: uppercase; letter-spacing: .05em; opacity: .9; }
.metric-card h3 { margin: 8px 0 0; font-weight: 700; }
.metric-card-primary { background: linear-gradient(135deg, #0f4c75, #3282b8); }
.metric-card-success { background: linear-gradient(135deg, #198754, #146c43); }
.metric-card-danger { background: linear-gradient(135deg, #dc3545, #a71d2a); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-venue').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var deleteUrl = this.getAttribute('data-url');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Fake Listing?',
                    text: 'This venue listing will be permanently removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = deleteUrl;
                    }
                });
            } else if (confirm('Delete this venue listing?')) {
                window.location.href = deleteUrl;
            }
        });
    });
});
</script>
@endsection
