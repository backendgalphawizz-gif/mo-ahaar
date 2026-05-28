@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <h5 class="mb-3">Payments & Wallet</h5>
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card dashboard-card mb-3">
                    <div class="card-body text-white" style="background:#8a3f00;border-radius:8px;">
                        <small>Available Balance</small>
                        <h2 class="mb-0">₹{{ number_format((float)$vendor->wallet_balance, 2) }}</h2>
                        <small>Updated just now</small>
                    </div>
                </div>
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h6>Withdraw Funds</h6>
                        <form method="POST" action="{{ route('vendor.payments.withdraw') }}">
                            @csrf
                            <label class="form-label">Amount to Withdraw</label>
                            <input type="number" name="amount" class="form-control mb-2" min="1" step="0.01" placeholder="0.00">
                            <button class="btn btn-sm btn-brown w-100">Send Request to Admin</button>
                        </form>
                        <small class="text-muted d-block mt-2">Minimum withdraw amount is ₹50.00</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Transaction History</h6>
                            <a href="javascript:void(0)" class="small">Download Statement</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Date & Details</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($transactions as $txn)
                                    <tr>
                                        <td>{{ $txn['id'] }}</td>
                                        <td>{{ $txn['title'] }}<br><small class="text-muted">{{ $txn['date'] }}</small></td>
                                        <td>
                                            <span class="text-success">₹{{ number_format((float)$txn['amount'], 2) }}</span><br>
                                            <small class="text-muted text-uppercase">{{ $txn['type'] }}</small>
                                        </td>
                                        <td><span class="badge {{ $txn['status'] === 'completed' ? 'badge-soft-success' : 'badge-soft-warning' }}">{{ ucfirst($txn['status']) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No transactions found.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.btn-brown{background:#8a3f00;border-color:#8a3f00;color:#fff}
.btn-brown:hover{background:#733400;border-color:#733400;color:#fff}
</style>
@endsection

