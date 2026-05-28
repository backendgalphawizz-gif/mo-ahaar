@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $isVendorPanel = (int) (session('role_type') ?? 0) === 3;
@endphp
<div class="page-body">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="title-header option-title d-flex flex-wrap align-items-center gap-2 mb-4">
                    <h5 class="mb-0">Food Management</h5>
                    <a class="btn btn-danger btn-sm ms-auto" href="{{ route($isVendorPanel ? 'vendor.add-product' : 'admin.add-product') }}">
                        <i class="ri-add-line me-1"></i>Add Food
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

                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
                            <form method="GET" action="{{ route($isVendorPanel ? 'vendor.products' : 'admin.products') }}" class="d-flex align-items-center gap-2" style="max-width: 360px;">
                                <i class="ri-search-line"></i>
                                <input type="text" name="search" class="form-control form-control-sm" value="{{ $search ?? '' }}" placeholder="Search food items...">
                                <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                            </form>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ri-download-line me-1"></i>Export Data
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.products.export-excel', array_filter(['search' => $search ?? ''])) }}">Export Excel</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.products.export-pdf', array_filter(['search' => $search ?? ''])) }}">Export PDF</a></li>
                                </ul>
                            </div>
                        </div>
                      
                        <div class="table-responsive">
                            <table class="table table-modern align-middle" id="table_id">
                                <thead>
                                    <tr>
                                        <th>SR. NO.</th>
                                        <th>FOOD ITEM</th>
                                        <th>PRICE</th>
                                        <th>TYPE</th>
                                        <th>RATING</th>
                                        <th>STATUS</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($allProducts as $product)
                                        <tr>
                                            <td>{{ $allProducts->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ !empty($product->product_image) ? asset('public/uploads/products/' . $product->product_image) : asset('public/assets/images/product/1.png') }}" alt="food" style="width:34px;height:34px;border-radius:6px;object-fit:cover;">
                                                    <div>
                                                        <div class="fw-semibold">{{ $product->product_name }}</div>
                                                        <small class="text-muted">{{ optional($product->created_at)->format('Y-m-d') }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>₹{{ number_format((float) ($product->price ?? 0), 0) }}</td>
                                            <td>
                                                @if(strtolower((string) $product->product_type) === 'veg')
                                                    <span class="text-success small"><i class="ri-seedling-line me-1"></i>Veg</span>
                                                @else
                                                    <span class="text-danger small"><i class="ri-fire-line me-1"></i>Non-Veg</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ number_format((float) ($product->avg_rating ?? 0), 1) }}
                                                <small class="text-warning">★</small>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" action="{{ route($isVendorPanel ? 'vendor.products.toggle-status' : 'admin.products.toggle-status', $product->product_id) }}" class="status-toggle-form m-0">
                                                    @csrf
                                                    @php
                                                        $isProductActive = (int) $product->is_active_status === 1;
                                                    @endphp
                                                    <label class="status-switch" title="Toggle status">
                                                        <input type="checkbox" aria-label="Toggle product status" {{ $isProductActive ? 'checked' : '' }} onchange="this.form.submit()">
                                                        <span class="status-slider"></span>
                                                    </label>
                                                </form>
                                            </td>
                                            <td>
                                                <ul class="d-flex gap-2 mb-0 list-unstyled">
                                                    <li>
                                                        <a href="{{ route($isVendorPanel ? 'vendor.view-product' : 'admin.view-product', $product->product_id) }}" title="View">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route($isVendorPanel ? 'vendor.edit-product' : 'admin.edit-product', ['id' => $product->product_id]) }}" title="Edit">
                                                            <i class="ri-pencil-line"></i>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)" title="Delete" data-product-id="{{ $product->product_id }}" data-product-name="{{ $product->product_name }}" data-bs-toggle="modal" data-bs-target="#deleteProductModal">
                                                            <i class="ri-delete-bin-line text-danger"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No products found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($allProducts->hasPages())
                            <div class="product-pagination-wrap d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
                                <p class="text-muted mb-0 small">
                                    Showing {{ $allProducts->firstItem() }} to {{ $allProducts->lastItem() }} of {{ $allProducts->total() }} products
                                </p>
                                <div class="product-pagination">
                                    {{ $allProducts->onEachSide(1)->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Delete Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <i class="ri-error-warning-line text-danger" style="font-size:48px;"></i>
                                <p class="mt-3 mb-1">Are you sure you want to delete this product?</p>
                                <p class="mb-0 text-muted" id="deleteProductName"></p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <a href="#" class="btn btn-danger" id="confirmDeleteProductBtn">Yes, Delete</a>
                            </div>
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
.status-switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 24px;
}
.status-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.status-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: linear-gradient(135deg, #b8872b 0%, #c9973a 50%, #e0b45a 100%);
    transition: .25s;
    border-radius: 24px;
}
.status-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    top: 3px;
    background-color: #fff;
    transition: .25s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,.25);
}
.status-switch input:checked + .status-slider {
    background-color: #22c55e;
}
.status-switch input:checked + .status-slider:before {
    transform: translateX(22px);
}

