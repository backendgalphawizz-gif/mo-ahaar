@extends('layouts.app')

@section('content')
    @include('admin.delivery._form', ['driver' => $driver, 'profile' => $profile, 'tab' => $tab ?? 'personal'])
@endsection
