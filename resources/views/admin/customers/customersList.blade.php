@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="title-header option-title d-flex align-items-center mb-4">
                        <h5><i class="ri-group-line me-2"></i>User Management</h5>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-3">
                            <div class="card border-0 customer-stat customer-stat-teal h-100">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div class="stat-content">
                                        <small class="stat-label">Total registered</small>
                                        <h3 class="stat-value">{{ $totalCustomers }}</h3>
                                    </div>
                                    <span class="stat-icon-box">
                                        <i class="ri-group-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        @if(!empty($hasApproval))
                            <div class="col-12 col-md-3">
                                <div class="card border-0 customer-stat customer-stat-amber h-100">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div class="stat-content">
                                            <small class="stat-label">Pending approval</small>
                                            <h3 class="stat-value">{{ $pendingCount }}</h3>
                                        </div>
                                        <span class="stat-icon-box">
                                            <i class="ri-time-line"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-12 col-md-3">
                            <div class="card border-0 customer-stat customer-stat-mint h-100">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div class="stat-content">
                                        <small class="stat-label">Active accounts</small>
                                        <h3 class="stat-value">{{ $activeCount }}</h3>
                                    </div>
                                    <span class="stat-icon-box">
                                        <i class="ri-user-follow-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-6 col-md-3">
                            <div class="card border-0 customer-stat customer-stat-slate h-100">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div class="stat-content">
                                        <small class="stat-label">On hold</small>
                                        <h3 class="stat-value">{{ $onHoldCount }}</h3>
                                    </div>
                                    <span class="stat-icon-box">
                                        <i class="ri-user-unfollow-line"></i>
                                    </span>
                                </div>
                            </div>
                        </div> --}}
                        @if(!empty($hasApproval))
                            <div class="col-12 col-md-3">
                                <div class="card border-0 customer-stat customer-stat-rose h-100">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div class="stat-content">
                                            <small class="stat-label">Rejected</small>
                                            <h3 class="stat-value">{{ $rejectedCount }}</h3>
                                        </div>
                                        <span class="stat-icon-box">
                                            <i class="ri-close-circle-line"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('admin.customers') }}" class="customer-filter-toolbar mb-3">
                        <div class="customer-filter-grid">
                            <div class="customer-filter-field customer-filter-search d-flex align-items-center gap-2">
                                <!-- <label for="searchText" class="form-label mb-1">Search</label> -->
                                <input type="text" name="search" id="searchText" class="form-control"
                                    value="{{ $search ?? '' }}" placeholder="Search by name, email or mobile">
                                <button type="submit" class="btn btn-theme" style="height: 42px;"><i
                                        class="ri-search-line me-1"></i>Search</button>
                            </div>
                            <div class="customer-filter-actions">
                                <a href="{{ route('admin.customers') }}" class="btn btn-outline-secondary">Reset</a>
                                <div class="dropdown">
                                    <button class="btn btn-theme dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false" style="min-height: 41px;">
                                        <i class="ri-download-line"></i> Export
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('admin.customers.export-excel', array_filter(['search' => $search ?? ''])) }}">
                                                <i class="ri-file-excel-line me-1 text-success"></i> Export Excel
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('admin.customers.export-pdf', array_filter(['search' => $search ?? ''])) }}">
                                                <i class="ri-file-pdf-line me-1 text-danger"></i> Export PDF
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="card card-table">
                        <div class="card-body">
                            <div class="table-responsive table-product">
                                <table class="table table table-modern align-middle" id="table_id">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Customer Name</th>
                                            <th>Mobile No.</th>
                                            <th>Email ID</th>
                                            <th>User type</th>
                                            <th>Registration date</th>
                                            @if(!empty($hasApproval))
                                                <th>Approval</th>
                                            @endif
                                            <th>GST</th>

                                            <th class="text-center">Account</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allCustomers as $customer)
                                            <tr>
                                                <td>{{ ($allCustomers->firstItem() ?? 0) + $loop->index }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="table-image" style="float:none;">
                                                            <img src="{{ !empty($customer->profile_image) ? asset('public/uploads/customers/' . $customer->profile_image) : asset('public/uploads/customers/customer.png') }}"
                                                                class="img-fluid" alt="{{ $customer->name }}">
                                                        </div>
                                                        <div class="user-name">
                                                            <span>{{ $customer->name }}</span>
                                                            <!-- <span>{{ $customer->customer_address ?: 'No address added' }}</span> -->
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $customer->mobile ?: '-' }}</td>
                                                <td>{{ $customer->email }}</td>
                                                <td>
                                                    @php
                                                        $segment = is_string($customer->user_type ?? null) ? trim($customer->user_type) : '';
                                                    @endphp
                                                    @if($segment === '')
                                                        <span class="text-muted">—</span>
                                                    @elseif(strcasecmp($segment, 'Wholesaler') === 0)
                                                        <span
                                                            class="badge bg-primary-subtle text-primary border border-primary-subtle">Wholesaler</span>
                                                    @elseif(strcasecmp($segment, 'Retailer') === 0)
                                                        <span
                                                            class="badge bg-success-subtle text-success  border-success-subtle">Retailer</span>
                                                    @else
                                                        <span
                                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $segment }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap small">
                                                    @if(!empty($customer->registration_date))
                                                        {{ \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                @if(!empty($hasApproval))
                                                    <td>
                                                        @php
                                                            $ap = strtolower((string) ($customer->approval_status ?? 'approved'));
                                                        @endphp
                                                        @if($ap === 'pending')
                                                            <span
                                                                class="badge bg-warning-subtle text-warning   badge-soft-success border-warning-subtle">Pending</span>
                                                        @elseif($ap === 'rejected')
                                                            <span
                                                                class="badge bg-danger-subtle text-danger  border-danger-subtle">Rejected</span>
                                                        @else
                                                            <span
                                                                class="badge bg-success-subtle text-success badge-soft-success border-success-subtle">Approved</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td>
                                                    @php
                                                        $gst = trim((string) ($customer->gst_number ?? ''));
                                                        $gstOk = !empty($customer->gst_verified_at ?? null);
                                                        $isRetailerRow = strcasecmp(trim((string) ($customer->user_type ?? '')), 'Retailer') === 0;
                                                    @endphp
                                                    @if($isRetailerRow || $gst === '')
                                                        <span class="text-muted">—</span>
                                                    @elseif($gstOk)
                                                        <span
                                                            class="badge bg-success-subtle text-success border border-success-subtle"
                                                            title="Verified at {{ \Carbon\Carbon::parse($customer->gst_verified_at ?? null)->format('d M Y H:i') }}">Verified</span>
                                                    @else
                                                        <span
                                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Unverified</span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    @php
                                                        $apRow = strtolower((string) ($customer->approval_status ?? 'approved'));
                                                    @endphp
                                                    @if(!empty($hasApproval) && $apRow !== 'approved')
                                                        @if($apRow === 'pending')
                                                            <span class="text-muted small">Awaiting approval</span>
                                                        @else
                                                            <span class="text-muted small">—</span>
                                                        @endif
                                                    @else
                                                        <form method="POST"
                                                            action="{{ route('admin.customers.toggle-status', $customer->customer_id) }}"
                                                            class="status-toggle-form m-0">
                                                            @csrf
                                                            @php
                                                                $isCustomerActive = (int) $customer->status === 1;
                                                            @endphp
                                                            <label class="status-switch" title="Activate / deactivate account">
                                                                <input type="checkbox" aria-label="Toggle customer account" {{ $isCustomerActive ? 'checked' : '' }}
                                                                    onchange="this.form.submit()">
                                                                <span class="status-slider"></span>
                                                            </label>
                                                        </form>
                                                    @endif
                                                </td>
                                                <td>
                                                    <ul class="d-flex gap-2 mb-0 list-unstyled align-items-center">
                                                        <li>
                                                            <a href="{{ route('admin.view-customer', urlencode(Crypt::encrypt($customer->customer_id))) }}"
                                                                title="View profile">
                                                                <i class="ri-eye-line"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.edit-customer', urlencode(Crypt::encrypt($customer->customer_id))) }}"
                                                                title="Edit">
                                                                <i class="ri-pencil-line"></i>
                                                            </a>
                                                        </li>
                                                        @if(!empty($hasApproval) && $apRow === 'pending')
                                                            <li>
                                                                <form method="POST"
                                                                    action="{{ route('admin.customers.approve-registration', $customer->customer_id) }}"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-link p-0 text-success border-0"
                                                                        title="Approve registration"><i
                                                                            class="ri-checkbox-circle-line"></i></button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="btn btn-link p-0 text-danger border-0"
                                                                    title="Reject registration" data-bs-toggle="modal"
                                                                    data-bs-target="#rejectCustomerModal"
                                                                    data-customer-id="{{ $customer->customer_id }}"
                                                                    data-customer-name="{{ $customer->name }}">
                                                                    <i class="ri-close-circle-line"></i>
                                                                </button>
                                                            </li>
                                                        @endif
                                                        @php
                                                            $gstRow = trim((string) ($customer->gst_number ?? ''));
                                                            $canVerifyGst = $gstRow !== '' && empty($customer->gst_verified_at);
                                                        @endphp
                                                        @if($canVerifyGst && !empty($hasGstVerified))
                                                            <li>
                                                                <form method="POST"
                                                                    action="{{ route('admin.customers.verify-gst', $customer->customer_id) }}"
                                                                    class="d-inline"
                                                                    onsubmit="return confirm('Mark GST as verified for this customer?');">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-link p-0 text-primary border-0"
                                                                        title="Verify GST"><i
                                                                            class="ri-shield-check-line"></i></button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a href="javascript:void(0)" title="Delete"
                                                                data-customer-id="{{ urlencode(Crypt::encrypt($customer->customer_id)) }}"
                                                                data-customer-name="{{ $customer->name }}"
                                                                data-bs-toggle="modal" data-bs-target="#deleteCustomerModal">
                                                                <i class="ri-delete-bin-line text-danger"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ !empty($hasApproval) ? 10 : 9 }}"
                                                    class="text-center text-muted py-4">No customers found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($allCustomers->hasPages())
                                <div
                                    class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-3 admin-pagination-wrap">
                                    <div class="text-muted small">
                                        Showing {{ $allCustomers->firstItem() }} to {{ $allCustomers->lastItem() }} of
                                        {{ $allCustomers->total() }} entries
                                    </div>
                                    <div>
                                        {{ $allCustomers->onEachSide(1)->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal fade" id="rejectCustomerModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject registration</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="#" id="rejectCustomerForm">
                                    @csrf
                                    <div class="modal-body">
                                        <p class="mb-2">Reject registration for <strong id="rejectCustomerName"></strong>?
                                        </p>
                                        <label class="form-label small text-muted">Optional note (shown in confirmation
                                            only)</label>
                                        <textarea class="form-control" name="reason" rows="2" maxlength="500"
                                            placeholder="Reason for rejection (optional)"></textarea>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject registration</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <i class="ri-error-warning-line text-danger" style="font-size: 48px;"></i>
                                    <p class="mt-3 mb-1">Are you sure you want to delete this customer?</p>
                                    <p class="mb-0 text-muted" id="deleteCustomerName"></p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <a href="#" id="confirmDeleteCustomerBtn" class="btn btn-danger">Yes, Delete</a>
                                </div>
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
        .customer-stat {
            border-radius: 12px;
            border-left: 3px solid transparent;
        }

        .customer-stat .card-body {
            padding: 18px 14px 18px 12px;
        }

        .customer-stat .stat-label {
            display: block;
            font-size: 15px;
            font-weight: 500;
            color: #5f6b7a;
        }

        .customer-stat .stat-value {
            margin: 6px 0 0;
            font-size: 40px;
            line-height: 1;
            font-weight: 700;
            color: #121f2d;
        }

        .customer-stat .stat-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 8px;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .customer-stat-teal {
            background: #dff1ed;
            border-left-color: #1ca18c;
        }

        .customer-stat-teal .stat-icon-box {
            background: #1ca18c;
        }

        .customer-stat-amber {
            background: #f6efe2;
            border-left-color: #f2a533;
        }

        .customer-stat-amber .stat-icon-box {
            background: #f2a533;
        }

        .customer-stat-rose {
            background: #f4e8e7;
            border-left-color: #f06265;
        }

        .customer-stat-rose .stat-icon-box {
            background: #f06265;
        }

        .customer-stat-mint {
            background: #e6f4ef;
            border-left-color: #0da487;
        }

        .customer-stat-mint .stat-icon-box {
            background: #0da487;
        }

        .customer-stat-slate {
            background: #eceff3;
            border-left-color: #64748b;
        }

        .customer-stat-slate .stat-icon-box {
            background: #64748b;
        }

        .customer-filter-toolbar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
        }

        .customer-filter-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            justify-content: space-between;
        }

        .customer-filter-search {
            flex: 0 1 320px;
        }

        .customer-filter-field .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        .customer-filter-field .form-select,
        .customer-filter-field .form-control {
            min-height: 41px;
            border-color: #cbd5e1;
        }

        .customer-filter-field .form-select:focus,
        .customer-filter-field .form-control:focus {
            border-color: #0da487;
            box-shadow: 0 0 0 .2rem rgba(13, 164, 135, .16);
        }

        .customer-filter-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .customer-filter-actions .btn {
            min-height: 41px;
            min-width: 96px;
        }

        @media (max-width: 767px) {
            .customer-stat .stat-label {
                font-size: 12px;
            }

            .customer-stat .stat-value {
                font-size: 30px;
            }

            .customer-filter-toolbar {
                padding: 12px;
            }

            .customer-filter-grid {
                flex-direction: column;
                gap: 10px;
            }

            .customer-filter-search {
                flex: 1 1 100%;
            }

            .customer-filter-actions {
                width: 100%;
                justify-content: stretch;
            }

            .customer-filter-actions .btn {
                flex: 1;
            }
        }

        @media (min-width: 768px) and (max-width: 1199px) {
            .customer-filter-search {
                flex: 0 1 280px;
            }
        }

        .status-switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
        }

        .status-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .status-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #d4d7dd;
            transition: .25s;
            border-radius: 24px;
        }

        .status-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            top: 3px;
            background-color: #fff;
            transition: .25s;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
        }

        .status-switch input:checked+.status-slider {
            background-color: #0da487;
        }

        .status-switch input:checked+.status-slider:before {
            transform: translateX(22px);
        }

        .admin-pagination-wrap .pagination {
            margin-bottom: 0;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var rejectModal = document.getElementById('rejectCustomerModal');
            if (rejectModal) {
                rejectModal.addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    if (!trigger) {
                        return;
                    }
                    var id = trigger.getAttribute('data-customer-id');
                    var name = trigger.getAttribute('data-customer-name') || 'this customer';
                    document.getElementById('rejectCustomerName').textContent = name;
                    var base = @json(rtrim(url('/admin/customers'), '/'));
                    document.getElementById('rejectCustomerForm').action = base + '/' + encodeURIComponent(id) + '/reject-registration';
                });
            }

            var deleteModal = document.getElementById('deleteCustomerModal');
            if (!deleteModal) {
                return;
            }

            deleteModal.addEventListener('show.bs.modal', function (event) {
                var trigger = event.relatedTarget;
                if (!trigger) {
                    return;
                }

                var encryptedId = trigger.getAttribute('data-customer-id');
                var customerName = trigger.getAttribute('data-customer-name') || 'this customer';
                document.getElementById('deleteCustomerName').textContent = customerName;
                document.getElementById('confirmDeleteCustomerBtn').href = '/admin/delete-customer/' + encryptedId;
            });
        });
    </script>
@endsection