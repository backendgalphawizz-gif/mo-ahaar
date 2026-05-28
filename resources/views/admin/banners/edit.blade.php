@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <h4 class="mb-4"><i class="ri-arrow-left-line me-2"></i>Edit Banner</h4>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fix the highlighted fields and submit again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card dashboard-card mx-auto" style="max-width: 760px;">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.banners.update', $banner->id) }}" enctype="multipart/form-data" class="row g-3">
                    @csrf

                    <div class="col-12">
                        <label class="form-label">Banner Title</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $banner->title) }}">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Banner Image</label>
                        <div class="position-relative rounded overflow-hidden border">
                            <img src="{{ asset('public/uploads/banners/' . $banner->banner_image) }}" alt="banner" class="w-100" style="height:200px;object-fit:cover;">
                            <label class="replace-upload">
                                <i class="ri-upload-2-line me-1"></i>Upload New Image
                                <small class="d-block">Click to replace current image</small>
                                <input type="file" name="banner_image" accept=".jpg,.jpeg,.png,.webp" class="@error('banner_image') is-invalid @enderror">
                            </label>
                        </div>
                        @error('banner_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Visible from</label>
                        <input type="date" name="visible_from" class="form-control @error('visible_from') is-invalid @enderror" value="{{ old('visible_from', optional($banner->visible_from)->format('Y-m-d')) }}">
                        @error('visible_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Visible to</label>
                        <input type="date" name="visible_to" class="form-control @error('visible_to') is-invalid @enderror" value="{{ old('visible_to', optional($banner->visible_to)->format('Y-m-d')) }}">
                        @error('visible_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ (string) old('status', $banner->status) === '1' ? 'selected' : '' }}>Active</option>
                            <option value="2" {{ (string) old('status', $banner->status) === '2' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top mt-2">
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Update Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.replace-upload { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); background: rgba(255, 255, 255, 0.9); border-radius: 10px; padding: 10px 14px; text-align: center; font-weight: 600; color: #f97316; cursor: pointer; }
.replace-upload small { color: #64748b; font-weight: 400; }
.replace-upload input { display: none; }
</style>
@endsection
