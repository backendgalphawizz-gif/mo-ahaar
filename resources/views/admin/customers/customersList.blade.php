@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'User Management',
            'subtitle' => 'Manage and view all registered users',
            'actionUrl' => route('admin.add-customer'),
            'actionLabel' => 'Add New User',
        ])

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card dashboard-card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.customers') }}" class="figma-toolbar">
                    <div class="toolbar-search">
                        <i class="ri-search-line"></i>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="{{ $search ?? '' }}">
                    </div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" style="max-width:150px;">
                    <select name="status" class="form-select" style="max-width:130px;">
                        <option value="all" {{ ($statusFilter ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($statusFilter ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Filter</button>
                    <span class="toolbar-spacer"></span>
                    <a href="{{ route('admin.customers.export-excel', request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="ri-download-line me-1"></i>Export All</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>User Info</th>
                                <th>Total Orders</th>
                                <th>Joined Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allCustomers as $customer)
                                @php
                                    $initials = collect(explode(' ', (string) $customer->name))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
                                    $avatarClass = ['avatar-blue', 'avatar-green', 'avatar-orange'][$loop->index % 3];
                                    $isActive = (int) $customer->status === 1;
                                    $userCode = 'USR-' . str_pad((string) $customer->customer_id, 4, '0', STR_PAD_LEFT);
                                @endphp
                                <tr>
                                    <td class="fw-semibold">#{{ $userCode }}</td>
                                    <td>
                                        <div class="cell-with-avatar">
                                            <span class="user-avatar {{ $avatarClass }}">{{ $initials ?: 'U' }}</span>
                                            <div>
                                                <div class="fw-semibold">{{ $customer->name }}</div>
                                                <small class="text-muted d-block">+91 {{ $customer->mobile ?: '—' }}</small>
                                                <small class="text-muted">{{ $customer->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold">{{ number_format((int) ($orderCounts[$customer->customer_id] ?? 0)) }}</td>
                                    <td>{{ $customer->registration_date ? \Carbon\Carbon::parse($customer->registration_date)->format('d/m/Y') : '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.customers.toggle-status', $customer->customer_id) }}">
                                            @csrf
                                            <label class="figma-switch">
                                                <input type="checkbox" onchange="this.form.submit()" {{ $isActive ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                        </form>
                                        <span class="status-label {{ $isActive ? 'on' : 'off' }}">{{ $isActive ? 'ACTIVE' : 'INACTIVE' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 table-action-icons">
                                            <a href="{{ route('admin.view-customer', urlencode(Crypt::encrypt($customer->customer_id))) }}" title="View"><i class="ri-eye-line"></i></a>
                                            <a href="{{ route('admin.edit-customer', urlencode(Crypt::encrypt($customer->customer_id))) }}" title="Edit"><i class="ri-pencil-line"></i></a>
                                            <a href="javascript:void(0)" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteCustomerModal"
                                               data-customer-id="{{ urlencode(Crypt::encrypt($customer->customer_id)) }}"
                                               data-customer-name="{{ $customer->name }}"><i class="ri-delete-bin-line text-danger"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-5">No users found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($allCustomers->hasPages())
                    <div class="figma-pagination">
                        <span>Showing {{ $allCustomers->firstItem() }} to {{ $allCustomers->lastItem() }} of {{ number_format($allCustomers->total()) }} entries</span>
                        <div>{{ $allCustomers->withQueryString()->links('pagination::bootstrap-5') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-1">Are you sure you want to delete <strong id="deleteCustomerName">this user</strong>?</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteCustomerConfirm" class="btn btn-danger">Yes, Delete</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('deleteCustomerModal')?.addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    if (!btn) return;
    document.getElementById('deleteCustomerName').textContent = btn.getAttribute('data-customer-name') || 'this user';
    document.getElementById('deleteCustomerConfirm').href = '{{ url('admin/delete-customer') }}/' + encodeURIComponent(btn.getAttribute('data-customer-id'));
});
</script>
@endsection
