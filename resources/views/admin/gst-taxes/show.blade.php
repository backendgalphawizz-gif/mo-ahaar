@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center flex-wrap gap-2 mb-4">
            <h5 class="mb-0">GST Tax Details</h5>
            <a class="btn btn-outline-secondary btn-sm ms-auto" href="{{ route('admin.gst-taxes.index') }}">
                <i class="ri-arrow-left-line me-1"></i>Back to list
            </a>
            <a class="btn btn-theme btn-sm" href="{{ route('admin.gst-taxes.edit', $gst_tax->id) }}">
                <i class="ri-pencil-line me-1"></i>Edit
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width:40%">ID</th>
                                <td>{{ $gst_tax->id }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Name</th>
                                <td class="fw-medium">{{ $gst_tax->name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Percentage</th>
                                <td>{{ number_format((float) $gst_tax->percentage, 2) }}%</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    @if($gst_tax->status === 1)
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-warning">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Products using this slab</th>
                                <td>{{ $gst_tax->products()->count() }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Created</th>
                                <td>{{ $gst_tax->created_at->format('M j, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Updated</th>
                                <td>{{ $gst_tax->updated_at->format('M j, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
