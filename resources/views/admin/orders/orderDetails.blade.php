@extends('layouts.app')

@section('content')
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="mb-0">{{ $title ?? 'Order Details' }}</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.order-tracking', $order->order_id) }}" class="btn btn-outline-primary btn-sm">Tracking</a>
                        <a href="{{ route('admin.order-invoice-pdf', $order->order_id) }}" class="btn btn-outline-secondary btn-sm">Invoice PDF</a>
                        <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary btn-sm">Back to orders</a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-3">Order #{{ $order->order_number }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 220px;">Order number</th>
                                                <td>{{ $order->order_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>Order status</th>
                                                <td>{{ \App\Models\Orders::statusLabel($order->order_status) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Payment method</th>
                                                <td>{{ ucfirst($order->payment_method) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Payment status</th>
                                                <td>{{ ucfirst($order->payment_status) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Shipping address</th>
                                                <td>
                                                    @php
                                                $rawShippingAddress = $order->shipping_address ?? null;
                                                $shipAddr = is_string($rawShippingAddress) ? json_decode($rawShippingAddress, true) : $rawShippingAddress;
                                                $fallbackShippingAddress = is_string($rawShippingAddress) && trim($rawShippingAddress) !== ''
                                                    ? $rawShippingAddress
                                                    : 'N/A';
                                            @endphp
                                            @if(is_array($shipAddr))
                                                @if(!empty($shipAddr['contact_name'])){{ $shipAddr['contact_name'] }}<br>@endif
                                                @if(!empty($shipAddr['mobile'])){{ $shipAddr['mobile'] }}<br>@endif
                                                @if(!empty($shipAddr['address_line'])){{ $shipAddr['address_line'] }}@endif
                                                @if(!empty($shipAddr['landmark'])), {{ $shipAddr['landmark'] }}@endif
                                                @if(!empty($shipAddr['city'])), {{ $shipAddr['city'] }}@endif
                                                @if(!empty($shipAddr['state'])), {{ $shipAddr['state'] }}@endif
                                                @if(!empty($shipAddr['pincode'])) - {{ $shipAddr['pincode'] }}@endif
                                                @if(!empty($shipAddr['country']))<br>{{ $shipAddr['country'] }}@endif
                                                @if(!empty($shipAddr['address_type']))<br><span class="text-muted" style="font-size:12px;">{{ ucfirst($shipAddr['address_type']) }}</span>@endif
                                            @else
                                                {{ $fallbackShippingAddress }}
                                            @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Notes</th>
                                                <td>{{ $order->notes ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Created at</th>
                                                <td>{{ optional($order->created_at)->format('d M Y, h:i A') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Line items</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-modern mb-0">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Line total</th>
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
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No line items for this order.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3 border">
                            <div class="card-body">
                                <h6 class="mb-3">Update order status</h6>
                                {{-- <p class="small text-muted mb-2">Set fulfillment to Processing, Shipped, Delivered, or Cancelled. Legacy statuses stay until you pick a new value.</p> --}}
                                <p class="small text-muted mb-2">Set fulfillment to Ready to Dispatch, Out for Delivery, Delivered, or Cancelled. Legacy statuses stay until you pick a new value.</p>
                                <form method="POST" action="{{ route('admin.update-order-status', $order->order_id) }}" class="d-flex flex-column gap-2">
                                    @csrf
                                    @include('admin.orders.partials.order-status-quick-select', [
                                        'order' => $order,
                                        'selectClass' => 'form-select',
                                    ])
                                    <button type="submit" class="btn btn-theme btn-sm">Save status</button>
                                </form>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-3">Customer</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ optional(optional($order->customer)->user)->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ optional(optional($order->customer)->user)->email ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Customer ID:</strong> {{ $order->customer_id ?? 'N/A' }}</p>
                            </div>
                        </div>

                        @if(!empty($order->vendor_id))
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Vendor</h6>
                                    <p class="mb-1"><strong>Business:</strong> {{ optional($order->vendor)->business_name ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Owner:</strong> {{ optional($order->vendor)->owner_name ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ optional($order->vendor)->email ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Vendor ID:</strong> {{ $order->vendor_id }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3">Amount summary</h6>
                                <p class="mb-1"><strong>Subtotal:</strong> ₹{{ number_format((float) $order->subtotal, 2) }}</p>
                                <p class="mb-1"><strong>Tax:</strong> ₹{{ number_format((float) $order->tax_amount, 2) }}</p>
                                <p class="mb-1"><strong>Shipping:</strong> ₹{{ number_format((float) $order->shipping_amount, 2) }}</p>
                                <p class="mb-0"><strong>Total:</strong> ₹{{ number_format((float) $order->total_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
