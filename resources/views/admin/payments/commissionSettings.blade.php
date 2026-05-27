<!-- This file has been disabled as Payment % earning feature is removed. -->
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Business Name</th>
                                                <th>Owner Name</th>
                                                <th>Email ID</th>
                                                <th>Mobile No.</th>
                                                <th>Status</th>
                                                <th>Commission %</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($vendors as $vendor)
                                                @php
                                                    $isActive = in_array(strtolower((string) ($vendor->status ?? '0')), ['1', 'active'], true);
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $vendor->business_name ?: '-' }}</td>
                                                    <td>{{ $vendor->owner_name ?: '-' }}</td>
                                                    <td>{{ $vendor->email ?: '-' }}</td>
                                                    <td>{{ $vendor->mobile ?: '-' }}</td>
                                                    <td>
                                                        <span class="badge {{ $isActive ? 'bg-success' : 'bg-danger' }}">
                                                            {{ $isActive ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>
                                                    <td style="min-width: 180px;">
                                                        <form method="POST" action="{{ route('admin.payments.commission-settings.update', $vendor->vendor_id) }}" class="d-flex gap-2 align-items-center">
                                                            @csrf
                                                            <input
                                                                type="number"
                                                                name="commission_percent"
                                                                value="{{ number_format((float) ($vendor->commission_percent ?? 0), 2, '.', '') }}"
                                                                min="0"
                                                                max="100"
                                                                step="0.01"
                                                                class="form-control form-control-sm"
                                                                style="width: 100px;"
                                                                required>
                                                            <span>%</span>
                                                    </td>
                                                    <td>
                                                            <button type="submit" class="btn btn-theme btn-sm">Update</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-4">No vendors found.</td>
                                                </tr>
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
    </div>
</div>
@endsection
