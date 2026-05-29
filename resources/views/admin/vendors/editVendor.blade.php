@extends('layouts.app')

@section('content')
    @include('admin.vendors._form', ['vendor' => $vendor, 'tab' => $tab ?? 'personal', 'isEdit' => true])
@endsection

