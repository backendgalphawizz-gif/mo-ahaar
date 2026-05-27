@extends('layouts.app')

@section('content')

    <style>
        .btn-theme,
        .btn-outline-secondary,
        input[type="date"] {
            height: 38px !important;
        }
    </style>
    <div class="page-body">
        <div class="container-fluid">
            <div class="title-header option-title d-flex align-items-center mb-4">
                <h5><i class="ri-file-list-3-line me-2"></i>Order Reports</h5>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 report-metric report-metric-primary h-100">
                        <div class="card-body"><small>Total Orders</small>
                            <h3>{{ $summary['total'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 report-metric report-metric-success h-100">
                        <div class="card-body"><small>Completed</small>
                            <h3>{{ $summary['completed'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 report-metric report-metric-warning h-100">
                        <div class="card-body"><small>Pending</small>
                            <h3>{{ $summary['pending'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 report-metric report-metric-danger h-100">
                        <div class="card-body"><small>Cancelled</small>
                            <h3>{{ $summary['cancelled'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-table">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.orders') }}" class="report-filter-form mb-3">
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(['pending', 'processing', 'confirmed', 'completed', 'delivered', 'cancelled', 'rejected', 'failed'] as $statusOption)
                                <option value="{{ $statusOption }}" {{ $status === $statusOption ? 'selected' : '' }}>
                                    {{ ucfirst($statusOption) }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-theme">Generate</button>
                        <a href="{{ route('admin.reports.orders') }}" class="btn btn-outline-secondary">Reset</a>
                        <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-download-line"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.orders.export-excel', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'status' => $status])) }}">
                                    <i class="ri-file-excel-line me-1 text-success"></i> Export Excel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.reports.orders.export-pdf', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'status' => $status])) }}">
                                    <i class="ri-file-pdf-line me-1 text-danger"></i> Export PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table all-package theme-table table-product align-middle text-start">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order ID</th>
                                    <!-- Vendor column removed -->
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>#{{ $order->order_number }}</td>
                                        <!-- Vendor cell removed -->
                                        <td>{{ optional(optional($order->customer)->user)->name ?? 'N/A' }}</td>
                                        <td>₹{{ number_format((float) $order->total_amount, 2) }}</td>
                                        <td>{{ ucfirst((string) $order->payment_status) }}</td>
                                        <td>{{ ucfirst((string) $order->order_status) }}</td>
                                        <td class="text-center">{{ optional($order->created_at)->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No order records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <style>
        .report-metric {
            border-radius: 12px;
            color: #fff;
        }

        .report-metric .card-body {
            padding: 16px 18px;
        }

        .report-metric small {
            text-transform: uppercase;
            letter-spacing: .05em;
            opacity: .9;
        }

        .report-metric h3 {
            margin: 8px 0 0;
            font-weight: 700;
        }

        .report-metric-primary {
            background: linear-gradient(135deg, #366e9369 0%, #0f4c75bd 55%, #3282b8bf 100%);
        }

        .report-metric-success {
            background: linear-gradient(135deg, #198754a8 0%, #198754d9 55%, #146c43eb 100%);
        }

        .report-metric-warning {
            background: linear-gradient(135deg, #ff943bad 0%, #fd7e14c2 55%, #d0620abf 100%);
        }

        .report-metric-danger {
            background: linear-gradient(135deg, #f95766d9 0%, #dc3545c7 55%, #a71d2ae0 100%);
        }

        /* .report-filter-form {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            align-items: center;
        }

        @media (max-width: 991px) {
            .report-filter-form {
                grid-template-columns: 1fr;
            }
        } */

        .report-filter-form { 
            display:grid; 
            grid-template-columns: repeat(6, minmax(0, 1fr)); gap:12px; 
            align-items:center; 
        
        }
        @media (max-width: 991px) { 
            .report-filter-form { 
                grid-template-columns: 1fr; 
            } 
        }
    </style>
@endsection