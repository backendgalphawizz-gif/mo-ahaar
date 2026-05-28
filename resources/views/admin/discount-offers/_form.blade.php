{{--
    Shared form partial for Discount Offer create/edit.
    Expects: $products (Product collection), $categories (ProductCategory collection)
    Optionally expects $discountOffer for edit mode.
    NOTE: JS for dynamic fields is included in the parent create/edit view's @section('scripts').
--}}
@php
    $isEdit   = isset($discountOffer);
    $oldApply = old('apply_to', $isEdit ? $discountOffer->apply_to : 'all');

    $selectedProducts   = old('product_ids',  $isEdit ? (array)($discountOffer->product_ids ?? [])  : []);
    $selectedCategories = old('category_ids', $isEdit ? (array)($discountOffer->category_ids ?? []) : []);
@endphp

{{-- Basic Info --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Basic Information</div>
    <div class="card-body">

        <div class="mb-3">
            <label class="form-label-title">Promo Code <span class="text-danger">*</span></label>
            <input type="text" name="title"
                   class="form-control @error('title') is-invalid @enderror"
                   placeholder="E.G., SUMMER50, FLAT300"
                   value="{{ old('title', $isEdit ? $discountOffer->title : '') }}"
                   maxlength="150" required>
            <small class="text-muted">Customers will enter this exact code.</small>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label-title">Display Message</label>
            <textarea name="description"
                      class="form-control @error('description') is-invalid @enderror"
                      rows="2"
                      placeholder="Optional: internal notes about this offer">{{ old('description', $isEdit ? $discountOffer->description : '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-title">Discount Type <span class="text-danger">*</span></label>
                <select name="discount_type" id="discountType"
                        class="form-select @error('discount_type') is-invalid @enderror" required>
                    <option value="percentage" {{ old('discount_type', $isEdit ? $discountOffer->discount_type : 'percentage') === 'percentage' ? 'selected' : '' }}>
                        Percentage (%)
                    </option>
                    <option value="fixed" {{ old('discount_type', $isEdit ? $discountOffer->discount_type : '') === 'fixed' ? 'selected' : '' }}>
                        Fixed Amount (₹)
                    </option>
                </select>
                @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label-title">Discount Value <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text" id="discountSymbol">%</span>
                    <input type="number" name="discount_value" id="discountValue" step="0.01" min="0.01"
                           class="form-control @error('discount_value') is-invalid @enderror"
                           placeholder="e.g. 10"
                           value="{{ old('discount_value', $isEdit ? $discountOffer->discount_value : '') }}"
                           required>
                    @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="text-muted" id="discountHint">Enter a percentage (0–100).</small>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label-title">Status <span class="text-danger">*</span></label>
            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                <option value="1" {{ old('is_active', $isEdit ? (string)$discountOffer->is_active : '1') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $isEdit ? (string)$discountOffer->is_active : '1') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

    </div>
</div>

{{-- Scope: which products/categories this applies to --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Apply Offer To</div>
    <div class="card-body">

        <div class="mb-3">
            <label class="form-label-title">Scope <span class="text-danger">*</span></label>
            <select name="apply_to" id="applyTo"
                    class="form-select @error('apply_to') is-invalid @enderror" required>
                <option value="all" {{ $oldApply === 'all' ? 'selected' : '' }}>All Products</option>
                <option value="specific_products" {{ $oldApply === 'specific_products' ? 'selected' : '' }}>Specific Products</option>
                <option value="specific_categories" {{ $oldApply === 'specific_categories' ? 'selected' : '' }}>Specific Categories</option>
            </select>
            @error('apply_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Specific Products picker --}}
        <div id="productPickerWrap" class="{{ $oldApply === 'specific_products' ? '' : 'd-none' }}">
            <label class="form-label-title">Select Products</label>
            <select name="product_ids[]" id="productPicker"
                    class="form-select @error('product_ids') is-invalid @enderror"
                    multiple size="8">
                @foreach($products as $product)
                    <option value="{{ $product->product_id }}"
                        {{ in_array($product->product_id, $selectedProducts) ? 'selected' : '' }}>
                        {{ $product->product_name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl / Cmd to select multiple products.</small>
            @error('product_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Specific Categories picker --}}
        <div id="categoryPickerWrap" class="{{ $oldApply === 'specific_categories' ? '' : 'd-none' }}">
            <label class="form-label-title">Select Categories</label>
            <select name="category_ids[]" id="categoryPicker"
                    class="form-select @error('category_ids') is-invalid @enderror"
                    multiple size="6">
                @foreach($categories as $cat)
                    <option value="{{ $cat->category_id }}"
                        {{ in_array($cat->category_id, $selectedCategories) ? 'selected' : '' }}>
                        {{ $cat->category_name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl / Cmd to select multiple categories.</small>
            @error('category_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

    </div>
</div>

{{-- Validity period --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Validity Period</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-title">Valid From</label>
                <input type="date" name="valid_from"
                       class="form-control @error('valid_from') is-invalid @enderror"
                       value="{{ old('valid_from', $isEdit && $discountOffer->valid_from ? $discountOffer->valid_from->format('Y-m-d') : '') }}">
                <small class="text-muted">Leave blank for no start restriction.</small>
                @error('valid_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label-title">Valid Until</label>
                <input type="date" name="valid_until"
                       class="form-control @error('valid_until') is-invalid @enderror"
                       value="{{ old('valid_until', $isEdit && $discountOffer->valid_until ? $discountOffer->valid_until->format('Y-m-d') : '') }}">
                <small class="text-muted">Leave blank for no end restriction.</small>
                @error('valid_until')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Conditions --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Conditions <small class="text-muted fw-normal">(all fields optional)</small></div>
    <div class="card-body">

        <h6 class="text-muted mb-2 small text-uppercase">Quantity per Line Item</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label-title">Minimum Quantity</label>
                <input type="number" name="min_quantity" min="1"
                       class="form-control @error('min_quantity') is-invalid @enderror"
                       placeholder="e.g. 2"
                       value="{{ old('min_quantity', $isEdit ? $discountOffer->min_quantity : '') }}">
                <small class="text-muted">Offer applies only if the item quantity ≥ this value.</small>
                @error('min_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label-title">Maximum Quantity</label>
                <input type="number" name="max_quantity" min="1"
                       class="form-control @error('max_quantity') is-invalid @enderror"
                       placeholder="e.g. 100"
                       value="{{ old('max_quantity', $isEdit ? $discountOffer->max_quantity : '') }}">
                <small class="text-muted">Offer applies only if the item quantity ≤ this value.</small>
                @error('max_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <h6 class="text-muted mb-2 small text-uppercase">Cart Total Amount</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-title">Minimum Cart Amount (₹)</label>
                <input type="number" name="min_cart_amount" step="0.01" min="0"
                       class="form-control @error('min_cart_amount') is-invalid @enderror"
                       placeholder="e.g. 500"
                       value="{{ old('min_cart_amount', $isEdit ? $discountOffer->min_cart_amount : '') }}">
                <small class="text-muted">Offer applies only if cart subtotal ≥ this amount.</small>
                @error('min_cart_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label-title">Maximum Cart Amount (₹)</label>
                <input type="number" name="max_cart_amount" step="0.01" min="0"
                       class="form-control @error('max_cart_amount') is-invalid @enderror"
                       placeholder="e.g. 5000"
                       value="{{ old('max_cart_amount', $isEdit ? $discountOffer->max_cart_amount : '') }}">
                <small class="text-muted">Offer applies only if cart subtotal ≤ this amount.</small>
                @error('max_cart_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

    </div>
</div>
