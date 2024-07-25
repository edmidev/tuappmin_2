@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <visitantes-component :auth="{{ Auth::user() }}" asset="{{ asset('/') }}" :conjunto_residencial="{{ $conjunto_residencial }}"></visitantes-component>
@endsection

@section('asset_end')
    
@endsection