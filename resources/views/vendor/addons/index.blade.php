@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">Add Ons</h5>
                <small class="text-muted">Add Ons List</small>
            </div>
            <a href="{{ route('vendor.addons.create') }}" class="btn btn-sm btn-addon">+ Add New Add On</a>
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ADD ON NAME</th>
                                <th>PRICE</th>
                                <th>TYPE</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($addons as $addon)
                                <tr>
                                    <td>{{ $addon['name'] }}</td>
                                    <td>₹{{ number_format((float)$addon['price'], 0) }}</td>
                                    <td>
                                        <span class="{{ $addon['type'] === 'veg' ? 'text-success' : 'text-danger' }}">
                                            {{ ucfirst($addon['type']) }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('vendor.addons.toggle', $addon['id']) }}">
                                            @csrf
                                            <label class="status-switch m-0">
                                                <input type="checkbox" onchange="this.form.submit()" {{ !empty($addon['is_active']) ? 'checked' : '' }}>
                                                <span class="status-slider"></span>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="d-flex gap-1">
                                        <a href="{{ route('vendor.addons.edit', $addon['id']) }}" class="btn btn-sm btn-outline-primary"><i class="ri-pencil-line"></i></a>
                                        <form method="POST" action="{{ route('vendor.addons.delete', $addon['id']) }}" onsubmit="return confirm('Delete add on?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-3 text-muted">No add ons found.</td></tr>
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
.btn-addon{background:#8a3f00;border-color:#8a3f00;color:#fff}
.btn-addon:hover{background:#733400;border-color:#733400;color:#fff}
.status-switch{position:relative;display:inline-block;width:36px;height:20px}
.status-switch input{opacity:0;width:0;height:0}
.status-slider{position:absolute;cursor:pointer;inset:0;background:#d1d5db;border-radius:999px;transition:.2s}
.status-slider:before{content:"";position:absolute;height:14px;width:14px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s}
.status-switch input:checked + .status-slider{background:#22c55e}
.status-switch input:checked + .status-slider:before{transform:translateX(16px)}
</style>
@endsection

