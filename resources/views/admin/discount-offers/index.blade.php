@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0"><i class="ri-price-tag-3-line me-2"></i>Discount Offers</h5>
            <a href="{{ route('admin.discount-offers.create') }}" class="btn btn-theme btn-sm ms-auto">
                <i class="ri-add-line me-1"></i>Add Offer
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Search --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('admin.discount-offers.index') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" style="max-width:280px"
                           placeholder="Search by title…" value="{{ $search }}">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Search</button>
                    @if($search)
                        <a href="{{ route('admin.discount-offers.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card card-table border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table all-package theme-table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Discount</th>
                                <th>Apply To</th>
                                <th>Validity</th>
                                <th>Conditions</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($offers as $offer)
                                <tr id="offer-row-{{ $offer->id }}">
                                    <td>{{ $offers->firstItem() + $loop->index }}</td>
                                    <td class="fw-medium">{{ $offer->title }}</td>
                                    <td>
                                        @if($offer->discount_type === 'percentage')
                                            <span class="badge bg-info text-dark">{{ number_format((float)$offer->discount_value, 2) }}% Off</span>
                                        @else
                                            <span class="badge bg-warning text-dark">₹{{ number_format((float)$offer->discount_value, 2) }} Off</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($offer->apply_to === 'all')
                                            <span class="badge bg-success">All Products</span>
                                        @elseif($offer->apply_to === 'specific_products')
                                            <span class="badge bg-primary">
                                                {{ count((array)($offer->product_ids ?? [])) }} Product(s)
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                {{ count((array)($offer->category_ids ?? [])) }} Category(s)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($offer->valid_from || $offer->valid_until)
                                            {{ $offer->valid_from ? $offer->valid_from->format('d M Y') : '∞' }}
                                            &mdash;
                                            {{ $offer->valid_until ? $offer->valid_until->format('d M Y') : '∞' }}
                                        @else
                                            <span class="text-muted">Always</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($offer->min_quantity || $offer->max_quantity)
                                            Qty: {{ $offer->min_quantity ?? '–' }}–{{ $offer->max_quantity ?? '∞' }}<br>
                                        @endif
                                        @if($offer->min_cart_amount || $offer->max_cart_amount)
                                            Cart: ₹{{ $offer->min_cart_amount ? number_format((float)$offer->min_cart_amount,0) : '–' }}
                                            –₹{{ $offer->max_cart_amount ? number_format((float)$offer->max_cart_amount,0) : '∞' }}
                                        @endif
                                        @if(!$offer->min_quantity && !$offer->max_quantity && !$offer->min_cart_amount && !$offer->max_cart_amount)
                                            None
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch mb-0" title="Toggle status">
                                            <input class="form-check-input offer-status-toggle" type="checkbox"
                                                   data-id="{{ $offer->id }}"
                                                   data-url="{{ route('admin.discount-offers.toggle-status', $offer->id) }}"
                                                   {{ $offer->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <ul class="d-flex gap-2 mb-0 list-unstyled">
                                            <li>
                                                <a href="{{ route('admin.discount-offers.show', $offer->id) }}" title="View">
                                                    <i class="ri-eye-line text-info"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.discount-offers.edit', $offer->id) }}" title="Edit">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)"
                                                   class="delete-offer-btn"
                                                   data-form-id="delete-offer-form-{{ $offer->id }}"
                                                   data-name="{{ $offer->title }}"
                                                   title="Delete">
                                                    <i class="ri-delete-bin-line text-danger"></i>
                                                </a>
                                                <form id="delete-offer-form-{{ $offer->id }}"
                                                      method="POST"
                                                      action="{{ route('admin.discount-offers.destroy', $offer->id) }}"
                                                      class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No discount offers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($offers->hasPages())
                    <div class="mt-3">
                        {{ $offers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Delete confirmation
    document.querySelectorAll('.delete-offer-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var name = this.dataset.name;
            if (confirm('Delete offer "' + name + '"? This cannot be undone.')) {
                document.getElementById(this.dataset.formId).submit();
            }
        });
    });

    // Toggle status via AJAX
    document.querySelectorAll('.offer-status-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var url = this.dataset.url;
            var checkbox = this;
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.status) {
                    checkbox.checked = !checkbox.checked; // revert
                }
            })
            .catch(function () {
                checkbox.checked = !checkbox.checked; // revert on error
            });
        });
    });

});
</script>
@endsection
