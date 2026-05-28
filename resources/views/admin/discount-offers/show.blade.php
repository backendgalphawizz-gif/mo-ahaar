@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php $tab = request('tab', 'overview'); @endphp
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
            <a href="{{ route('admin.discount-offers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
            <div class="flex-grow-1">
                <h5 class="mb-0">Promo Code Details</h5>
                <small class="text-muted">View usage and configuration for {{ strtoupper($discountOffer->title) }}</small>
            </div>
            <span class="badge {{ $discountOffer->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }} px-3 py-2">
                <i class="ri-checkbox-blank-circle-fill me-1" style="font-size:8px;"></i>{{ $discountOffer->is_active ? 'Active' : 'Inactive' }}
            </span>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.discount-offers.edit', $discountOffer->id) }}">Edit Promo</a>
        </div>

        <ul class="nav nav-tabs admin-profile-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'overview' ? 'active' : '' }}" href="{{ route('admin.discount-offers.show', $discountOffer->id) }}?tab=overview">
                    <i class="ri-layout-grid-line me-1"></i>Promo Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'usage' ? 'active' : '' }}" href="{{ route('admin.discount-offers.show', $discountOffer->id) }}?tab=usage">
                    <i class="ri-bar-chart-line me-1"></i>Usage & Performance
                </a>
            </li>
        </ul>

        @if($tab === 'usage')
            <div class="card dashboard-card text-center py-5">
                <div class="card-body">
                    <i class="ri-bar-chart-line display-4 text-muted mb-3"></i>
                    <h6>Usage Analytics</h6>
                    <p class="text-muted mb-0 mx-auto" style="max-width:480px;">
                        This promo code can be tracked once orders apply <strong>{{ strtoupper($discountOffer->title) }}</strong> at checkout.
                        Detailed analytics and user redemptions will appear here once more data is collected.
                    </p>
                </div>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card dashboard-card">
                        <div class="card-body text-center">
                            <div class="rounded bg-light mb-3 d-flex align-items-center justify-content-center mx-auto" style="height:140px;">
                                <i class="ri-coupon-3-line display-4 text-warning"></i>
                            </div>
                            <div class="promo-code-badge d-inline-block mb-2">{{ strtoupper($discountOffer->title) }}</div>
                            <p class="text-muted">{{ $discountOffer->description ?: 'No display message' }}</p>
                            <div class="row g-2 text-start mt-3">
                                <div class="col-6">
                                    <small class="text-muted d-block"><i class="ri-calendar-line me-1"></i>Start Date</small>
                                    <strong>{{ $discountOffer->valid_from ? $discountOffer->valid_from->format('n/j/Y') : '—' }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block"><i class="ri-calendar-line me-1"></i>End Date</small>
                                    <strong>{{ $discountOffer->valid_until ? $discountOffer->valid_until->format('n/j/Y') : '—' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <h6 class="mb-4">Rules & Configuration</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 rounded border">
                                        <i class="ri-percent-line text-primary me-2"></i>
                                        <strong>Discount Value</strong>
                                        <div class="fs-5 fw-bold mt-1">
                                            @if($discountOffer->discount_type === 'percentage')
                                                {{ number_format((float) $discountOffer->discount_value, 0) }}%
                                            @else
                                                ₹{{ number_format((float) $discountOffer->discount_value, 0) }}
                                            @endif
                                        </div>
                                        <small class="text-muted">Applied as {{ $discountOffer->discount_type === 'percentage' ? 'Percentage' : 'Fixed Amount' }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded border">
                                        <i class="ri-wallet-3-line text-success me-2"></i>
                                        <strong>Minimum Order Amount</strong>
                                        <div class="fs-5 fw-bold mt-1">₹{{ number_format((float) ($discountOffer->min_cart_amount ?? 0), 0) }}</div>
                                        <small class="text-muted">{{ $discountOffer->min_cart_amount ? 'Required to apply code' : 'No minimum required' }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded border">
                                        <i class="ri-price-tag-3-line text-info me-2"></i>
                                        <strong>Maximum Discount</strong>
                                        <div class="fs-5 fw-bold mt-1">{{ $discountOffer->max_cart_amount ? '₹' . number_format((float) $discountOffer->max_cart_amount, 0) : 'No limit' }}</div>
                                        <small class="text-muted">Capped amount</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 rounded border">
                                        <i class="ri-group-line text-warning me-2"></i>
                                        <strong>Apply To</strong>
                                        <div class="fs-5 fw-bold mt-1">{{ ucwords(str_replace('_', ' ', $discountOffer->apply_to)) }}</div>
                                        <small class="text-muted">Product scope</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
