@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-car-line me-2"></i>{{ $title }}</h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-2"><h5>Link Vehicle FASTag</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.fastag.account.store') }}" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label-title">Vehicle Number</label>
                                <input type="text" name="vehicle_number" class="form-control @error('vehicle_number') is-invalid @enderror" value="{{ old('vehicle_number') }}" placeholder="MH12AB1234">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-title">Provider</label>
                                <select name="provider" class="form-select @error('provider') is-invalid @enderror">
                                    <option value="">Select Provider</option>
                                    @foreach(['Paytm FASTag','ICICI FASTag','HDFC FASTag','Airtel FASTag'] as $provider)
                                        <option value="{{ $provider }}">{{ $provider }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-title">Tag ID</label>
                                <input type="text" name="tag_id" class="form-control @error('tag_id') is-invalid @enderror" value="{{ old('tag_id') }}" placeholder="TAG123456789">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-title">Opening Balance</label>
                                <input type="number" step="0.01" min="0" name="current_balance" class="form-control" value="{{ old('current_balance', 0) }}">
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-theme">Link FASTag</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-2"><h5>Recharge FASTag Balance</h5></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.fastag.recharge.store') }}" class="row g-3">
                            @csrf
                            <div class="col-12">
                                <label class="form-label-title">Vehicle FASTag</label>
                                <select name="fastag_account_id" class="form-select @error('fastag_account_id') is-invalid @enderror">
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->fastag_account_id }}">{{ $account->vehicle_number }} - {{ $account->provider }} (Bal: ₹{{ number_format((float)$account->current_balance, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label-title">Recharge Amount</label>
                                <input type="number" step="0.01" min="50" name="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Minimum 50">
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-theme">Recharge FASTag</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header card-header-2"><h5>Linked FASTag Accounts</h5></div>
            <div class="card-body table-responsive">
                <table class="table all-package theme-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle</th>
                            <th>Provider</th>
                            <th>Tag ID</th>
                            <th>Current Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $account->vehicle_number }}</td>
                                <td>{{ $account->provider }}</td>
                                <td>{{ $account->tag_id }}</td>
                                <td>₹{{ number_format((float)$account->current_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No FASTag linked yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-2"><h5>FASTag Transaction History</h5></div>
            <div class="card-body table-responsive">
                <table class="table all-package theme-table" id="table_id">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle</th>
                            <th>Provider</th>
                            <th>Tag ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Transaction Ref</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $tx->vehicle_number }}</td>
                                <td>{{ $tx->provider }}</td>
                                <td>{{ $tx->tag_id }}</td>
                                <td>₹{{ number_format((float)$tx->amount, 2) }}</td>
                                <td><span class="badge badge-light-success text-capitalize">{{ $tx->status }}</span></td>
                                <td>{{ $tx->transaction_ref }}</td>
                                <td>{{ optional($tx->recharged_at)->format('d-m-Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No FASTag recharge history.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

