@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-add-circle-line me-2"></i>{{ $title }}</h5>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">Back</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fix the highlighted fields and submit again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Link (optional)</label>
                        <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link') }}" placeholder="https://example.com/page">
                        @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">When set, the app can open this URL when the banner is tapped.</small>
                    </div>

                    @if(!empty($hasBannerType))
                    <div class="col-md-6">
                        <label class="form-label">Home screen section <span class="text-danger">*</span></label>
                        <select name="banner_type" class="form-select @error('banner_type') is-invalid @enderror" required>
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
                        @error('banner_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Controls where this creative appears in the customer app home API.</small>
                    </div>
                    @endif

                    <div class="col-md-3">
                        <label class="form-label">Visible from</label>
                        <input type="date" name="visible_from" class="form-control @error('visible_from') is-invalid @enderror" value="{{ old('visible_from') }}">
                        @error('visible_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Optional. Leave empty to show with no start limit.</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Visible to</label>
                        <input type="date" name="visible_to" class="form-control @error('visible_to') is-invalid @enderror" value="{{ old('visible_to') }}">
                        @error('visible_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Optional. Leave empty for no end date.</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', '1') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="2" {{ old('status') === '2' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Banner Image <span class="text-danger">*</span></label>
                        <input type="file" name="banner_image" class="form-control @error('banner_image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp" required>
                        @error('banner_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Recommended wide image for homepage banner.</small>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-theme">Save Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
