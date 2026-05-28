@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <h5 class="mb-0">Food Management</h5>
        </div>

        <div class="card dashboard-card mx-auto" style="max-width: 760px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Food Details</h6>
                    <a href="{{ route('admin.products') }}" class="small">Back to List</a>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <img src="{{ !empty($product->product_image) ? asset('public/uploads/products/' . $product->product_image) : asset('public/assets/images/product/1.png') }}" alt="food" style="width:180px;height:160px;border-radius:8px;object-fit:cover;">
                    <div class="flex-grow-1">
                        <h5 class="mb-1">{{ $product->product_name }}
                            @if(strtolower((string) $product->product_type) === 'veg')
                                <small class="text-success"><i class="ri-seedling-line"></i></small>
                            @endif
                        </h5>
                        <div class="text-warning fw-semibold mb-2">₹{{ number_format((float) ($product->price ?? 0), 2) }}</div>
                        <div class="small text-muted mb-2">Ingredients</div>
                        <div class="small mb-3">{{ $product->product_description ?: '—' }}</div>
                        <div class="d-flex flex-wrap gap-4 small">
                            <div>Rating<br><strong>{{ number_format((float) ($product->avg_rating ?? 4.5), 1) }} / 5.0</strong></div>
                            <div>Status<br><span class="badge badge-soft-danger">{{ (int) $product->is_active_status === 1 ? 'Active' : 'Inactive' }}</span></div>
                            <div>Promo Code<br><strong>{{ $product->tags ?: '—' }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection