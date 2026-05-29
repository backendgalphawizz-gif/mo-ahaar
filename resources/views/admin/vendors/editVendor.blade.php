@extends('layouts.app')

@section('content')
    @include('admin.vendors._form', ['vendor' => $vendor, 'tab' => $tab ?? 'personal', 'isEdit' => true])
@endsection

@if(($tab ?? 'personal') === 'business')
@section('scripts')
    @include('admin.partials.google-address-autocomplete')
@endsection
@endif

