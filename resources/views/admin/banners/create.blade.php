@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <h4 class="mb-4"><i class="ri-arrow-left-line me-2"></i>Add Banner</h4>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fix the highlighted fields and submit again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card dashboard-card mx-auto" style="max-width: 760px;">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="row g-3" novalidate>
                    @csrf

                    <div class="col-12">
                        <label class="form-label">Banner Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Enter banner title" maxlength="190">
                        @include('admin.partials.field-error', ['field' => 'title'])
                    </div>

                    <div class="col-12">
                        <label class="form-label">Banner Image</label>
                        <div class="upload-area">
                            <i class="ri-image-2-line upload-icon"></i>
                            <div>Click to upload image</div>
                            <small class="text-muted">Recommended size: 1200 x 400 pixels</small>
                            <input type="file" name="banner_image" class="upload-input @error('banner_image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                        @include('admin.partials.field-error', ['field' => 'banner_image'])
                    </div>

                    @if(!empty($hasBannerType))
                    <div class="col-md-6">
                        <label class="form-label">Home screen section <span class="text-danger">*</span></label>
                        <select name="banner_type" class="form-select @error('banner_type') is-invalid @enderror">
                            @php
                                $typeLabels = [
                                    'slider' => 'Slider / hero carousel',
                                    'offer' => 'Offer',
                                    'promotion' => 'Promotion',
                                    'announcement' => 'Announcement',
                                ];
                            @endphp
                            @foreach(\App\Models\Banner::homeBannerTypeOptions() as $opt)
                                <option value="{{ $opt }}" {{ old('banner_type', 'slider') === $opt ? 'selected' : '' }}>{{ $typeLabels[$opt] ?? $opt }}</option>
                            @endforeach
                        </select>
                        @include('admin.partials.field-error', ['field' => 'banner_type'])
                        <small class="text-muted">Controls where this creative appears in the customer app home API.</small>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label">Visible from</label>
                        <input type="date" name="visible_from" class="form-control @error('visible_from') is-invalid @enderror" value="{{ old('visible_from') }}">
                        @include('admin.partials.field-error', ['field' => 'visible_from'])
                        <small class="text-muted">Optional. Leave empty to show with no start limit.</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Visible to</label>
                        <input type="date" name="visible_to" class="form-control @error('visible_to') is-invalid @enderror" value="{{ old('visible_to') }}">
                        @include('admin.partials.field-error', ['field' => 'visible_to'])
                        <small class="text-muted">Optional. Leave empty for no end date.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="2" {{ old('status') === '2' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @include('admin.partials.field-error', ['field' => 'status'])
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top mt-2">
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Save Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.upload-area { border: 1px dashed #d1d5db; border-radius: 12px; min-height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center; position: relative; background: #f9fafb; }
.upload-icon { font-size: 28px; color: #94a3b8; }
.upload-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
</style>
@endsection
