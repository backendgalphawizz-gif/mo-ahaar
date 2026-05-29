@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0">Add GST Tax</h5>
            <a class="btn btn-outline-secondary btn-sm ms-auto" href="{{ route('admin.gst-taxes.index') }}">
                <i class="ri-arrow-left-line me-1"></i>Back to list
            </a>
        </div>

        {{-- @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-semibold mb-1">Please fix the errors below.</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif --}}

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.gst-taxes.store') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label class="form-label-title">GST Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="e.g. GST 18%"
                                       value="{{ old('name') }}"
                                       maxlength="100">
                                <small class="text-muted">Use a clear label like "GST 5%", "GST 12%", etc.</small>
                                @include('admin.partials.field-error', ['field' => 'name'])
                            </div>

                            <div class="mb-3">
                                <label class="form-label-title">Percentage (%) <span class="text-danger">*</span></label>
                                <input type="number" name="percentage" step="0.01" min="0" max="100"
                                       class="form-control @error('percentage') is-invalid @enderror"
                                       placeholder="e.g. 18.00"
                                       value="{{ old('percentage') }}">
                                <small class="text-muted">Enter a value between 0 and 100. Up to 2 decimal places.</small>
                                @include('admin.partials.field-error', ['field' => 'percentage'])
                            </div>

                            <div class="mb-4">
                                <label class="form-label-title">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @include('admin.partials.field-error', ['field' => 'status'])
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-theme px-4">Save</button>
                                <a href="{{ route('admin.gst-taxes.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
