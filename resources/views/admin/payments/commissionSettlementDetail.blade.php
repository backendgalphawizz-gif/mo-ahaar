@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Payment Request</h4>
                <small class="text-muted">Review and process withdrawal request PRQ-{{ $settlement->settlement_id }}</small>
            </div>
            <span class="badge badge-soft-success">Current Status: {{ ucfirst((string) $settlement->status) }}</span>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="ri-file-list-3-line me-1"></i>Request Overview</h6>
                        <div class="border rounded p-3 text-center mb-3">
                            <small class="text-muted d-block">AMOUNT REQUESTED</small>
                            <h3 class="mb-0">₹ {{ number_format((float) $settlement->payout_amount, 2) }}</h3>
                            <small class="text-muted">Requested on {{ optional($settlement->requested_at)->format('d/m/Y') ?: '-' }}</small>
                        </div>

                        <h6 class="mb-2">User Information</h6>
                        <div class="small text-muted border-bottom pb-2 mb-2">
                            Name <span class="float-end text-dark">{{ optional($settlement->vendor)->owner_name ?: optional($settlement->vendor)->business_name ?: 'N/A' }}</span>
                        </div>
                        <div class="small text-muted border-bottom pb-2 mb-2">
                            Role Type <span class="float-end text-primary">Vendor</span>
                        </div>

                        <h6 class="mb-2 mt-3">Payment Address</h6>
                        <div class="small text-muted">
                            Account Name <span class="float-end text-dark">{{ optional($settlement->vendor)->owner_name ?: 'N/A' }}</span><br>
                            Account Number <span class="float-end text-dark">{{ optional($settlement->vendor)->bank_account_number ?? 'N/A' }}</span><br>
                            IFSC Code <span class="float-end text-dark">{{ optional($settlement->vendor)->ifsc_code ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <h6 class="mb-1"><i class="ri-shield-check-line me-1"></i>Process Payment</h6>
                        {{-- <p class="text-muted small mb-3">Update the status and leave a note for the requested withdrawal.</p> --}}

                        <form method="POST" action="{{ route('admin.payments.settlements.update-status', $settlement->settlement_id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Payment Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach(['pending','processing','approved','rejected','paid'] as $status)
                                        <option value="{{ $status }}" {{ (string) old('status', $settlement->status) === $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Admin Remark</label>
                                <textarea name="admin_note" rows="4" class="form-control @error('admin_note') is-invalid @enderror" placeholder="e.g., Settled via NEFT / Details mismatch...">{{ old('admin_note', $settlement->admin_note) }}</textarea>
                                @error('admin_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">This remark will be visible to the vendor.</small>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.payments.settlements') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-danger"><i class="ri-save-line me-1"></i>Save Updates</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
