@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center mb-4">
            <h5><i class="ri-edit-line me-2"></i>Edit: {{ $page->title }}</h5>
            <a href="{{ route('admin.static-pages.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">Back</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.static-pages.update', ['id' => $page->static_page_id]) }}" class="row g-3">
                    @csrf
                    <div class="col-md-8">
                        <label class="form-label-title">Title</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $page->title) }}">
                        @error('title')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-title">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="1" {{ (string) old('status', $page->status) === '1' ? 'selected' : '' }}>Published</option>
                            <option value="0" {{ (string) old('status', $page->status) === '0' ? 'selected' : '' }}>Draft</option>
                        </select>
                        @error('status')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label-title">Content</label>
                        <textarea id="page_content" name="content" rows="16" class="form-control @error('content') is-invalid @enderror" placeholder="Write page content here...">{{ old('content', $page->content) }}</textarea>
                        @error('content')<p class="invalid-feedback d-block mb-0">{{ $message }}</p>@enderror
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-theme">Save Page</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editorElement = document.querySelector('#page_content');
    if (!editorElement) {
        return;
    }

    ClassicEditor
        .create(editorElement)
        .catch(function (error) {
            console.error(error);
        });
});
</script>
@endsection
