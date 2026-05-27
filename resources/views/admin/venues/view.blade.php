@extends('layouts.app')

@section('content')
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                <div class="title-header option-title d-flex align-items-center mb-4">
                    <h5><i class="ri-store-2-line me-2"></i>Venue Details</h5>
                    <a href="{{ route('admin.venues.listings') }}" class="btn btn-outline-secondary btn-sm ms-auto">Back</a>
                </div>

                <div class="row g-4">
                    <div class="col-xl-4">
                        <div class="card h-100 venue-summary-card">
                            <div class="card-body text-center">
                                <img src="{{ $venue->image ? asset('public/uploads/venues/' . $venue->image) : asset('public/assets/images/product/1.png') }}"
                                     alt="{{ $venue->name }}" class="summary-image mb-3">
                                <h5 class="mb-1">{{ $venue->name }}</h5>
                                <p class="text-muted mb-2">{{ $venue->city ?: '-' }}</p>

                                @php
                                    $isActive = in_array(strtolower((string) $venue->status), ['1', 'active', 'approved', 'enabled'], true);
                                @endphp
                                <span class="badge {{ $isActive ? 'bg-success' : 'bg-danger' }} mb-2">{{ $isActive ? 'Active' : 'Inactive' }}</span>

                                <div class="d-grid gap-2 mt-3 text-start">
                                    <div class="summary-line"><small>Type</small><strong>{{ $venue->type }}</strong></div>
                                    <div class="summary-line"><small>Capacity</small><strong>{{ $venue->capacity }}</strong></div>
                                    <div class="summary-line"><small>Price / Booking</small><strong>₹{{ number_format((float) $venue->price_per_booking, 2) }}</strong></div>
                                    <div class="summary-line"><small>Address</small><strong>{{ $venue->address }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8">
                        <div class="card mb-4">
                            <div class="card-header card-header-2"><h5>Venue Information</h5></div>
                            <div class="card-body detail-grid cols-2">
                                <div class="detail-item"><label>Venue Name</label><span>{{ $venue->name }}</span></div>
                                <div class="detail-item"><label>Venue Type</label><span>{{ $venue->type }}</span></div>
                                <div class="detail-item"><label>City</label><span>{{ $venue->city }}</span></div>
                                <div class="detail-item"><label>State</label><span>{{ $venue->state }}</span></div>
                                <div class="detail-item"><label>Pincode</label><span>{{ $venue->pincode }}</span></div>
                                <div class="detail-item"><label>Capacity</label><span>{{ $venue->capacity }}</span></div>
                                <div class="detail-item cols-span-2"><label>Address</label><span>{{ $venue->address }}</span></div>
                                <div class="detail-item"><label>Venue Contact Person</label><span>{{ $venue->contact_name }}</span></div>
                                <div class="detail-item"><label>Venue Contact Number</label><span>{{ $venue->contact_phone }}</span></div>
                                <div class="detail-item cols-span-2"><label>Venue Contact Email</label><span>{{ $venue->contact_email }}</span></div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header card-header-2"><h5>Venue Vendor Information</h5></div>
                            <div class="card-body detail-grid cols-2">
                                <div class="detail-item"><label>Business Name</label><span>{{ $vendor->business_name ?? 'N/A' }}</span></div>
                                <div class="detail-item"><label>Owner Name</label><span>{{ $vendor->owner_name ?? 'N/A' }}</span></div>
                                <div class="detail-item"><label>Mobile No.</label><span>{{ $vendor->mobile ?? 'N/A' }}</span></div>
                                <div class="detail-item"><label>Email ID</label><span>{{ $vendor->email ?? 'N/A' }}</span></div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header card-header-2"><h5>Description</h5></div>
                            <div class="card-body">
                                <p class="mb-0">{{ $venue->description }}</p>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header card-header-2"><h5>Audit Information</h5></div>
                            <div class="card-body detail-grid cols-2">
                                <div class="detail-item"><label>Created On</label><span>{{ !empty($venue->created_at) ? \Carbon\Carbon::parse($venue->created_at)->format('d M Y, h:i A') : 'N/A' }}</span></div>
                                <div class="detail-item"><label>Last Updated</label><span>{{ !empty($venue->updated_at) ? \Carbon\Carbon::parse($venue->updated_at)->format('d M Y, h:i A') : 'N/A' }}</span></div>
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
.venue-summary-card { border: 1px solid #ebeff4; background: radial-gradient(circle at top right, rgba(18,163,138,.16), #fff 60%); }
.summary-image { width: 100%; max-width: 240px; height: 220px; object-fit: cover; border-radius: 12px; border: 1px solid #dbe2ea; }
.summary-line { display: flex; justify-content: space-between; border-bottom: 1px dashed #e4e9ef; padding: 4px 0; }
.summary-line small { color: #7f8a99; }
.detail-grid { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.detail-grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.detail-item { border: 1px solid #ecf1f5; border-radius: 8px; background: #fafbfd; padding: 10px 12px; }
.detail-item label { display: block; font-size: 11px; color: #7f8a99; text-transform: uppercase; margin-bottom: 3px; font-weight: 600; }
.detail-item span { font-size: 14px; color: #27313f; }
.cols-span-2 { grid-column: span 2; }
@media (max-width: 991px) {
    .detail-grid, .detail-grid.cols-2 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    .cols-span-2 { grid-column: span 1; }
}
</style>
@endsection
