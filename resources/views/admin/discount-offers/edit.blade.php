@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-3 mb-2">
            <a href="{{ route('admin.discount-offers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
            <div>
                <h5 class="mb-0">Edit Promo Code</h5>
                <small class="text-muted">Update existing promotional offer details</small>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-semibold mb-1">Please fix the errors below.</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.discount-offers.update', $discountOffer->id) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.discount-offers._form')
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-theme px-4">Update Offer</button>
                <a href="{{ route('admin.discount-offers.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var applyTo   = document.getElementById('applyTo');
    var prodWrap  = document.getElementById('productPickerWrap');
    var catWrap   = document.getElementById('categoryPickerWrap');
    var discType  = document.getElementById('discountType');
    var discSym   = document.getElementById('discountSymbol');
    var discHint  = document.getElementById('discountHint');
    var discInput = document.getElementById('discountValue');

    function updateApply() {
        var v = applyTo.value;
        prodWrap.classList.toggle('d-none', v !== 'specific_products');
        catWrap.classList.toggle('d-none', v !== 'specific_categories');
        document.getElementById('productPicker').disabled  = (v !== 'specific_products');
        document.getElementById('categoryPicker').disabled = (v !== 'specific_categories');
    }

    function updateDiscountUI() {
        if (discType.value === 'percentage') {
            discSym.textContent = '%';
            discHint.textContent = 'Enter a percentage (0–100).';
            discInput.max = '100';
        } else {
            discSym.textContent = '₹';
            discHint.textContent = 'Enter a fixed rupee amount.';
            discInput.removeAttribute('max');
        }
    }

    applyTo.addEventListener('change', updateApply);
    discType.addEventListener('change', updateDiscountUI);
    updateApply();
    updateDiscountUI();
})();
</script>
@endsection
