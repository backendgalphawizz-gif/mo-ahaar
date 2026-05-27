<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Reports Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
        h2 { font-size: 18px; font-weight: bold; margin: 0 0 4px 0; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 10px; }
        .filters { background: #f3f4f6; padding: 6px 10px; border-radius: 4px; margin-bottom: 12px; font-size: 10px; color: #374151; }
        .summary { display: table; width: 100%; margin-bottom: 14px; border-collapse: separate; border-spacing: 6px; }
        .summary-cell { display: table-cell; width: 25%; background: #f3f4f6; border-radius: 6px; padding: 8px 10px; text-align: center; }
        .summary-cell .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
        .summary-cell .value { font-size: 18px; font-weight: 700; margin-top: 2px; }
        .summary-primary .value { color: #1d4ed8; }
        .summary-success .value { color: #065f46; }
        .summary-warning .value { color: #9a3412; }
        .summary-danger .value { color: #991b1b; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1f2937; color: #fff; padding: 7px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 9px; font-weight: bold; }
        .badge-pending, .badge-processing, .badge-out_for_delivery { background: #fff7ed; color: #9a3412; border: 1px solid #fdba74; }
        .badge-accepted, .badge-delivered, .badge-completed, .badge-success, .badge-confirmed { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-shipped { background: #eff6ff; color: #1e40af; border: 1px solid #93c5fd; }
        .badge-rejected, .badge-cancelled, .badge-failed { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
        .text-right { text-align: right; }
        footer { margin-top: 18px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    @php
        $store = $storeSetting ?? null;
        $brandName = '';
        if ($store) {
            $brandName = trim((string) ($store->site_title ?? $store->app_name ?? ''));
        }
        if ($brandName === '') {
            $brandName = config('app.name', 'Store');
        }
    @endphp

    <h2>{{ $brandName }} — Order Reports</h2>
    <div class="meta">Generated: {{ now()->format('d M Y, h:i A') }}</div>

    @if($startDate || $endDate || $status)
        <div class="filters">
            <strong>Applied Filters:</strong>
            @if($startDate) &nbsp; From: <em>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</em> @endif
            @if($endDate) &nbsp; To: <em>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</em> @endif
            @if($status) &nbsp; Status: <em>{{ ucfirst($status) }}</em> @endif
        </div>
    @endif

    <div class="summary">
        <div class="summary-cell summary-primary">
            <div class="label">Total Orders</div>
            <div class="value">{{ $summary['total'] }}</div>
        </div>
        <div class="summary-cell summary-success">
            <div class="label">Completed</div>
            <div class="value">{{ $summary['completed'] }}</div>
        </div>
        <div class="summary-cell summary-warning">
            <div class="label">Pending</div>
            <div class="value">{{ $summary['pending'] }}</div>
        </div>
        <div class="summary-cell summary-danger">
            <div class="label">Cancelled</div>
            <div class="value">{{ $summary['cancelled'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount (₹)</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
                @php $orderStatus = $order->order_status ?? ''; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>#{{ $order->order_number }}</td>
                    <td>{{ optional(optional($order->customer)->user)->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format((float)$order->total_amount, 2) }}</td>
                    <td>{{ ucfirst((string)$order->payment_status) }}</td>
                    <td>
                        <span class="badge badge-{{ $orderStatus }}">{{ ucfirst(str_replace('_', ' ', $orderStatus)) }}</span>
                    </td>
                    <td>{{ optional($order->created_at)->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:16px; color:#6b7280;">No order records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <footer>Total {{ $orders->count() }} orders &mdash; {{ $brandName }}</footer>
</body>
</html>
