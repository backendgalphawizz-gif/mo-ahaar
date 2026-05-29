@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0">Edit GST Tax</h5>
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
                        <form method="POST" action="{{ route('admin.gst-taxes.update', $gst_tax->id) }}" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label-title">GST Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="e.g. GST 18%"
                                       value="{{ old('name', $gst_tax->name) }}"
                                       maxlength="100">
                                <small class="text-muted">Use a clear label like "GST 5%", "GST 12%", etc.</small>
                                @include('admin.partials.field-error', ['field' => 'name'])
                            </div>

                            <div class="mb-3">
                                <label class="form-label-title">Percentage (%) <span class="text-danger">*</span></label>
                                <input type="number" name="percentage" step="0.01" min="0" max="100"
                                       class="form-control @error('percentage') is-invalid @enderror"
                                       placeholder="e.g. 18.00"
                                       value="{{ old('percentage', $gst_tax->percentage) }}">
                                <small class="text-muted">Enter a value between 0 and 100. Up to 2 decimal places.</small>
                                @include('admin.partials.field-error', ['field' => 'percentage'])
                            </div>

                            <div class="mb-4">
                                <label class="form-label-title">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="1" {{ (string) old('status', $gst_tax->status) === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ (string) old('status', $gst_tax->status) === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @include('admin.partials.field-error', ['field' => 'status'])
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-theme px-4">Update</button>
                                <a href="{{ route('admin.gst-taxes.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- GST calculation preview --}}
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Calculation Preview</h6>
                        <p class="mb-1 small text-muted">For a product priced at ₹1,000:</p>
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted">Base Price</td>
                                <td class="fw-medium">₹1,000.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">GST ({{ number_format((float) $gst_tax->percentage, 2) }}%)</td>
                                <td class="fw-medium">₹{{ number_format(1000 * $gst_tax->percentage / 100, 2) }}</td>
                            </tr>
                            <tr class="table-light">
                                <td class="fw-semibold">Final Price</td>
                                <td class="fw-bold text-success">₹{{ number_format(1000 + (1000 * $gst_tax->percentage / 100), 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
