@extends('layouts.app')

@section('asset_init')
    <link rel="stylesheet" href="{{ asset('css/reproductor.css') }}">
@endsection

@section('content')
    <minutas-component :auth="{{ Auth::user() }}" asset="{{ asset('/') }}"></minutas-component>
@endsection

@section('asset_end')
    
@endsection