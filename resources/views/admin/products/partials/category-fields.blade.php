@php
    $selectedCategoryId = old('category_id', $selectedCategoryId ?? '');
    $selectedSubCategoryId = old('sub_category_id', $selectedSubCategoryId ?? '');
    $subCategoryList = $subCategoryList ?? collect();
@endphp
<div class="col-md-6">
    <label class="form-label">Category <span class="text-danger">*</span></label>
    <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
        <option value="">Select category</option>
        @foreach($categoryList as $category)
            <option value="{{ $category->category_id }}" {{ (string) $selectedCategoryId === (string) $category->category_id ? 'selected' : '' }}>
                {{ $category->category_name }}
            </option>
        @endforeach
    </select>
    @include('admin.partials.field-error', ['field' => 'category_id'])
</div>
<div class="col-md-6">
    <label class="form-label">Sub Category</label>
    <select name="sub_category_id" id="sub_category_id" class="form-select @error('sub_category_id') is-invalid @enderror" {{ $selectedCategoryId ? '' : 'disabled' }}>
        <option value="">Select sub category (optional)</option>
        @foreach($subCategoryList as $subCategory)
            <option value="{{ $subCategory->sub_category_id }}" {{ (string) $selectedSubCategoryId === (string) $subCategory->sub_category_id ? 'selected' : '' }}>
                {{ $subCategory->sub_cat_name }}
            </option>
        @endforeach
    </select>
    @include('admin.partials.field-error', ['field' => 'sub_category_id'])
</div>
