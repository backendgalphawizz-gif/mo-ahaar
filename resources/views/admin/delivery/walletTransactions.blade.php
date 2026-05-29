@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'Wallet Transactions',
            'subtitle' => 'Delivery partner wallet credits and debits',
        ])

        <div class="card dashboard-card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.delivery.wallet-transactions') }}" class="figma-toolbar mb-3">
                    <input type="text" name="search" class="form-control form-control-sm" style="max-width:240px;" placeholder="Search reference..." value="{{ $search }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Driver</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                                <tr>
                                    <td>#{{ $txn->transaction_id }}</td>
                                    <td>{{ optional($txn->driver)->name ?? 'Driver #' . $txn->driver_id }}</td>
                                    <td>
                                        <span class="{{ $txn->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                            {{ ucfirst((string) $txn->type) }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">₹{{ number_format((float) $txn->amount, 2) }}</td>
                                    <td class="text-nowrap">{{ $txn->transaction_ref ?? '—' }}</td>
                                    <td>
                                        <div class="fw-medium">{{ $txn->title ?? '—' }}</div>
                                        @if(!empty($txn->subtitle))
                                            <small class="text-muted">{{ Str::limit($txn->subtitle, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst((string) ($txn->status ?? '—')) }}</td>
                                    <td>{{ optional($txn->created_at)->format('d/m/Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">No wallet transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($transactions, 'hasPages') && $transactions->hasPages())
                    <div class="mt-3">{{ $transactions->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
