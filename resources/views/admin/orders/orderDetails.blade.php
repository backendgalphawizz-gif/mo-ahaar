@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
@php
    $customer = $order->customer?->user;
    $driver = $order->deliveryAssignment?->driver;
    $shipAddr = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address;
    $commission = $order->adminCommissionAmount();
    $statusBadge = match (strtolower((string) $order->order_status)) {
        'delivered', 'completed', 'success' => 'badge-soft-success',
        'cancelled', 'rejected' => 'badge-soft-danger',
        default => 'badge-soft-info',
    };
@endphp
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
            <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
            <div class="flex-grow-1">
                <h5 class="mb-0">Order Details: {{ $order->order_number }}</h5>
            </div>
            <a href="{{ route('admin.order-invoice-pdf', $order->order_id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ri-file-download-line me-1"></i>Download Invoice
            </a>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Food Information</h6>
                        <p><strong>Foods:</strong> {{ $order->productSummary() }}</p>
                        <p><strong>Order Date:</strong> {{ optional($order->created_at)->format('d-m-Y') }}</p>
                        <p><strong>Order Status:</strong> <span class="badge {{ $statusBadge }}">{{ \App\Models\Orders::statusLabel($order->order_status) }}</span></p>
                        <p class="mb-0"><strong>Active Status:</strong>
                            <span class="badge {{ in_array($order->order_status, ['cancelled','rejected'], true) ? 'badge-soft-danger' : 'badge-soft-success' }}">
                                {{ in_array($order->order_status, ['cancelled','rejected'], true) ? 'Inactive' : 'Active' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Payment Details</h6>
                        <p><strong>Total Amount:</strong> ₹{{ number_format((float) $order->total_amount, 2) }}</p>
                        <p><strong>Admin Commission:</strong> <span class="text-success fw-semibold">₹{{ number_format($commission, 2) }}</span></p>
                        <p><strong>Payment Method:</strong> {{ strtoupper($order->payment_method ?? '—') }}</p>
                        <p class="mb-0"><strong>Payment Status:</strong>
                            <span class="badge {{ strtolower((string) $order->payment_status) === 'paid' ? 'badge-soft-success' : 'badge-soft-warning' }}">
                                {{ ucfirst($order->payment_status ?? 'pending') }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Customer Information</h6>
                        <p><strong>Name:</strong> {{ $customer->name ?? '—' }}</p>
                        <p><strong>Phone:</strong> {{ !empty($customer?->mobile) ? '+91 ' . $customer->mobile : '—' }}</p>
                        <p><strong>Email:</strong> {{ $customer->email ?? '—' }}</p>
                        <p class="mb-0"><strong>Address:</strong>
                            @if(is_array($shipAddr))
                                {{ collect([$shipAddr['address_line'] ?? null, $shipAddr['city'] ?? null, $shipAddr['state'] ?? null, $shipAddr['pincode'] ?? null])->filter()->implode(', ') ?: '—' }}
                            @else
                                {{ $order->shipping_address ?: '—' }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Vendor Information</h6>
                        <p><strong>Vendor Name:</strong> {{ $order->vendor?->business_name ?? '—' }}</p>
                        <p><strong>Vendor ID:</strong> {{ $order->vendor_id ? 'V-' . $order->vendor_id : '—' }}</p>
                        <p class="mb-0"><strong>Contact:</strong> {{ $order->vendor?->mobile ?? $order->vendor?->email ?? '—' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h6 class="mb-2">Delivery Information</h6>
                            <p class="mb-0"><strong>Delivery Boy:</strong>
                                @if($driver)
                                    {{ $driver->name }} (+91 {{ $driver->mobile }})
                                @else
                                    <span class="text-muted">Not Assigned</span>
                                @endif
                            </p>
                        </div>
                        @unless($driver)
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#assignDriverModalDetail">
                                Assign Delivery Boy
                            </button>
                        @endunless
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-header bg-transparent"><h6 class="mb-0">Line Items</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->orderItems as $item)
                                <tr>
                                    <td>{{ $item->product_name ?? ('Item #' . $item->item_id) }}</td>
                                    <td>{{ $item->sku ?: '—' }}</td>
                                    <td class="text-end">₹{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="text-end">{{ (int) $item->quantity }}</td>
                                    <td class="text-end">₹{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-3 text-muted">No items.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignDriverModalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.orders.assign-driver', $order->order_id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Delivery Boy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Assignment Mode</label>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="form-check">
                            <input class="form-check-input detail-assignment-mode" type="radio" name="assignment_mode" id="detail_assignment_mode_manual" value="manual" checked>
                            <label class="form-check-label" for="detail_assignment_mode_manual">Manual (admin selects driver)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input detail-assignment-mode" type="radio" name="assignment_mode" id="detail_assignment_mode_broadcast" value="broadcast">
                            <label class="form-check-label" for="detail_assignment_mode_broadcast">Automatic (notify nearby drivers, first acceptance wins)</label>
                        </div>
                    </div>
                    <div id="detailManualDriverWrap">
                        <label class="form-label">Select Driver</label>
                        <select name="driver_id" id="detail_driver_id" class="form-select">
                            <option value="">Choose driver...</option>
                            @foreach($availableDrivers ?? [] as $d)
                                <option value="{{ $d->user_id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <small class="text-muted d-none" id="detailBroadcastInfo">All nearby online drivers will be notified. Once one accepts, others cannot accept.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var manualRadio = document.getElementById('detail_assignment_mode_manual');
    var broadcastRadio = document.getElementById('detail_assignment_mode_broadcast');
    var manualWrap = document.getElementById('detailManualDriverWrap');
    var manualSelect = document.getElementById('detail_driver_id');
    var info = document.getElementById('detailBroadcastInfo');

    function syncMode() {
        var isManual = manualRadio && manualRadio.checked;
        if (manualWrap) manualWrap.classList.toggle('d-none', !isManual);
        if (info) info.classList.toggle('d-none', isManual);
        if (manualSelect) manualSelect.required = isManual;
    }

    [manualRadio, broadcastRadio].forEach(function (el) {
        if (el) el.addEventListener('change', syncMode);
    });
    syncMode();
});
</script>
@endsection
