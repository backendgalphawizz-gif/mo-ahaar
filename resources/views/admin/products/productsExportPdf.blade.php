<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Products Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; }
        h2 { font-size: 18px; font-weight: bold; margin: 0 0 4px 0; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 14px; }
        .filters { background: #f3f4f6; padding: 6px 10px; border-radius: 4px; margin-bottom: 14px; font-size: 10px; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1f2937; color: #fff; padding: 7px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 9px; font-weight: bold; }
        .badge-approved { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-pending { background: #fff7ed; color: #9a3412; border: 1px solid #fdba74; }
        .badge-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
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
        $statusLabels = [1 => 'Approved', 2 => 'Pending', 3 => 'Rejected'];
        $statusBadges = [1 => 'badge-approved', 2 => 'badge-pending', 3 => 'badge-rejected'];
    @endphp

    <h2>{{ $brandName }} — Products Export</h2>
    <div class="meta">Generated: {{ now()->format('d M Y, h:i A') }}</div>

    @if($search)
        <div class="filters">
            <strong>Applied Filters:</strong>
            @if($search) &nbsp; Search: <em>{{ $search }}</em> @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>SKU</th>
                <th class="text-right">MRP (₹)</th>
                <th class="text-right">Price (₹)</th>
                <th>Approval</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
                @php
                    $statusInt = (int)($product->status ?? 0);
                    $statusLabel = $statusLabels[$statusInt] ?? 'Unknown';
                    $statusBadge = $statusBadges[$statusInt] ?? 'badge-pending';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->product_name ?? 'N/A' }}</td>
                    <td>
                        {{ $product->category_name ?? '-' }}
                        @if(!empty($product->sub_cat_name))
                            / {{ $product->sub_cat_name }}
                        @endif
                    </td>
                    <td>{{ $product->sku ?? '-' }}</td>
                    <td class="text-right">{{ number_format((float)($product->mrp ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float)($product->price ?? 0), 2) }}</td>
                    <td><span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:16px; color:#6b7280;">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <footer>Total {{ $products->count() }} products &mdash; {{ $brandName }}</footer>
</body>
</html>
