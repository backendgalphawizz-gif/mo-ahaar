<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .header { display: table; width: 100%; margin-bottom: 18px; }
        .header .left, .header .right { display: table-cell; width: 50%; vertical-align: top; }
        .brand { margin-bottom: 10px; }
        .brand-logo { max-width: 130px; max-height: 60px; margin-bottom: 8px; }
        .brand-name { font-size: 16px; font-weight: bold; }
        .title { font-size: 22px; font-weight: bold; margin-bottom: 4px; }
        .muted { color: #6b7280; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; }
        th { background: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        .summary td { border: none; padding: 4px 0; }
        .total { font-weight: bold; font-size: 14px; border-top: 1px solid #d1d5db; padding-top: 8px; }
    </style>
</head>
<body>
    @php
        $store = $storeSetting ?? $globalStoreSetting ?? null;
        $siteName = trim((string) ($store->site_title ?? ''));
        $appName = trim((string) ($store->app_name ?? ''));
        $brandName = $siteName !== '' ? $siteName : ($appName !== '' ? $appName : config('app.name', 'Store'));
        $supportNumber = trim((string) ($store->support_number ?? ''));
        $supportEmail = trim((string) ($store->support_email ?? ''));
        $supportAddress = trim((string) ($store->address ?? ''));
        $logoName = trim((string) ($store->logo ?? ''));
        $logoPath = $logoName !== '' ? public_path('uploads/settings/' . $logoName) : '';

        $customerName = optional(optional($order->customer)->user)->name ?? 'N/A';
        $customerEmail = optional(optional($order->customer)->user)->email ?? 'N/A';
        $customerPhone = optional(optional($order->customer)->user)->mobile ?? 'N/A';
        $customerAddress = optional($order->customer)->customer_address ?? ($order->shipping_address ?? 'N/A');
    @endphp

    <div class="header">
        <div class="left">
            <div class="brand">
                @if($logoPath !== '' && file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="{{ $brandName }} logo" class="brand-logo">
                @endif
                <div class="brand-name">{{ $brandName }}</div>
                @if($supportNumber !== '')<div class="muted">Phone: {{ $supportNumber }}</div>@endif
                @if($supportEmail !== '')<div class="muted">Email: {{ $supportEmail }}</div>@endif
                @if($supportAddress !== '')<div class="muted">Address: {{ $supportAddress }}</div>@endif
            </div>
            <div class="title">Invoice</div>
            <div class="muted">Order Number: #{{ $order->order_number }}</div>
            <div class="muted">Date: {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</div>
        </div>
        <div class="right text-right">
            <div><strong>Payment:</strong> {{ ucfirst($order->payment_method) }}</div>
            <div><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</div>
            <div><strong>Order Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</div>
        </div>
    </div>

    <div class="card">
        <strong>Customer Details</strong>
        <div style="margin-top:6px;">
            <div>{{ $customerName }}</div>
            <div>{{ $customerEmail }}</div>
            <div>{{ $customerPhone }}</div>
            <div>{{ $customerAddress }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 42%;">Item</th>
                <th style="width: 14%;" class="text-right">Price</th>
                <th style="width: 14%;" class="text-right">Qty</th>
                <th style="width: 14%;" class="text-right">Tax</th>
                <th style="width: 16%;" class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product_name ?? ('Item #' . $item->item_id) }}</td>
                    <td class="text-right">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                    <td class="text-right">{{ (int)$item->quantity }}</td>
                    <td class="text-right">₹{{ number_format((float)$item->tax_amount, 2) }}</td>
                    <td class="text-right">₹{{ number_format((float)$item->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td>Order Item</td>
                    <td class="text-right">₹{{ number_format((float)$order->subtotal, 2) }}</td>
                    <td class="text-right">1</td>
                    <td class="text-right">₹{{ number_format((float)$order->tax_amount, 2) }}</td>
                    <td class="text-right">₹{{ number_format((float)$order->total_amount, 2) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 14px; width: 320px; margin-left: auto;">
        <table class="summary">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">₹{{ number_format((float)$order->subtotal, 2) }}</td>
            </tr>
            @if ($order->shipping_amount > 0)
                <tr>
                    <td>Shipping</td>
                    <td class="text-right">₹{{ number_format((float)$order->shipping_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>Tax</td>
                <td class="text-right">₹{{ number_format((float)$order->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="total">Total</td>
                <td class="text-right total">₹{{ number_format((float)$order->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>

