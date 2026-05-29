@php
    use App\Models\Product;

    $gstTaxes = $gstTaxes ?? collect();
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
    $selectedGstTaxId = old('gst_tax_id');
    if ($selectedGstTaxId === null && isset($product) && $product->gst_percentage !== null && $product->gst_percentage !== '') {
        $match = $gstTaxes->first(fn ($tax) => (float) $tax->percentage === (float) $product->gst_percentage);
        $selectedGstTaxId = $match?->id;
    }
    $gstType = old('gst_calculation_type', $product->gst_calculation_type ?? Product::GST_EXCLUDED);
@endphp

<div class="col-12">
    <h6 class="text-muted small text-uppercase fw-semibold mb-0 pt-1">GST &amp; Tax</h6>
    <hr class="mt-2 mb-0">
</div>

<div class="col-md-6">
    <label class="form-label" for="gst_tax_id">GST Rate</label>
    <select name="gst_tax_id" id="gst_tax_id" class="form-select @error('gst_tax_id') is-invalid @enderror">
        <option value="">No GST / 0%</option>
        @foreach($gstTaxes as $tax)
            <option value="{{ $tax->id }}"
                data-percentage="{{ $tax->percentage }}"
                {{ (string) $selectedGstTaxId === (string) $tax->id ? 'selected' : '' }}>
                {{ $tax->label ?? ($tax->name . ' (' . number_format((float) $tax->percentage, 2) . '%)') }}
            </option>
        @endforeach
    </select>
    @if($gstTaxes->isEmpty())
        <small class="text-warning d-block mt-1">
            No active GST rates found.
            @if(!$isVendorPanel)
                <a href="{{ route('admin.gst-taxes.create') }}">Add GST tax</a>
            @else
                Contact admin to configure GST rates.
            @endif
        </small>
    @else
        <small class="text-muted d-block mt-1">Select tax slab (e.g. 5%, 12%, 18%)</small>
    @endif
    @include('admin.partials.field-error', ['field' => 'gst_tax_id'])
</div>

<div class="col-md-6">
    <label class="form-label" for="gst_calculation_type">Tax Type <span class="text-danger">*</span></label>
    <select name="gst_calculation_type" id="gst_calculation_type" class="form-select @error('gst_calculation_type') is-invalid @enderror" required>
        <option value="{{ Product::GST_EXCLUDED }}" {{ $gstType === Product::GST_EXCLUDED ? 'selected' : '' }}>
            GST Excluded — tax added on top of price
        </option>
        <option value="{{ Product::GST_INCLUDED }}" {{ $gstType === Product::GST_INCLUDED ? 'selected' : '' }}>
            GST Included — price already includes tax
        </option>
    </select>
    <small class="text-muted d-block mt-1" id="gst_type_help">
        Customer pays base price plus GST.
    </small>
    @include('admin.partials.field-error', ['field' => 'gst_calculation_type'])
</div>

<div class="col-12">
    <div class="alert alert-light border small mb-0 py-2" id="gst_preview_box">
        <strong>GST preview:</strong>
        <span id="gst_preview_text" class="text-muted">Enter price and select GST to see breakdown.</span>
    </div>
</div>
