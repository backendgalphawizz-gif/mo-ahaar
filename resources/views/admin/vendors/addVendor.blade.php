@extends('layouts.app')

@section('content')
    @include('admin.vendors._form', ['vendor' => null, 'tab' => $tab ?? 'personal', 'isEdit' => false])
@endsection

@section('scripts')
<style>
.vendor-form-tabs .nav-link.active { border-bottom: 2px solid #c9973a; color: #111827; font-weight: 600; }
.form-section { background: #fafafa; border: 1px solid #ececec; border-radius: 12px; padding: 20px; }
.form-section h6 { margin-bottom: 4px; font-weight: 700; }
</style>
@endsection