.product-pagination .pagination {
    margin-bottom: 0;
    gap: 6px;
    flex-wrap: wrap;
}

.product-pagination .page-item {
    margin: 0;
}

.product-pagination .page-item .page-link {
    border: 1px solid #d9e2ec;
    border-radius: 8px;
    color: #334155;
    min-width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
    font-size: 13px;
    font-weight: 600;
    background: #fff;
    box-shadow: none;
}

.product-pagination .page-item.active .page-link {
    background: #ed1c24;
    border-color: #ed1c24;
    color: #fff;
}

.product-pagination .page-item .page-link:hover {
    background: #f0fdfa;
    border-color: #0da487;
    color: #0f766e;
}

.product-pagination .page-item.disabled .page-link {
    color: #94a3b8;
    background: #f8fafc;
    border-color: #e2e8f0;
}

@media (max-width: 767px) {
    .product-pagination-wrap {
        align-items: flex-start !important;
    }

    .product-pagination .page-item .page-link {
        min-width: 34px;
        height: 34px;
        font-size: 12px;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('deleteProductModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;

            var productId = trigger.getAttribute('data-product-id');
            var productName = trigger.getAttribute('data-product-name') || 'this product';

            document.getElementById('deleteProductName').textContent = productName;
            document.getElementById('confirmDeleteProductBtn').href = '{{ $isVendorPanel ? url('/vendor/delete-product') : url('/admin/delete-product') }}/' + productId;
        });
    }

    document.querySelectorAll('.product-approval-select').forEach(function (selectElement) {
        selectElement.dataset.lastValue = selectElement.value;

        var applySelectStatusClass = function (element, status) {
            element.classList.remove('status-approved', 'status-pending', 'status-rejected');
            element.classList.add('status-' + status);
        };

        var humanize = function (status) {
            if (status === '1') return 'Approved';
            if (status === '2') return 'Pending';
            if (status === '3') return 'Rejected';
            return status || '';
        };

        var normalizeStatus = function (status) {
            if (status === '1') return 'approved';
            if (status === '2') return 'pending';
            if (status === '3') return 'rejected';
            return status || '';
        };

        applySelectStatusClass(selectElement, normalizeStatus(selectElement.value));

        selectElement.addEventListener('change', function () {
            var newStatus = this.value;
            var oldStatus = this.dataset.lastValue || this.dataset.currentStatus || '';
            var form = this.closest('form');
            var productName = this.dataset.productName || 'this product';

            if (!form || newStatus === oldStatus) {
                return;
            }

            applySelectStatusClass(this, normalizeStatus(newStatus));

            Swal.fire({
                title: 'Change Product Approval Status?',
                html: '<strong>' + productName + '</strong><br>from <strong>' + humanize(oldStatus) + '</strong> to <strong>' + humanize(newStatus) + '</strong>.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d9488',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Change Status',
                cancelButtonText: 'No, Keep Current'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    this.value = oldStatus;
                    applySelectStatusClass(this, normalizeStatus(oldStatus));
                }
            });
        });
    });
});
</script>
@endsection
