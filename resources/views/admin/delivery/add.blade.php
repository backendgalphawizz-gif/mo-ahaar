@extends('layouts.app')

@section('content')
    @include('admin.delivery._form', ['driver' => null, 'profile' => null, 'tab' => $tab ?? 'personal'])
@endsection
