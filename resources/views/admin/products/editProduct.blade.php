@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
    $currentPrice = old('price', old('mrp_price', $product->mrp_price ?? $product->price));
@endphp
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'Food Management',
            'subtitle' => 'Update food item details',
        ])

        <div class="card dashboard-card mx-auto" style="max-width:980px;">
            <div class="card-body p-4">
                <h6 class="figma-section-title">Edit Food Item</h6>
                <form method="POST" action="{{ route($isVendorPanel ? 'vendor.update-product' : 'admin.update-product') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                    <input type="hidden" name="is_active_status" value="{{ $product->is_active_status }}">
                    <input type="hidden" name="discount" value="{{ old('discount', $product->discount ?? 0) }}">
                    <input type="hidden" name="gst_calculation_type" value="{{ old('gst_calculation_type', $product->gst_calculation_type ?? \App\Models\Product::GST_EXCLUDED) }}">
                    <input type="hidden" name="status" value="{{ old('status', $product->status ?? 1) }}">
                    <input type="hidden" name="price" id="price_hidden" value="{{ $currentPrice }}">
                    <input type="hidden" name="mrp_price" id="mrp_hidden" value="{{ $currentPrice }}">
                    <input type="hidden" name="category_id" value="{{ old('category_id', $product->category_id ?: optional($categoryList->first())->category_id) }}">

                    <div class="col-md-6">
                        <label class="form-label">Food Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control @error('product_name') is-invalid @enderror" value="{{ old('product_name', $product->product_name) }}">
                        @include('admin.partials.field-error', ['field' => 'product_name'])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="price" id="product_price" class="form-control @error('price') is-invalid @enderror @error('mrp_price') is-invalid @enderror" value="{{ $currentPrice }}">
                        @include('admin.partials.field-error', ['field' => 'price'])
                        @include('admin.partials.field-error', ['field' => 'mrp_price'])
                    </div>

                    <div class="col-12">
                        <label class="form-label">Ingredients <span class="text-danger">*</span></label>
                        <textarea name="product_description" rows="3" class="form-control @error('product_description') is-invalid @enderror">{{ old('product_description', $product->product_description) }}</textarea>
                        @include('admin.partials.field-error', ['field' => 'product_description'])
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Veg / Non Veg <span class="text-danger">*</span></label>
                        @php
                            $foodType = strtolower((string) old('product_type', in_array(strtolower((string) $product->product_type), ['veg', 'non-veg'], true) ? $product->product_type : 'veg'));
                        @endphp
                        <select name="product_type" class="form-select @error('product_type') is-invalid @enderror">
                            <option value="">Select type</option>
                            <option value="veg" {{ $foodType === 'veg' ? 'selected' : '' }}>Veg</option>
                            <option value="non-veg" {{ $foodType === 'non-veg' ? 'selected' : '' }}>Non Veg</option>
                        </select>
                        @include('admin.partials.field-error', ['field' => 'product_type'])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Promo Code (If Any)</label>
                        <select name="tags" class="form-select @error('tags') is-invalid @enderror">
                            <option value="">Select promo code</option>
                            @foreach(($promoCodes ?? collect()) as $promo)
                                <option value="{{ $promo->title }}" {{ old('tags', $product->tags) === $promo->title ? 'selected' : '' }}>{{ $promo->title }}</option>
                            @endforeach
                        </select>
                        @include('admin.partials.field-error', ['field' => 'tags'])
                    </div>

                    <div class="col-12">
                        <label class="form-label">Upload Image</label>
                        <label for="product_image" class="upload-box w-100 @error('product_image') border-danger @enderror">
                            <i class="ri-image-2-line"></i>
                            <span class="d-block text-primary">Upload a file</span>
                            <small class="text-muted">PNG, JPG, GIF up to 2MB</small>
                            @if(!empty($product->product_image))
                                <small class="d-block mt-1 text-muted">Current: {{ $product->product_image }}</small>
                            @endif
                        </label>
                        <input type="file" id="product_image" name="product_image" class="d-none" accept=".png,.jpg,.jpeg,.gif,.webp">
                        @include('admin.partials.field-error', ['field' => 'product_image'])
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2 pt-3 border-top">
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
    var priceInput = document.getElementById('product_price');
    var priceHidden = document.getElementById('price_hidden');
    var mrpHidden = document.getElementById('mrp_hidden');
    function syncPrice() {
        var val = priceInput ? priceInput.value : '';
        if (priceHidden) priceHidden.value = val;
        if (mrpHidden) mrpHidden.value = val;
    }
    if (priceInput) {
        priceInput.addEventListener('input', syncPrice);
        syncPrice();
    }
});
</script>
@endsection
