@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
            <div>
                <h5 class="mb-0">Promo Codes</h5>
                <small class="text-muted">Manage discount coupons and promotional offers.</small>
            </div>
            <a href="{{ route('admin.discount-offers.create') }}" class="btn btn-theme ms-auto">
                <i class="ri-add-line me-1"></i>Add Promo Code
            </a>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="card dashboard-card mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">All Promo Codes</h6>
                    <form method="GET" action="{{ route('admin.discount-offers.index') }}" class="d-flex gap-2">
                        <input type="text" name="search" class="form-control form-control-sm" style="width:220px;" placeholder="Search promo codes..." value="{{ $search }}">
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Image</th>
                                <th>Promo Code</th>
                                <th>Message</th>
                                <th>Duration</th>
                                <th>Discount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($offers as $offer)
                                <tr>
                                    <td>{{ $offers->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span class="rounded bg-light d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                            <i class="ri-coupon-3-line text-warning fs-4"></i>
                                        </span>
                                    </td>
                                    <td><span class="promo-code-badge">{{ strtoupper($offer->title) }}</span></td>
                                    <td>{{ Str::limit($offer->description ?: '—', 50) }}</td>
                                    <td>
                                        <small>
                                            From: {{ optional($offer->valid_from)->format('n/j/Y') ?: '—' }}<br>
                                            To: {{ optional($offer->valid_until)->format('n/j/Y') ?: '—' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($offer->discount_type === 'percentage')
                                            {{ number_format((float) $offer->discount_value, 0) }}%<br><small class="text-muted">Percentage</small>
                                        @else
                                            ₹{{ number_format((float) $offer->discount_value, 0) }}<br><small class="text-muted">Fixed Amount</small>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.discount-offers.toggle-status', $offer->id) }}">
                                            @csrf
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $offer->is_active ? 'checked' : '' }}>
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.discount-offers.show', $offer->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="ri-eye-line"></i></a>
                                            <a href="{{ route('admin.discount-offers.edit', $offer->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <form method="POST" action="{{ route('admin.discount-offers.destroy', $offer->id) }}" class="d-inline" onsubmit="return confirm('Delete this promo code?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">No promo codes found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($offers->hasPages())<div class="mt-3">{{ $offers->links('pagination::bootstrap-5') }}</div>@endif
            </div>
        </div>
    </div>
</div>
@endsection
