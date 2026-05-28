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
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                                <tr>
                                    <td>#{{ $txn->id }}</td>
                                    <td>{{ optional($txn->driver)->name ?? 'Driver #' . $txn->driver_id }}</td>
                                    <td>{{ ucfirst((string) $txn->type) }}</td>
                                    <td class="fw-semibold">₹{{ number_format((float) $txn->amount, 2) }}</td>
                                    <td>{{ $txn->reference ?? '—' }}</td>
                                    <td>{{ Str::limit($txn->description ?? '—', 40) }}</td>
                                    <td>{{ optional($txn->created_at)->format('d/m/Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">No wallet transactions found.</td></tr>
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
