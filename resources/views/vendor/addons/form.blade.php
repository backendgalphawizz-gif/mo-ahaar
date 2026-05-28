@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @php $isEdit = !empty($addon); @endphp
        <h5 class="mb-3">Add Ons</h5>
        <div class="card dashboard-card mx-auto" style="max-width:700px;">
            <div class="card-body">
                <h5 class="mb-3">{{ $isEdit ? 'Edit Add On' : 'Add New Add On' }}</h5>
                <form method="POST" action="{{ $isEdit ? route('vendor.addons.update', $addon->id) : route('vendor.addons.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Add On Name</label>
                            <input name="name" class="form-control" value="{{ old('name', $isEdit ? $addon->name : '') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price (₹)</label>
                            <input name="price" class="form-control" type="number" min="0" step="0.01" value="{{ old('price', $isEdit ? $addon->price : '') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Veg / Non-Veg</label>
                            <select name="type" class="form-select" required>
                                <option value="veg" {{ old('type', $isEdit ? $addon->type : '') === 'veg' ? 'selected' : '' }}>Veg</option>
                                <option value="non-veg" {{ old('type', $isEdit ? $addon->type : '') === 'non-veg' ? 'selected' : '' }}>Non-Veg</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('vendor.addons.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
                        <button class="btn btn-sm btn-addon">{{ $isEdit ? 'Save Changes' : 'Add Add On' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>.btn-addon{background:#8a3f00;border-color:#8a3f00;color:#fff}.btn-addon:hover{background:#733400;border-color:#733400;color:#fff}</style>
@endsection

