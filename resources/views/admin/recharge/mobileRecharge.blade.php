@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-smartphone-line me-2"></i>{{ $title }}</h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header card-header-2"><h5>Prepaid Mobile Recharge</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.mobile-recharge.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label-title">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" value="{{ old('mobile_number') }}" placeholder="10-digit number">
                        @error('mobile_number')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-title">Operator</label>
                        <select name="operator" id="operatorSelect" class="form-select @error('operator') is-invalid @enderror">
                            <option value="">Select Operator</option>
                            @foreach($operators as $operator)
                                <option value="{{ $operator }}" {{ old('operator') === $operator ? 'selected' : '' }}>{{ $operator }}</option>
                            @endforeach
                        </select>
                        @error('operator')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-title">Recharge Plan</label>
                        <select name="plan_id" id="planSelect" class="form-select @error('plan_id') is-invalid @enderror">
                            <option value="">Select Plan</option>
                        </select>
                        @error('plan_id')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-title">Amount</label>
                        <input type="number" step="0.01" min="10" name="amount" id="amountInput" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" placeholder="Amount">
                        @error('amount')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-theme">Recharge Now</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-2"><h5>Recharge Transactions</h5></div>
            <div class="card-body table-responsive">
                <table class="table all-package theme-table" id="table_id">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mobile</th>
                            <th>Operator</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Transaction Ref</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recharges as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->mobile_number }}</td>
                                <td>{{ $item->operator }}</td>
                                <td>{{ $item->plan_name ?: '-' }}</td>
                                <td>₹{{ number_format((float)$item->amount, 2) }}</td>
                                <td><span class="badge badge-light-success text-capitalize">{{ $item->status }}</span></td>
                                <td>{{ $item->transaction_ref }}</td>
                                <td>{{ optional($item->recharged_at)->format('d-m-Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var operatorSelect = document.getElementById('operatorSelect');
    var planSelect = document.getElementById('planSelect');
    var amountInput = document.getElementById('amountInput');

    function loadPlans(operator) {
        planSelect.innerHTML = '<option value="">Select Plan</option>';
        if (!operator) return;

        fetch('/admin/mobile-recharge/plans/' + encodeURIComponent(operator))
            .then(function (res) { return res.json(); })
            .then(function (plans) {
                plans.forEach(function (plan) {
                    var option = document.createElement('option');
                    option.value = plan.plan_id;
                    option.setAttribute('data-amount', plan.amount);
                    option.textContent = plan.plan_name + ' - ₹' + plan.amount + (plan.validity_days ? ' (' + plan.validity_days + ' days)' : '');
                    planSelect.appendChild(option);
                });
            });
    }

    operatorSelect.addEventListener('change', function () {
        loadPlans(this.value);
    });

    planSelect.addEventListener('change', function () {
        var selected = this.options[this.selectedIndex];
        var amount = selected ? selected.getAttribute('data-amount') : '';
        if (amount) amountInput.value = amount;
    });

    if (operatorSelect.value) loadPlans(operatorSelect.value);
});
</script>
@endsection

