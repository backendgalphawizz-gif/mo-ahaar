@extends('layouts.app')

@section('content')

<style>
    select , .btn-theme , .btn-outline-secondary{
        height: 38px !important;
    }
</style>
<div class="page-body">
    <div class="container-fluid">
        <div class="card card-table">
            <div class="card-body">
                <div class="title-header option-title d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h5 class="mb-0">{{ $title }}</h5>
                        <small class="text-muted">Manage customer support tickets, assignments, and replies.</small>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="GET" action="{{ route('admin.tickets.index') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" style="height: 38px;" placeholder="Search subject or user">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="status">
                            <option value="">All statuses</option>
                            @foreach(\App\Models\Ticket::statusOptions() as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="type">
                            <option value="">All types</option>
                            @foreach(\App\Models\Ticket::typeOptions() as $type)
                                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="user_id">
                            <option value="">All users</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->user_id }}" {{ (string) request('user_id') === (string) $customer->user_id ? 'selected' : '' }}>
                                    {{ $customer->name ?: 'User #' . $customer->user_id }}{{ $customer->email ? ' (' . $customer->email . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-theme flex-fill">Filter</button>
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table all-package table-modern text-start">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assigned To</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td>{{ ($tickets->firstItem() ?? 0) + $loop->index }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $ticket->subject }}</div>
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($ticket->description, 80) }}</small>
                                    </td>
                                    <td>{{ $ticket->user?->name ?: 'User #' . $ticket->user_id }}</td>
                                    <td>{{ ucfirst($ticket->type) }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span></td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($ticket->priority) }}</span></td>
                                    <td>{{ $ticket->assignedTo?->name ?: 'Unassigned' }}</td>
                                    <td>{{ optional($ticket->created_at)->format('d M Y h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-sm btn-theme">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">No tickets found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($tickets->hasPages())
                    <div class="mt-3">
                        {{ $tickets->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection