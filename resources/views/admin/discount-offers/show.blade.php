@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0"><i class="ri-price-tag-3-line me-2"></i>Offer Details</h5>
            <div class="ms-auto d-flex gap-2">
                <a class="btn btn-theme btn-sm" href="{{ route('admin.discount-offers.edit', $discountOffer->id) }}">
                    <i class="ri-pencil-line me-1"></i>Edit
                </a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.discount-offers.index') }}">
                    <i class="ri-arrow-left-line me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="row g-4">
            {{-- Left column --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent fw-semibold">Basic Info</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted w-40">Title</td>
                                <td class="fw-medium">{{ $discountOffer->title }}</td>
                            </tr>
                            @if($discountOffer->description)
                            <tr>
                                <td class="text-muted">Description</td>
                                <td>{{ $discountOffer->description }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Discount Type</td>
                                <td>{{ ucfirst($discountOffer->discount_type) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Discount Value</td>
                                <td>
                                    @if($discountOffer->discount_type === 'percentage')
                                        <span class="badge bg-info text-dark fs-6">{{ number_format((float)$discountOffer->discount_value, 2) }}%</span>
                                    @else
                                        <span class="badge bg-warning text-dark fs-6">₹{{ number_format((float)$discountOffer->discount_value, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>
                                    @if($discountOffer->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created</td>
                                <td>{{ $discountOffer->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent fw-semibold">Apply To</div>
                    <div class="card-body">
                        @if($discountOffer->apply_to === 'all')
                            <span class="badge bg-success">All Products</span>
                        @elseif($discountOffer->apply_to === 'specific_products')
                            <p class="mb-2 text-muted small">Specific Products ({{ count((array)($discountOffer->product_ids ?? [])) }})</p>
                            @if($products->isNotEmpty())
                                <ul class="mb-0 ps-3">
                                    @foreach($products as $p)
                                        <li>{{ $p->product_name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted small">None selected.</span>
                            @endif
                        @else
                            <p class="mb-2 text-muted small">Specific Categories ({{ count((array)($discountOffer->category_ids ?? [])) }})</p>
                            @if($categories->isNotEmpty())
                                <ul class="mb-0 ps-3">
                                    @foreach($categories as $c)
                                        <li>{{ $c->category_name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted small">None selected.</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right column --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent fw-semibold">Validity Period</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted">Valid From</td>
                                <td>{{ $discountOffer->valid_from ? $discountOffer->valid_from->format('d M Y') : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Valid Until</td>
                                <td>{{ $discountOffer->valid_until ? $discountOffer->valid_until->format('d M Y') : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold">Conditions</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted">Min Quantity</td>
                                <td>{{ $discountOffer->min_quantity ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Max Quantity</td>
                                <td>{{ $discountOffer->max_quantity ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Min Cart Amount</td>
                                <td>{{ $discountOffer->min_cart_amount ? '₹'.number_format((float)$discountOffer->min_cart_amount,2) : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Max Cart Amount</td>
                                <td>{{ $discountOffer->max_cart_amount ? '₹'.number_format((float)$discountOffer->max_cart_amount,2) : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
