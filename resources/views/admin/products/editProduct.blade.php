@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
@endphp
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <h5 class="mb-0">Food Management</h5>
        </div>

        <div class="card dashboard-card mx-auto" style="max-width:980px;">
            <div class="card-body p-4">
                <h5 class="mb-3">Edit Food Item</h5>
                <form method="POST" action="{{ route($isVendorPanel ? 'vendor.update-product' : 'admin.update-product') }}" enctype="multipart/form-data" id="foodEditForm" class="row g-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                    <input type="hidden" name="is_active_status" value="{{ $product->is_active_status }}">
                    <input type="hidden" name="target_user_type" value="{{ old('target_user_type', $product->target_user_type ?: \App\Models\Product::TARGET_RETAILER) }}">
                    <input type="hidden" name="stock" value="{{ old('stock', $product->stock ?: 100) }}">
                    <input type="hidden" name="stock_status" value="{{ old('stock_status', $product->stock_status ?: 'in_stock') }}">
                    <input type="hidden" name="discount" value="{{ old('discount', $product->discount ?? 0) }}">
                    <input type="hidden" name="gst_calculation_type" value="{{ old('gst_calculation_type', $product->gst_calculation_type ?? \App\Models\Product::GST_EXCLUDED) }}">
                    <input type="hidden" name="status" value="{{ old('status', $product->status ?? 1) }}">
                    <input type="hidden" name="price" id="price_hidden" value="{{ old('mrp_price', $product->mrp_price ?? $product->price) }}">
                    <input type="hidden" name="category_id" value="{{ old('category_id', $product->category_id ?: optional($categoryList->first())->category_id) }}">

                    <div class="col-md-6">
                        <label class="form-label">Food Name</label>
                        <input type="text" name="product_name" class="form-control @error('product_name') is-invalid @enderror" value="{{ old('product_name', $product->product_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Price ($)</label>
                        <input type="number" step="0.01" min="0" name="mrp_price" id="mrp_price" class="form-control @error('mrp_price') is-invalid @enderror" value="{{ old('mrp_price', $product->mrp_price ?? $product->price) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Ingredients</label>
                        <textarea name="product_description" rows="3" class="form-control @error('product_description') is-invalid @enderror" required>{{ old('product_description', $product->product_description) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Veg / Non Veg</label>
                        @php
                            $foodType = strtolower((string) old('product_type', in_array(strtolower((string) $product->product_type), ['veg', 'non-veg'], true) ? $product->product_type : 'veg'));
                        @endphp
                        <select name="product_type" class="form-select @error('product_type') is-invalid @enderror" required>
                            <option value="">Select type</option>
                            <option value="veg" {{ $foodType === 'veg' ? 'selected' : '' }}>Veg</option>
                            <option value="non-veg" {{ $foodType === 'non-veg' ? 'selected' : '' }}>Non Veg</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Promo Code (If Any)</label>
                        <select name="tags" class="form-select @error('tags') is-invalid @enderror">
                            <option value="">Select promo code</option>
                            @foreach(($promoCodes ?? collect()) as $promo)
                                <option value="{{ $promo->title }}" {{ old('tags', $product->tags) === $promo->title ? 'selected' : '' }}>{{ $promo->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Upload Image</label>
                        <label for="product_image" class="upload-box w-100">
                            <i class="ri-image-2-line"></i>
                            <span class="d-block text-primary">Upload a file</span>
                            <small class="text-muted">PNG, JPG, GIF up to 2MB</small>
                            @if(!empty($product->product_image))
                                <small class="d-block mt-1 text-muted">Current: {{ $product->product_image }}</small>
                            @endif
                        </label>
                        <input type="file" id="product_image" name="product_image" class="d-none @error('product_image') is-invalid @enderror" accept=".png,.jpg,.jpeg,.gif,.webp">
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route($isVendorPanel ? 'vendor.products' : 'admin.products') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.upload-box { border: 1px dashed #d1d5db; border-radius: 8px; padding: 28px 12px; text-align: center; background: #fafafa; cursor: pointer; }
.upload-box i { font-size: 30px; color: #94a3b8; display: block; margin-bottom: 6px; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var mrpInput = document.getElementById('mrp_price');
    var priceHidden = document.getElementById('price_hidden');
    function syncPrice() {
        if (priceHidden) priceHidden.value = mrpInput.value || '';
    }
    if (mrpInput) {
        mrpInput.addEventListener('input', syncPrice);
        syncPrice();
    }
});
</script>
@endsection
