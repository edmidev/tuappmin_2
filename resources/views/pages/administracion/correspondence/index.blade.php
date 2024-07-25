@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <correspondences-component :auth="{{ Auth::user() }}" asset="{{ asset('/') }}" :conjunto_residencial="{{ $conjunto_residencial }}"></correspondences-component>
@endsection

@section('asset_end')
    
@endsection