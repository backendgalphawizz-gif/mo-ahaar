@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0">{{ $title }}</h5>
            <a class="btn btn-outline-secondary btn-sm ms-auto" href="{{ route('admin.products', array_filter(['segment' => $segment ?? null])) }}">
                <i class="ri-arrow-left-line me-1"></i>Back to products
            </a>
            <a class="btn btn-theme btn-sm" href="{{ route('admin.view-product', $product->product_id) }}">
                <i class="ri-eye-line me-1"></i>View product
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fix the highlighted fields and submit again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.update-product') }}" enctype="multipart/form-data" id="productForm">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                <input type="hidden" name="is_active_status" value="{{ $product->is_active_status }}">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 text-muted small fw-semibold text-uppercase" style="letter-spacing:.4px;">
                                        <i class="ri-information-line"></i>
                                        Basic Information
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-title">Product name <span class="text-danger">*</span></label>
                                    <input type="text" name="product_name" class="form-control @error('product_name') is-invalid @enderror" placeholder="Product name" value="{{ old('product_name', $product->product_name) }}" maxlength="100" data-product-name required>
                                    @error('product_name')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-title">Customer segment <span class="text-danger">*</span></label>
                                    <select name="target_user_type" id="target_user_type" class="form-select @error('target_user_type') is-invalid @enderror" required>
                                        <option value="">Select retailer or wholesaler</option>
                                        <option value="{{ \App\Models\Product::TARGET_RETAILER }}" {{ (string) old('target_user_type', $product->target_user_type) === \App\Models\Product::TARGET_RETAILER ? 'selected' : '' }}>Retailer</option>
                                        <option value="{{ \App\Models\Product::TARGET_WHOLESALER }}" {{ (string) old('target_user_type', $product->target_user_type) === \App\Models\Product::TARGET_WHOLESALER ? 'selected' : '' }}>Wholesaler</option>
                                    </select>
                                    <small class="text-muted">Retailer and wholesaler catalogs are separate in the list and app.</small>
                                    @error('target_user_type')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1" {{ old('featured', $product->featured) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="featured">
                                            Feature this product
                                        </label>
                                    </div>
                                    <small class="text-muted">Featured products are highlighted in the app and on the website.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-title">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" id="category_id" required>
                                        <option value="">Select category</option>
                                        @foreach($categoryList as $category)
                                            <option value="{{ $category->category_id }}" {{ (string) old('category_id', $product->category_id) === (string) $category->category_id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-title">Sub-category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('sub_category_id') is-invalid @enderror" name="sub_category_id" id="sub_category_id" required>
                                        <option value="">Select sub-category</option>
                                    </select>
                                    @error('sub_category_id')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label-title">Product Demo Video (YouTube URL)</label>
                                    <input type="url" name="video" class="form-control @error('video') is-invalid @enderror" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('video', $product->video) }}">
                                    <small class="text-muted">Paste a YouTube video link to show a demo video for this product. Optional.</small>
                                    @error('video')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label-title">Description <span class="text-danger">*</span></label>
                                    <textarea name="product_description" id="product_description" rows="5" maxlength="220" class="form-control @error('product_description') is-invalid @enderror" placeholder="Full description" required>{{ old('product_description', $product->product_description) }}</textarea>
                                    <div class="d-flex align-items-center justify-content-between mt-1">
                                        <small class="text-muted"><i class="ri-information-line me-1"></i>Maximum 220 characters allowed.</small>
                                        <small id="desc_counter" class="text-muted"><span id="desc_count">{{ strlen(old('product_description', $product->product_description ?? '')) }}</span> / 220</small>
                                    </div>
                                    @error('product_description')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-12 pt-1 mt-2 border-top"></div>

                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 text-muted small fw-semibold text-uppercase" style="letter-spacing:.4px;">
                                        <i class="ri-price-tag-3-line"></i>
                                        Pricing
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-title">MRP price (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" name="mrp_price" id="mrp_price" class="form-control @error('mrp_price') is-invalid @enderror" placeholder="0.00" value="{{ old('mrp_price', $product->mrp_price ?? $product->price) }}" required>
                                    @error('mrp_price')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                @php
                                    $mrpVal = (float) old('mrp_price', $product->mrp_price ?? $product->price ?? 0);
                                    $priceVal = (float) old('price', $product->price ?? 0);
                                    $discountPct = old('discount',
                                        ($product->discount !== null && (float)$product->discount > 0)
                                            ? (float) $product->discount
                                            : ($mrpVal > 0 && $priceVal < $mrpVal ? round(($mrpVal - $priceVal) / $mrpVal * 100, 2) : 0)
                                    );
                                @endphp
                                <div class="col-md-4">
                                    <label class="form-label-title">Discount (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" name="discount" id="discount_pct" class="form-control @error('discount') is-invalid @enderror" placeholder="0.00" value="{{ $discountPct }}">
                                    <small class="text-muted">Enter 0 for no discount.</small>
                                    @error('discount')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-title">Price after discount (₹)</label>
                                    <input type="number" step="0.01" id="price_display" class="form-control bg-light text-dark" placeholder="0.00" value="{{ old('price', $product->price) }}" readonly tabindex="-1">
                                    <input type="hidden" name="price" id="price" value="{{ old('price', $product->price) }}">
                                    @error('price')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-12 pt-1 mt-2 border-top"></div>

                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 text-muted small fw-semibold text-uppercase" style="letter-spacing:.4px;">
                                        <i class="ri-archive-line"></i>
                                        Inventory & Tax
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-title">Stock quantity <span class="text-danger">*</span></label>
                                    <input type="number" min="0" name="stock" id="product_stock" class="form-control @error('stock') is-invalid @enderror" placeholder="0" value="{{ old('stock', $product->stock) }}" required>
                                    @error('stock')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-title">Stock status <span class="text-danger">*</span></label>
                                    <select name="stock_status" class="form-select @error('stock_status') is-invalid @enderror" required>
                                        <option value="in_stock" {{ old('stock_status', $product->stock_status) === 'in_stock' ? 'selected' : '' }}>In stock</option>
                                        <option value="out_of_stock" {{ old('stock_status', $product->stock_status) === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                                        <option value="backorder" {{ old('stock_status', $product->stock_status) === 'backorder' ? 'selected' : '' }}>On backorder</option>
                                    </select>
                                    @error('stock_status')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-4 {{ (string) old('target_user_type', $product->target_user_type) === \App\Models\Product::TARGET_WHOLESALER ? '' : 'd-none' }}" id="wholesalerMinQtyWrap">
                                    <label class="form-label-title">Start Order Quantity <span class="text-danger">*</span></label>
                                    <input type="number" min="1" name="min_quantity" id="min_quantity" class="form-control @error('min_quantity') is-invalid @enderror" placeholder="e.g. 6" value="{{ old('min_quantity', $product->min_quantity) }}">
                                    <small class="text-muted">Shown only for wholesaler products. Must be at least 1 and not greater than stock.</small>
                                    @error('min_quantity')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-title">SKU <small class="text-muted fw-normal">(optional — auto-generated if empty)</small></label>
                                    <input type="text" name="sku" maxlength="100" class="form-control @error('sku') is-invalid @enderror" placeholder="Leave blank to auto-generate" value="{{ old('sku', $product->sku) }}">
                                    @error('sku')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-title">GST Type</label>
                                    <select name="gst_calculation_type" class="form-select @error('gst_calculation_type') is-invalid @enderror">
                                        <option value="excluded" {{ old('gst_calculation_type', $product->gst_calculation_type ?? \App\Models\Product::GST_EXCLUDED) === \App\Models\Product::GST_EXCLUDED ? 'selected' : '' }}>Excluded GST</option>
                                        <option value="included" {{ old('gst_calculation_type', $product->gst_calculation_type ?? \App\Models\Product::GST_EXCLUDED) === \App\Models\Product::GST_INCLUDED ? 'selected' : '' }}>Included GST</option>
                                    </select>
                                    <small class="text-muted">Choose if selling price is GST-exclusive or GST-inclusive.</small>
                                    @error('gst_calculation_type')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-title">GST Tax Slab</label>
                                    <select name="gst_tax_id" class="form-select @error('gst_tax_id') is-invalid @enderror">
                                        <option value="">No GST / Exempt</option>
                                        @foreach($gstTaxes as $gst)
                                            <option value="{{ $gst->id }}" {{ (string) old('gst_tax_id') !== '' ? (string) old('gst_tax_id') === (string) $gst->id ? 'selected' : '' : (number_format((float) $gst->percentage, 2) === number_format((float) $product->gst_percentage, 2) ? 'selected' : '') }}>
                                                {{ $gst->name }} ({{ number_format((float) $gst->percentage, 2) }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">GST is added on top of the selling price. Leave blank if exempt.</small>
                                    @error('gst_tax_id')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-title">Approval status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="1" {{ (string) old('status', $product->status) === '1' ? 'selected' : '' }}>Approved</option>
                                        <option value="2" {{ (string) old('status', $product->status) === '2' ? 'selected' : '' }}>Pending</option>
                                        <option value="3" {{ (string) old('status', $product->status) === '3' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    @error('status')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-4 fw-semibold d-flex align-items-center gap-2" style="color:#374151">
                                <i class="ri-image-2-line" style="font-size:18px;color:#6366f1"></i> Images
                            </h6>

                            {{-- Thumbnail --}}
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label-title mb-0">Thumbnail</label>
                                    <small class="text-muted">600×600 · max 2 MB</small>
                                </div>
                                <input type="file" name="product_image" id="product_image" class="d-none @error('product_image') is-invalid @enderror" accept="image/*">
                                <label for="product_image" id="thumbDropZone" class="img-dropzone @error('product_image') has-error @enderror">
                                    <div class="dropzone-placeholder" id="thumbPlaceholder" @if(!empty($product->product_image)) style="display:none" @endif>
                                        <i class="ri-image-add-line"></i>
                                        <span class="d-block mt-2 fw-medium" style="font-size:13px">Click or drag &amp; drop</span>
                                        <small class="text-muted" style="font-size:11px">jpg &nbsp;·&nbsp; jpeg &nbsp;·&nbsp; png &nbsp;·&nbsp; webp</small>
                                    </div>
                                    <div id="thumbPreview" class="dropzone-preview @if(empty($product->product_image)) d-none @endif">
                                        @if(!empty($product->product_image))
                                            <img id="thumbPreviewImg" src="{{ asset('public/uploads/products/' . $product->product_image) }}" alt="Current thumbnail">
                                        @else
                                            <img id="thumbPreviewImg" src="" alt="thumbnail">
                                        @endif
                                        <div class="dropzone-overlay"><i class="ri-edit-2-line me-1"></i>Change photo</div>
                                    </div>
                                </label>
                                @error('product_image')<p class="text-danger small mt-2 mb-0"><i class="ri-error-warning-line me-1"></i>{{ $message }}</p>@enderror
                                <small class="text-muted d-block mt-1">Leave empty to keep current image.</small>
                            </div>

                            {{-- Gallery --}}
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label-title mb-0">Gallery</label>
                                    <small class="text-muted">Optional · max 4 MB each</small>
                                </div>
                                <input type="hidden" id="gallery_images_to_delete" name="gallery_images_to_delete" value="">

                                {{-- Existing saved gallery images --}}
                                @if(!empty($product->gallery_images))
                                    <div class="gallery-preview-grid mb-3" id="currentGalleryImages">
                                        @foreach(array_filter(array_map('trim', explode(',', $product->gallery_images))) as $gimg)
                                            <div class="gallery-thumb" id="gthumb-{{ md5($gimg) }}">
                                                <img src="{{ asset('public/uploads/products/' . $gimg) }}" alt="">
                                                <button type="button" class="gallery-thumb-remove" data-image="{{ $gimg }}" onclick="markImageForDelete(this, event)" title="Remove">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <input type="file" name="gallery_images[]" id="gallery_images" class="d-none @error('gallery_images') is-invalid @enderror @error('gallery_images.*') is-invalid @enderror" accept="image/*" multiple>
                                <label for="gallery_images" id="galleryDropZone" class="img-dropzone img-dropzone--sm @error('gallery_images') has-error @enderror @error('gallery_images.*') has-error @enderror">
                                    <i class="ri-gallery-line"></i>
                                    <div>
                                        <span class="d-block fw-medium" style="font-size:13px">Add gallery images</span>
                                        <small class="text-muted" style="font-size:11px">Click or drag &amp; drop · multiple files</small>
                                    </div>
                                </label>
                                @error('gallery_images')<p class="text-danger small mt-2 mb-0"><i class="ri-error-warning-line me-1"></i>{{ $message }}</p>@enderror
                                @error('gallery_images.*')<p class="text-danger small mt-2 mb-0"><i class="ri-error-warning-line me-1"></i>{{ $message }}</p>@enderror
                                <div id="galleryPreview" class="gallery-preview-grid mt-3"></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-theme px-4">Update product</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@php
    $__subCatRoutePlaceholder = '999999999';
    $__subCategoriesFetchUrl = route('get-sub-categories', ['category_id' => $__subCatRoutePlaceholder]);
@endphp
<style>
/* ── Image upload drop-zones ──────────────────────────────────────── */
.img-dropzone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 190px;
    border: 2px dashed #d0d7e2;
    border-radius: 18px;
    background: linear-gradient(140deg,#f8f9fb 0%,#f3f4f8 100%);
    cursor: pointer;
    transition: border-color .22s ease, background .22s ease, box-shadow .22s ease, color .22s ease;
    text-align: center;
    padding: 30px 20px;
    position: relative;
    overflow: hidden;
    color: #8e9aaa;
    user-select: none;
    margin-bottom: 0;
}
.img-dropzone:hover,
.img-dropzone.drag-over {
    border-color: #6366f1;
    background: linear-gradient(140deg,#eef2ff 0%,#f0f1ff 100%);
    box-shadow: 0 0 0 4px rgba(99,102,241,.1);
    color: #6366f1;
}
.img-dropzone.has-error {
    border-color: #f87171;
    background: linear-gradient(140deg,#fff5f5 0%,#fef2f2 100%);
    color: #ef4444;
}
.img-dropzone [class*="ri-image-add"],
.img-dropzone [class*="ri-gallery"] { font-size: 40px; line-height: 1; }

/* Compact gallery drop zone */
.img-dropzone--sm {
    min-height: 80px;
    padding: 16px 20px;
    flex-direction: row;
    gap: 14px;
    text-align: left;
}
.img-dropzone--sm [class*="ri-"] { font-size: 30px; flex-shrink: 0; }

/* Thumbnail in-zone preview */
.dropzone-placeholder { pointer-events: none; }
.dropzone-preview {
    position: absolute;
    inset: 0;
}
.dropzone-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.dropzone-overlay {
    position: absolute;
    inset: 0;
    background: rgba(10,10,30,.46);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .3px;
    opacity: 0;
    transition: opacity .2s;
    backdrop-filter: blur(3px);
}
.img-dropzone:hover .dropzone-overlay { opacity: 1; }

/* Gallery thumbnail grid */
.gallery-preview-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.gallery-thumb {
    position: relative;
    width: 78px;
    height: 78px;
    border-radius: 12px;
    overflow: hidden;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    flex-shrink: 0;
    transition: box-shadow .18s;
}
.gallery-thumb:hover { box-shadow: 0 4px 14px rgba(0,0,0,.14); }
.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .22s;
}
.gallery-thumb:hover img { transform: scale(1.06); }
.gallery-thumb-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(10,10,30,.6);
    color: #fff;
    border: none;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
    opacity: 0;
    transition: opacity .15s, background .15s;
    backdrop-filter: blur(2px);
    line-height: 1;
}
.gallery-thumb:hover .gallery-thumb-remove { opacity: 1; }
.gallery-thumb-remove:hover { background: #ef4444; }
</style>
<script>
// Handle gallery image deletion - GLOBAL function for onclick handler
function markImageForDelete(btn, event) {
    event.preventDefault();
    console.log('Delete button clicked');
    var imageName = btn.getAttribute('data-image');
    console.log('Image to delete:', imageName);
    var imageWrapper = btn.closest('.gallery-thumb');
    console.log('Image wrapper found:', !!imageWrapper);
    var deleteInput = document.getElementById('gallery_images_to_delete');
    console.log('Delete input found:', !!deleteInput);
    
    if (!deleteInput) {
        console.error('gallery_images_to_delete input not found!');
        return;
    }
    
    var deletedImages = deleteInput.value ? deleteInput.value.split(',') : [];
    console.log('Current deleted images:', deletedImages);
    
    if (!deletedImages.includes(imageName)) {
        deletedImages.push(imageName);
    }
    
    deleteInput.value = deletedImages.join(',');
    console.log('Updated input value:', deleteInput.value);
    
    if (imageWrapper) {
        imageWrapper.remove();
        console.log('Image wrapper removed from DOM');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-product-name]').forEach(function (input) {
        input.addEventListener('input', function () {
            // Strip special characters (keep letters, digits, space and &()-/.,)
            var val = this.value.replace(/[^a-zA-Z0-9 &()\-\/.,]/g, '');
            // Collapse any run of the same character exceeding 3
            val = val.replace(/(.)(\1{3,})/g, '$1$1$1');
            if (val !== this.value) { this.value = val; }
        });
    });

    var mrpInput      = document.getElementById('mrp_price');
    var discountInput = document.getElementById('discount_pct');
    var priceDisplay  = document.getElementById('price_display');
    var priceHidden   = document.getElementById('price');

    function calcDiscountedPrice() {
        var mrp  = parseFloat(mrpInput ? mrpInput.value : '');
        var disc = parseFloat(discountInput ? discountInput.value : '');
        if (Number.isNaN(mrp) || mrp < 0) { return; }
        if (Number.isNaN(disc) || disc < 0) { disc = 0; }
        if (disc > 100) { disc = 100; }
        var finalPrice = Math.round(mrp * (1 - disc / 100) * 100) / 100;
        if (priceDisplay) { priceDisplay.value = finalPrice.toFixed(2); }
        if (priceHidden)  { priceHidden.value  = finalPrice.toFixed(2); }
    }

    if (mrpInput)      { mrpInput.addEventListener('input', calcDiscountedPrice); }
    if (discountInput) { discountInput.addEventListener('input', calcDiscountedPrice); }
    calcDiscountedPrice();

    var wholesaleLabel = {!! json_encode(\App\Models\Product::TARGET_WHOLESALER) !!};
    var segmentSelect = document.getElementById('target_user_type');
    var minWrap = document.getElementById('wholesalerMinQtyWrap');
    var minInput = document.getElementById('min_quantity');
    function syncWholesalerMinQty() {
        if (!segmentSelect || !minWrap || !minInput) return;
        var isWholesale = segmentSelect.value === wholesaleLabel;
        minWrap.classList.toggle('d-none', !isWholesale);
        minInput.required = isWholesale;
        if (!isWholesale) {
            minInput.value = '';
        }
    }
    if (segmentSelect) {
        segmentSelect.addEventListener('change', syncWholesalerMinQty);
        syncWholesalerMinQty();
    }

    function setOptions(selectEl, items, valueKey, labelKey, placeholder, selectedValue) {
        if (!selectEl) return;
        selectEl.innerHTML = '<option value="">' + placeholder + '</option>';
        var list = Array.isArray(items) ? items : [];
        list.forEach(function (item) {
            var option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = item[labelKey];
            if (selectedValue !== '' && selectedValue !== null && selectedValue !== undefined && String(selectedValue) === String(item[valueKey])) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });
    }

    var subCatRouteTemplate = {!! json_encode($__subCategoriesFetchUrl) !!};
    var subCatRoutePlaceholder = {!! json_encode($__subCatRoutePlaceholder) !!};
    function subCategoriesFetchUrl(categoryId) {
        return String(subCatRouteTemplate).split(String(subCatRoutePlaceholder)).join(encodeURIComponent(String(categoryId)));
    }

    var categoryEl = document.getElementById('category_id');
    var subCategoryEl = document.getElementById('sub_category_id');
    var initialSubCategoryId = {!! json_encode(old('sub_category_id', $product->sub_category_id)) !!};

    function loadSubCategories(categoryId, selectedSubId) {
        if (!subCategoryEl) return;
        if (!categoryId) {
            setOptions(subCategoryEl, [], 'sub_category_id', 'sub_cat_name', 'Select sub-category', '');
            return;
        }
        var pick = (selectedSubId === undefined) ? initialSubCategoryId : selectedSubId;

        fetch(subCategoriesFetchUrl(categoryId), { headers: { Accept: 'application/json' } })
            .then(function (res) {
                if (!res.ok) throw new Error('Request failed');
                return res.json();
            })
            .then(function (data) {
                var rows = Array.isArray(data) ? data : [];
                setOptions(subCategoryEl, rows, 'sub_category_id', 'sub_cat_name', 'Select sub-category', pick);
            })
            .catch(function () {
                setOptions(subCategoryEl, [], 'sub_category_id', 'sub_cat_name', 'Select sub-category', '');
            });
    }

    if (categoryEl) {
        categoryEl.addEventListener('change', function () { loadSubCategories(this.value, ''); });
        if (categoryEl.value) {
            loadSubCategories(categoryEl.value);
        }
    }

    // ── Thumbnail ──────────────────────────────────────────────────────
    var thumbInput       = document.getElementById('product_image');
    var thumbZone        = document.getElementById('thumbDropZone');
    var thumbPlaceholder = document.getElementById('thumbPlaceholder');
    var thumbPreview     = document.getElementById('thumbPreview');
    var thumbImg         = document.getElementById('thumbPreviewImg');

    function showThumbPreview(file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            thumbImg.src = e.target.result;
            thumbPlaceholder.style.display = 'none';
            thumbPreview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }

    thumbInput.addEventListener('change', function () {
        if (this.files[0]) showThumbPreview(this.files[0]);
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        thumbZone.addEventListener(evt, function (e) { e.preventDefault(); thumbZone.classList.add('drag-over'); });
    });
    ['dragleave', 'dragend'].forEach(function (evt) {
        thumbZone.addEventListener(evt, function () { thumbZone.classList.remove('drag-over'); });
    });
    thumbZone.addEventListener('drop', function (e) {
        e.preventDefault();
        thumbZone.classList.remove('drag-over');
        var file = e.dataTransfer.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        var dt = new DataTransfer(); dt.items.add(file);
        thumbInput.files = dt.files;
        showThumbPreview(file);
    });

    // ── Gallery (new uploads) ──────────────────────────────────────────
    var galleryInput   = document.getElementById('gallery_images');
    var galleryPreview = document.getElementById('galleryPreview');
    var galleryZone    = document.getElementById('galleryDropZone');
    var galleryFiles   = [];

    function syncGalleryInput() {
        var dt = new DataTransfer();
        galleryFiles.forEach(function (f) { dt.items.add(f); });
        galleryInput.files = dt.files;
    }

    function renderGalleryPreviews() {
        galleryPreview.innerHTML = '';
        galleryFiles.forEach(function (file, idx) {
            (function (i, f) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var wrap = document.createElement('div');
                    wrap.className = 'gallery-thumb';
                    var img = document.createElement('img');
                    img.src = e.target.result; img.alt = '';
                    var btn = document.createElement('button');
                    btn.type = 'button'; btn.className = 'gallery-thumb-remove';
                    btn.innerHTML = '<i class="ri-close-line"></i>';
                    btn.addEventListener('click', function (ev) {
                        ev.preventDefault(); ev.stopPropagation();
                        galleryFiles.splice(i, 1);
                        renderGalleryPreviews();
                        syncGalleryInput();
                    });
                    wrap.appendChild(img); wrap.appendChild(btn);
                    galleryPreview.appendChild(wrap);
                };
                reader.readAsDataURL(f);
            })(idx, file);
        });
    }

    galleryInput.addEventListener('change', function () {
        Array.prototype.forEach.call(this.files, function (f) { galleryFiles.push(f); });
        renderGalleryPreviews();
        syncGalleryInput();
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        galleryZone.addEventListener(evt, function (e) { e.preventDefault(); galleryZone.classList.add('drag-over'); });
    });
    ['dragleave', 'dragend'].forEach(function (evt) {
        galleryZone.addEventListener(evt, function () { galleryZone.classList.remove('drag-over'); });
    });
    galleryZone.addEventListener('drop', function (e) {
        e.preventDefault();
        galleryZone.classList.remove('drag-over');
        Array.prototype.forEach.call(e.dataTransfer.files, function (f) {
            if (f.type.startsWith('image/')) galleryFiles.push(f);
        });
        renderGalleryPreviews();
        syncGalleryInput();
    });

});

        /* ── Description character counter ─────────────────────────── */
        (function () {
            var textarea = document.getElementById('product_description');
            var counter  = document.getElementById('desc_count');
            if (!textarea || !counter) return;
            textarea.addEventListener('input', function () {
                counter.textContent = textarea.value.length;
                counter.style.color = textarea.value.length >= 220 ? '#dc3545' : '';
            });
        })();
</script>
@endsection
