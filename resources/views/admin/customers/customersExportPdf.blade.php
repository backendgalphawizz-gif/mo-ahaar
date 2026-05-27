<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customers Export</title>
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
        .badge-active { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-inactive { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
        .badge-approved { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-pending { background: #fff7ed; color: #9a3412; border: 1px solid #fdba74; }
        .badge-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
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

    <h2>{{ $brandName }} — Customers Export</h2>
    <div class="meta">Generated: {{ now()->format('d M Y, h:i A') }}</div>

    @if($search)
        <div class="filters">
            <strong>Applied Filters:</strong>
            &nbsp; Search: <em>{{ $search }}</em>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>User Type</th>
                <th>Account Status</th>
                @if($hasApproval)
                <th>Approval</th>
                @endif
                <th>Registered On</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $index => $customer)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $customer->name ?? 'N/A' }}</td>
                    <td>{{ $customer->email ?? '' }}</td>
                    <td>{{ $customer->mobile ?? '-' }}</td>
                    <td>{{ $customer->user_type ?? '-' }}</td>
                    <td>
                        @if((int)($customer->status ?? 0) === 1)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    @if($hasApproval)
                    <td>
                        @php $ap = strtolower((string)($customer->approval_status ?? 'approved')); @endphp
                        <span class="badge badge-{{ $ap }}">{{ ucfirst($ap) }}</span>
                    </td>
                    @endif
                    <td>
                        @if(!empty($customer->registration_date))
                            {{ \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $hasApproval ? 8 : 7 }}" style="text-align:center; padding:16px; color:#6b7280;">No customers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <footer>Total {{ $customers->count() }} customers &mdash; {{ $brandName }}</footer>
</body>
</html>
