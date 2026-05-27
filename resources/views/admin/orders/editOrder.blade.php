@extends('layouts.app')

@section('content')
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <div class="page-body-wrapper">
        <div class="page-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-sm-8 m-auto">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-header-2">
                                            <h5>{{ $title ?? 'Edit Order' }}</h5>
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

                                        <form action="{{ route('admin.update-order', $order->order_id) }}" method="POST" id="orderEditForm">
                                            @csrf
                                            <div class="theme-form theme-form-2 mega-form">
                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Customer</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-select" id="customer_id" name="customer_id">
                                                            <option value="">Select Customer</option>
                                                            @foreach($customers as $customer)
                                                                <option value="{{ $customer->customer_id }}" {{ old('customer_id', $order->customer_id) == $customer->customer_id ? 'selected' : '' }}>
                                                                    {{ $customer->name }} ({{ $customer->email }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <p class="errors text-danger mb-0" id="err_customer_id">@error('customer_id') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <!-- Vendor field removed: No vendor logic in system -->

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Order Number</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" id="order_number" type="text" value="{{ $order->order_number }}" readonly>
                                                        <small class="text-muted">Auto generated vendor-wise. It will refresh only if vendor is changed.</small>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Payment Method</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-select" id="payment_method" name="payment_method">
                                                            <option value="">Select Payment Method</option>
                                                            @forelse(($activePaymentMethods ?? collect()) as $method)
                                                                <option value="{{ $method->gateway }}" {{ old('payment_method', $order->payment_method) == $method->gateway ? 'selected' : '' }}>
                                                                    {{ $method->display_name }}
                                                                </option>
                                                            @empty
                                                                <option value="" disabled>No active payment methods found</option>
                                                            @endforelse
                                                        </select>
                                                        <p class="errors text-danger mb-0" id="err_payment_method">@error('payment_method') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Payment Status</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-select" id="payment_status" name="payment_status">
                                                            <option value="pending" {{ old('payment_status', $order->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="paid" {{ old('payment_status', $order->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                                            <option value="failed" {{ old('payment_status', $order->payment_status) == 'failed' ? 'selected' : '' }}>Failed</option>
                                                            <option value="refunded" {{ old('payment_status', $order->payment_status) == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                        </select>
                                                        <p class="errors text-danger mb-0" id="err_payment_status">@error('payment_status') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Order Status</label>
                                                    <div class="col-sm-9">
                                                        @php
                                                            $os = (string) old('order_status', $order->order_status);
                                                            $formStatuses = [
                                                                'pending' => 'Pending',
                                                                'payment_pending' => 'Payment pending',
                                                                'confirmed' => 'Confirmed',
                                                                'accepted' => 'Accepted',
                                                                'processing' => 'Processing',
                                                                'shipped' => 'Shipped',
                                                                'out_for_delivery' => 'Out for delivery (legacy)',
                                                                'delivered' => 'Delivered',
                                                                'cancelled' => 'Cancelled',
                                                                'rejected' => 'Rejected',
                                                            ];
                                                        @endphp
                                                        <select class="form-select" id="order_status" name="order_status">
                                                            @if(!array_key_exists($os, $formStatuses))
                                                                <option value="{{ $os }}" selected>{{ \App\Models\Orders::statusLabel($os) }} (current)</option>
                                                            @endif
                                                            @foreach($formStatuses as $val => $label)
                                                                <option value="{{ $val }}" @selected($os === $val)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                        <p class="errors text-danger mb-0" id="err_order_status">@error('order_status') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Subtotal</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control amount-input" id="subtotal" type="number" step="0.01" min="0" name="subtotal" value="{{ old('subtotal', $order->subtotal) }}">
                                                        <p class="errors text-danger mb-0" id="err_subtotal">@error('subtotal') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Tax Amount</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control amount-input" id="tax_amount" type="number" step="0.01" min="0" name="tax_amount" value="{{ old('tax_amount', $order->tax_amount) }}">
                                                        <p class="errors text-danger mb-0" id="err_tax_amount">@error('tax_amount') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Shipping Amount</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control amount-input" id="shipping_amount" type="number" step="0.01" min="0" name="shipping_amount" value="{{ old('shipping_amount', $order->shipping_amount) }}">
                                                        <p class="errors text-danger mb-0" id="err_shipping_amount">@error('shipping_amount') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Total Amount</label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" id="total_amount" type="number" step="0.01" min="0" name="total_amount" value="{{ old('total_amount', $order->total_amount) }}" readonly>
                                                        <p class="errors text-danger mb-0" id="err_total_amount">@error('total_amount') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Shipping Address</label>
                                                    <div class="col-sm-9">
                                                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" placeholder="Shipping Address">{{ old('shipping_address', $order->shipping_address) }}</textarea>
                                                        <p class="errors text-danger mb-0" id="err_shipping_address">@error('shipping_address') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center">
                                                    <label class="form-label-title col-sm-3 mb-0">Notes</label>
                                                    <div class="col-sm-9">
                                                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Order Notes">{{ old('notes', $order->notes) }}</textarea>
                                                        <p class="errors text-danger mb-0" id="err_notes">@error('notes') {{ $message }} @enderror</p>
                                                    </div>
                                                </div>

                                                <div class="mb-4 row align-items-center mt-5">
                                                    <label class="form-label-title col-sm-3 mb-0"></label>
                                                    <div class="col-sm-9 d-flex gap-2">
                                                        <button type="button" class="btn btn-solid" onclick="updateOrder()">Update Order</button>
                                                        <button type="button" onclick="history.back()" class="btn btn-outline-secondary">Back</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
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
<script>
    function setError(field, message) {
        var el = document.getElementById('err_' + field);
        if (el) {
            el.textContent = message;
        }
    }

    function clearErrors() {
        [
            'customer_id', 'vendor_id', 'payment_method', 'payment_status',
            'order_status', 'subtotal', 'tax_amount', 'shipping_amount',
            'total_amount', 'shipping_address', 'notes'
        ].forEach(function(field) {
            setError(field, '');
        });
    }

    function calculateTotal() {
        var subtotal = parseFloat(document.getElementById('subtotal').value || 0);
        var tax = parseFloat(document.getElementById('tax_amount').value || 0);
        var shipping = parseFloat(document.getElementById('shipping_amount').value || 0);

        var total = subtotal + tax + shipping;
        document.getElementById('total_amount').value = total.toFixed(2);
    }

    function updateOrder() {
        clearErrors();

        var isValid = true;
        var customerId = document.getElementById('customer_id').value.trim();
        var vendorId = document.getElementById('vendor_id').value.trim();
        var paymentMethod = document.getElementById('payment_method').value.trim();
        var paymentStatus = document.getElementById('payment_status').value.trim();
        var orderStatus = document.getElementById('order_status').value.trim();
        var totalAmount = parseFloat(document.getElementById('total_amount').value || 0);

        if (customerId === '') {
            setError('customer_id', 'Please select a customer.');
            isValid = false;
        }

        if (vendorId === '') {
            setError('vendor_id', 'Please select a vendor.');
            isValid = false;
        }

        if (paymentMethod === '') {
            setError('payment_method', 'Payment method is required.');
            isValid = false;
        }

        if (paymentStatus === '') {
            setError('payment_status', 'Payment status is required.');
            isValid = false;
        }

        if (orderStatus === '') {
            setError('order_status', 'Order status is required.');
            isValid = false;
        }

        if (isNaN(totalAmount) || totalAmount < 0) {
            setError('total_amount', 'Total amount must be 0 or greater.');
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        document.getElementById('orderEditForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.amount-input').forEach(function(el) {
            el.addEventListener('input', calculateTotal);
        });

        calculateTotal();
    });
</script>
@endsection
