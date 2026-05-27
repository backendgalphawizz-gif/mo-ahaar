<!-- This file has been disabled as Payment % earning feature is removed. -->

                    <div class="col-md-3">
                        <label class="form-label">Period Start</label>
                        <input type="date" name="period_start" class="form-control @error('period_start') is-invalid @enderror" value="{{ old('period_start') }}">
                        @error('period_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Period End</label>
                        <input type="date" name="period_end" class="form-control @error('period_end') is-invalid @enderror" value="{{ old('period_end') }}">
                        @error('period_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2 d-grid align-self-end">
                        <button type="submit" class="btn btn-theme">Request</button>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Request Note</label>
                        <textarea name="request_note" rows="2" class="form-control @error('request_note') is-invalid @enderror" placeholder="Optional note for settlement request">{{ old('request_note') }}</textarea>
                        @error('request_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Payout Status Tracking</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-start">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Vendor</th>
                                <th>Settlement Period</th>
                                <th>Gross Sales</th>
                                <th>Commission Amount</th>
                                <th>Payout Amount</th>
                                <th>Status</th>
                                <th>Requested Date</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settlements as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ optional($item->vendor)->business_name ?? optional($item->vendor)->owner_name ?? 'N/A' }}</td>
                                    <td>{{ optional($item->period_start)->format('d M Y') }} - {{ optional($item->period_end)->format('d M Y') }}</td>
                                    <td>₹{{ number_format((float)$item->gross_sales, 2) }}</td>
                                    <td>₹{{ number_format((float)$item->commission_amount, 2) }} ({{ number_format((float)$item->commission_rate, 2) }}%)</td>
                                    <td>₹{{ number_format((float)$item->payout_amount, 2) }}</td>
                                    <td>{{ ucfirst($item->status) }}</td>
                                    <td>{{ optional($item->requested_at)->format('d M Y, h:i A') ?: '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.payments.settlements.status', $item->settlement_id) }}" class="d-flex gap-1">
                                            @csrf
                                            <select name="status" class="form-select form-select-sm">
                                                @foreach(['pending','processing','approved','rejected','paid'] as $status)
                                                    <option value="{{ $status }}" {{ $item->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">No settlement requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

