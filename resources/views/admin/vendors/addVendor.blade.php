@extends('layouts.app')

@section('content')
    @include('admin.vendors._form', ['vendor' => null, 'tab' => $tab ?? 'personal', 'isEdit' => false])
@endsection

