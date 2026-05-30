@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
@endphp
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'Food Management',
            'subtitle' => 'Add a new food item to the menu',
        ])

        <div class="card dashboard-card mx-auto" style="max-width: 980px;">
            <div class="card-body p-4">
                <h6 class="figma-section-title">Add New Food Item</h6>
                <form method="POST" action="{{ route($isVendorPanel ? 'vendor.store-product' : 'admin.store-product') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <input type="hidden" name="is_returnable" value="0">
                    <input type="hidden" name="is_active_status" value="1">
                    <input type="hidden" name="discount" value="0">
                    <input type="hidden" name="price" id="price_hidden" value="{{ old('price', old('mrp_price')) }}">
                    <input type="hidden" name="mrp_price" id="mrp_hidden" value="{{ old('mrp_price', old('price')) }}">

                    <div class="col-md-6">
                        <label class="form-label">Food Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control @error('product_name') is-invalid @enderror" value="{{ old('product_name') }}" placeholder="Enter food name">
                        @include('admin.partials.field-error', ['field' => 'product_name'])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="price" id="product_price" class="form-control @error('price') is-invalid @enderror @error('mrp_price') is-invalid @enderror" value="{{ old('price', old('mrp_price')) }}" placeholder="0.00">
                        @include('admin.partials.field-error', ['field' => 'price'])
                        @include('admin.partials.field-error', ['field' => 'mrp_price'])
                    </div>

                    @include('admin.products.partials.category-fields', [
                        'selectedCategoryId' => old('category_id'),
                        'selectedSubCategoryId' => old('sub_category_id'),
                        'subCategoryList' => $subCategoryList ?? collect(),
                    ])

                    @include('admin.products.partials.gst-fields')

                    <div class="col-12">
                        <label class="form-label">Ingredients <span class="text-danger">*</span></label>
                        <textarea name="product_description" rows="3" class="form-control @error('product_description') is-invalid @enderror" placeholder="List main ingredients">{{ old('product_description') }}</textarea>
                        @include('admin.partials.field-error', ['field' => 'product_description'])
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Veg / Non Veg <span class="text-danger">*</span></label>
                        <select name="product_type" class="form-select @error('product_type') is-invalid @enderror">
                            <option value="">Select type</option>
                            <option value="veg" {{ old('product_type') === 'veg' ? 'selected' : '' }}>Veg</option>
                            <option value="non-veg" {{ old('product_type') === 'non-veg' ? 'selected' : '' }}>Non Veg</option>
                        </select>
                        @include('admin.partials.field-error', ['field' => 'product_type'])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Promo Code (If Any)</label>
                        <select name="tags" class="form-select @error('tags') is-invalid @enderror">
                            <option value="">Select promo code</option>
                            @foreach(($promoCodes ?? collect()) as $promo)
                                <option value="{{ $promo->title }}" {{ old('tags') === $promo->title ? 'selected' : '' }}>{{ $promo->title }}</option>
                            @endforeach
                        </select>
                        @include('admin.partials.field-error', ['field' => 'tags'])
                    </div>

                    <div class="col-12">
                        <label class="form-label">Upload Image <span class="text-danger">*</span></label>
                        <label for="product_image" class="upload-box w-100 @error('product_image') border-danger @enderror">
                            <i class="ri-image-2-line"></i>
                            <span class="d-block text-primary">Upload a file</span>
                            <small class="text-muted">PNG, JPG, GIF up to 2MB</small>
                        </label>
                        <input type="file" id="product_image" name="product_image" class="d-none" accept=".png,.jpg,.jpeg,.gif,.webp">
                        @include('admin.partials.field-error', ['field' => 'product_image'])
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2 pt-3 border-top">
                        <a href="{{ route($isVendorPanel ? 'vendor.products' : 'admin.products') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Add Food Item</button>
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
@include('admin.products.partials.gst-fields-script')
@include('admin.products.partials.category-fields-script', [
    'selectedSubCategoryId' => old('sub_category_id'),
])
@endsection
