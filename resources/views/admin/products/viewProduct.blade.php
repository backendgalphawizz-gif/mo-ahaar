@extends('layouts.app')

@section('content')
    @php
        $gallery = !empty($product->gallery_images) ? array_filter(array_map('trim', explode(',', $product->gallery_images))) : [];
        $editSegment = match ($product->target_user_type ?? null) {
            \App\Models\Product::TARGET_RETAILER => 'retailer',
            \App\Models\Product::TARGET_WHOLESALER => 'wholesaler',
            default => null,
        };
        $approvalLabel = match ((string) ($product->status ?? '')) {
            '1' => 'Approved',
            '2' => 'Pending',
            '3' => 'Rejected',
            default => '—',
        };
    @endphp
    <div class="page-body">
        <div class="container-fluid">
            <div class="title-header option-title d-flex align-items-center mb-4">
                <h5>Food Details</h5>
                <a href="{{ route('admin.edit-product', array_merge(['id' => $product->product_id], array_filter(['segment' => $editSegment]))) }}"
                    class="btn btn-theme btn-sm ms-auto me-2">Edit Product</a>
                <a href="{{ route('admin.products', array_filter(['segment' => $editSegment])) }}"
                    class="btn btn-outline-secondary btn-sm">Back</a>
            </div>

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card h-100 product-summary-card">
                        <div class="card-body text-center">
                            <img src="{{ !empty($product->product_image) ? asset('public/uploads/products/' . $product->product_image) : asset('public/assets/images/product/1.png') }}"
                                alt="product" class="summary-image mb-3">
                            <h5 class="mb-1">{{ $product->product_name }}</h5>
                            <span class="badge badge-soft-warning mb-2">SKU: {{ $product->sku ?: '—' }}</span>
                            <div class="d-grid gap-2 mt-3 text-start">
                                <div class="summary-line"><small>Customer segment</small><strong>
                                        @if(!empty($product->target_user_type))
                                            @if($product->target_user_type === \App\Models\Product::TARGET_WHOLESALER)
                                                Wholesaler
                                            @else
                                                Retailer
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </strong></div>
                                <div class="summary-line">
                                    <small>MRP</small><strong>₹{{ number_format((float) ($product->mrp_price ?? $product->price), 2) }}</strong>
                                </div>
                                <div class="summary-line">
                                    <small>Price</small><strong>₹{{ number_format((float) $product->price, 2) }}</strong>
                                </div>
                                <div class="summary-line"><small>Stock</small><strong>{{ (int) $product->stock }}</strong>
                                </div>
                                <div class="summary-line"><small>Stock
                                        status</small><strong>{{ str_replace('_', ' ', $product->stock_status ?: '—') }}</strong>
                                </div>
                                @if(($product->target_user_type ?? null) === \App\Models\Product::TARGET_WHOLESALER)
                                    <div class="summary-line"><small>Min. order
                                            qty</small><strong>{{ $product->min_quantity !== null ? (int) $product->min_quantity : '—' }}</strong>
                                    </div>
                                @endif
                                <div class="summary-line"><small>Approval</small><strong>{{ $approvalLabel }}</strong></div>
                                <div class="summary-line"><small>Catalog
                                        listing</small><strong>{{ (int) $product->is_active_status === 1 ? 'Active' : 'Inactive' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card mb-4">
                        <div class="card-header card-header-2">
                            <h5>Product details</h5>
                        </div>
                        <div class="card-body detail-grid">
                            <div class="detail-item span-2"><label>Description</label><span
                                    class="text-break">{{ $product->product_description ?: '—' }}</span></div>
                            <div class="detail-item">
                                <label>Category</label><span>{{ $product->category_name ?: '—' }}</span></div>
                            <div class="detail-item">
                                <label>Sub-category</label><span>{{ $product->sub_cat_name ?: '—' }}</span></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header card-header-2">
                            <h5>Gallery</h5>
                        </div>
                        <div class="card-body">
                            <div class="preview-grid">
                                @forelse($gallery as $img)
                                    <img src="{{ asset('public/uploads/products/' . $img) }}" alt="gallery">
                                @empty
                                    <p class="text-muted mb-0">No gallery images.</p>
                                @endforelse
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
        .product-summary-card {
            border: 1px solid #ebeff4;
            background: radial-gradient(circle at top right, rgba(193, 143, 51, .16), #fff 60%);
        }

        .summary-image {
            width: 100%;
            max-width: 240px;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #dbe2ea;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #e4e9ef;
            padding: 4px 0;
        }

        .summary-line small {
            color: #7f8a99;
        }

        .detail-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .detail-item {
            border: 1px solid #ecf1f5;
            border-radius: 8px;
            background: #fafbfd;
            padding: 10px 12px;
        }

        .detail-item.span-2 {
            grid-column: 1 / -1;
        }

        .detail-item label {
            display: block;
            font-size: 11px;
            color: #7f8a99;
            text-transform: uppercase;
            margin-bottom: 3px;
            font-weight: 600;
        }

        .detail-item span {
            font-size: 14px;
            color: #27313f;
        }

        .preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .preview-grid img {
            width: 94px;
            height: 94px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #d9e0e8;
        }

        @media (max-width: 991px) {
            .detail-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>
@endsection