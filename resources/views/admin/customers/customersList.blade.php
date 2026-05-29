@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'User Management',
            'subtitle' => 'Manage and view all registered users',
            'actionModalId' => 'userFormModal',
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
                                        <label class="figma-switch">
                                            <input type="checkbox"
                                                class="ajax-status-toggle"
                                                data-toggle-url="{{ route('admin.customers.toggle-status', $customer->customer_id) }}"
                                                data-status-label="#customer-status-label-{{ $customer->customer_id }}"
                                                {{ $isActive ? 'checked' : '' }}
                                                aria-label="Toggle customer status">
                                            <span class="slider"></span>
                                        </label>
                                        <span id="customer-status-label-{{ $customer->customer_id }}" class="status-label {{ $isActive ? 'on' : 'off' }}">{{ $isActive ? 'ACTIVE' : 'INACTIVE' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 table-action-icons">
                                            <a href="{{ route('admin.view-customer', urlencode(Crypt::encrypt($customer->customer_id))) }}" title="View"><i class="ri-eye-line"></i></a>
                                            <button type="button" class="border-0 bg-transparent p-0 btn-edit-user" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#userFormModal"
                                                    data-customer-id="{{ $customer->customer_id }}"
                                                    data-customer-name="{{ $customer->name }}"
                                                    data-customer-email="{{ $customer->email }}"
                                                    data-customer-phone="{{ $customer->mobile }}"
                                                    data-customer-address="{{ $customer->customer_address ?? '' }}">
                                                <i class="ri-pencil-line"></i>
                                            </button>
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

@include('admin.customers.partials.user-form-modal')

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
                <form method="POST" id="deleteCustomerForm" action="#" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var modalEl = document.getElementById('userFormModal');
    var formEl = document.getElementById('userFormModalForm');
    var titleEl = document.getElementById('userFormModalLabel');
    var submitBtn = document.getElementById('userFormSubmitBtn');
    var customerIdInput = document.getElementById('userFormCustomerId');
    var storeUrl = @json(route('admin.store-customer'));
    var updateUrl = @json(route('admin.update-customer'));

    function setAddMode() {
        if (!formEl) return;
        formEl.action = storeUrl;
        customerIdInput.value = '';
        customerIdInput.disabled = true;
        titleEl.textContent = 'Add New User';
        submitBtn.textContent = 'Add User';
    }

    function setEditMode(data) {
        if (!formEl) return;
        formEl.action = updateUrl;
        customerIdInput.disabled = false;
        customerIdInput.value = data.id || '';
        document.getElementById('userFormName').value = data.name || '';
        document.getElementById('userFormEmail').value = data.email || '';
        document.getElementById('userFormPhone').value = data.phone || '';
        document.getElementById('userFormAddress').value = data.address || '';
        titleEl.textContent = 'Edit User';
        submitBtn.textContent = 'Update User';
    }

    document.querySelectorAll('[data-user-modal-mode="add"]').forEach(function (btn) {
        btn.addEventListener('click', setAddMode);
    });

    document.querySelectorAll('.btn-edit-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setEditMode({
                id: btn.getAttribute('data-customer-id'),
                name: btn.getAttribute('data-customer-name'),
                email: btn.getAttribute('data-customer-email'),
                phone: btn.getAttribute('data-customer-phone'),
                address: btn.getAttribute('data-customer-address') || '',
            });
        });
    });

    formEl?.addEventListener('submit', function () {
        customerIdInput.disabled = false;
    });

    modalEl?.addEventListener('hidden.bs.modal', function () {
        @if(!session('open_user_modal') && !$errors->any())
        formEl.reset();
        setAddMode();
        @endif
    });

    @if(session('open_user_modal') === 'add' || (session('open_user_modal') === 'edit' && $errors->any()))
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('open_user_modal') === 'edit')
                setEditMode({
                    id: @json(old('customer_id')),
                    name: @json(old('customer_name')),
                    email: @json(old('customer_email')),
                    phone: @json(old('customer_phone')),
                    address: @json(old('customer_address')),
                });
            @else
                setAddMode();
            @endif
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    @endif

    @if(!empty($modalEditCustomer))
        document.addEventListener('DOMContentLoaded', function () {
            setEditMode({
                id: @json($modalEditCustomer->customer_id),
                name: @json($modalEditCustomer->name),
                email: @json($modalEditCustomer->email),
                phone: @json($modalEditCustomer->mobile),
                address: @json($modalEditCustomer->customer_address ?? ''),
            });
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    @elseif(request('open') === 'add')
        document.addEventListener('DOMContentLoaded', function () {
            setAddMode();
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    @endif
})();

document.getElementById('deleteCustomerModal')?.addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    if (!btn) return;
    document.getElementById('deleteCustomerName').textContent = btn.getAttribute('data-customer-name') || 'this user';
    var deleteForm = document.getElementById('deleteCustomerForm');
    if (deleteForm) {
        deleteForm.action = '{{ url('admin/delete-customer') }}/' + encodeURIComponent(btn.getAttribute('data-customer-id'));
    }
});
</script>
@endsection
