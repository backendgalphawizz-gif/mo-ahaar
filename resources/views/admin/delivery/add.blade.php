@extends('layouts.app')

@section('content')
    @include('admin.delivery._form', ['driver' => null, 'profile' => null])
@endsection

@section('scripts')
<style>
.form-section { background: #fafafa; border: 1px solid #ececec; border-radius: 12px; padding: 20px; }
.form-section h6 { margin-bottom: 4px; font-weight: 700; }
</style>
@endsection
