@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('vendor.orders') }}" class="btn btn-sm btn-outline-secondary"><i class="ri-arrow-left-line"></i></a>
            <h5 class="mb-0">Order Details - {{ $order->order_number }}</h5>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card dashboard-card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-3">Items Ordered</h6>
                            <span class="badge badge-soft-info">{{ \App\Models\Orders::statusLabel($order->order_status) }}</span>
                        </div>
                        @foreach($order->orderItems as $item)
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span>{{ (int)$item->quantity }}x {{ $item->product_name }}</span>
                                <strong>₹{{ number_format((float)$item->line_total, 2) }}</strong>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-between pt-3">
                            <strong>Total Amount</strong>
                            <strong>₹{{ number_format((float)$order->total_amount, 2) }}</strong>
                        </div>
                    </div>
                </div>

                @if(!empty($order->cooking_instructions))
                <div class="card dashboard-card mb-3">
                    <div class="card-body">
                        <h6 class="mb-2">Cooking Instructions</h6>
                        <p class="mb-0 text-muted">{{ $order->cooking_instructions }}</p>
                    </div>
                </div>
                @endif

                @if(!empty($order->notes))
                <div class="card dashboard-card mb-3">
                    <div class="card-body">
                        <h6 class="mb-2">Order Notes</h6>
                        <p class="mb-0 text-muted">{{ $order->notes }}</p>
                    </div>
                </div>
                @endif

                <div class="card dashboard-card">
                    <div class="card-body">
                        <h6 class="mb-3">Update Order Status</h6>
                        <form method="POST" action="{{ route('vendor.update-order-status', $order->order_id) }}">
                            @csrf
                            <select name="order_status" class="form-select mb-2" onchange="this.form.submit()">
                                @foreach(\App\Models\Orders::persistableOrderStatuses() as $status)
                                    <option value="{{ $status }}" {{ $order->order_status === $status ? 'selected' : '' }}>{{ \App\Models\Orders::statusLabel($status) }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                @php
                    $customer = $order->customer?->user;
                    $driver = $order->deliveryAssignment?->driver;
                    $ship = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address;
                @endphp
                <div class="card dashboard-card mb-3"><div class="card-body">
                    <h6>Customer Details</h6>
                    <p class="mb-1">{{ $customer->name ?? '-' }}</p>
                    <small>{{ $customer->mobile ?? '-' }}</small><br>
                    <small>{{ is_array($ship) ? ($ship['formatted_address'] ?? '-') : ($order->shipping_address ?? '-') }}</small>
                </div></div>
                <div class="card dashboard-card mb-3"><div class="card-body">
                    <h6>Order Info</h6>
                    <small>Date: {{ optional($order->created_at)->format('Y-m-d H:i') }}</small><br>
                    <small>Payment: {{ strtoupper((string)$order->payment_method) }}</small>
                </div></div>
                <div class="card dashboard-card"><div class="card-body">
                    <h6>Driver Details</h6>
                    <p class="mb-1">{{ $driver->name ?? 'Not Assigned' }}</p>
                    <small>{{ $driver->mobile ?? '-' }}</small>
                </div></div>
            </div>
        </div>
    </div>
</div>
@endsection

