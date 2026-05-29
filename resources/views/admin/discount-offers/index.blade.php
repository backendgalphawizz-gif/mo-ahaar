@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'Promo Codes',
            'subtitle' => 'Manage discount coupons and promotional offers',
            'actionUrl' => route('admin.discount-offers.create'),
            'actionLabel' => 'Add Promo Code',
        ])

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="card dashboard-card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.discount-offers.index') }}" class="figma-toolbar">
                    <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm" placeholder="Filter by Date">
                    <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>Status: All</option>
                        <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($statusFilter ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @if(!empty($search))
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Apply</button>
                    <span class="toolbar-spacer"></span>
                    <input type="text" name="search" class="form-control form-control-sm" style="max-width:200px;" placeholder="Search..." value="{{ $search }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                    <a href="{{ route('admin.discount-offers.index') }}" class="btn btn-outline-secondary btn-sm">Export All</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Sl. No.</th>
                                <th>Image &amp; Promo Code</th>
                                <th>Message</th>
                                <th>Start &amp; End Date</th>
                                <th>Discount</th>
                                <th>Discount Type</th>
                                <th>Toggle Status</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($offers as $offer)
                                @php
                                    $isPercentage = $offer->discount_type === 'percentage';
                                    $discountLabel = $isPercentage
                                        ? number_format((float) $offer->discount_value, 0) . '%'
                                        : '₹' . number_format((float) $offer->discount_value, 0);
                                    $typeLabel = $isPercentage ? 'Percentage' : 'Flat';
                                @endphp
                                <tr>
                                    <td>{{ $offers->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="promo-cell">
                                            <span class="promo-thumb"><i class="ri-coupon-3-line"></i></span>
                                            <span class="promo-code-badge">{{ strtoupper($offer->title) }}</span>
                                        </div>
                                    </td>
                                    <td>{{ Str::limit($offer->description ?: '—', 60) }}</td>
                                    <td>
                                        <small>
                                            {{ optional($offer->valid_from)->format('d/m/Y') ?: '—' }}
                                            to
                                            {{ optional($offer->valid_until)->format('d/m/Y') ?: '—' }}
                                        </small>
                                    </td>
                                    <td class="fw-semibold">{{ $discountLabel }}</td>
                                    <td>{{ $typeLabel }}</td>
                                    <td>
                                        @include('admin.partials.ajax-status-toggle', [
                                            'url' => route('admin.discount-offers.toggle-status', $offer->id),
                                            'checked' => $offer->is_active,
                                            'statusPill' => '#discount-status-pill-' . $offer->id,
                                        ])
                                    </td>
                                    <td>
                                        <span id="discount-status-pill-{{ $offer->id }}" class="status-pill {{ $offer->is_active ? 'active' : 'inactive' }}">
                                            {{ $offer->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 table-action-icons">
                                            <a href="{{ route('admin.discount-offers.show', $offer->id) }}" title="View"><i class="ri-eye-line"></i></a>
                                            <a href="{{ route('admin.discount-offers.edit', $offer->id) }}" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <form method="POST" action="{{ route('admin.discount-offers.destroy', $offer->id) }}" class="d-inline" onsubmit="return confirm('Delete this promo code?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-4 text-muted">No promo codes found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($offers->hasPages())
                    <div class="mt-3">{{ $offers->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
