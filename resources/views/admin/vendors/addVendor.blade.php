@extends('layouts.app')

@section('content')
    @include('admin.vendors._form', ['vendor' => null, 'tab' => $tab ?? 'personal', 'isEdit' => false])
@endsection

@if(($tab ?? 'personal') === 'business')
@section('scripts')
    @include('admin.partials.google-address-autocomplete')
@endsection
@endif

