@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <citofonias-component :auth="{{ Auth::user() }}" asset="{{ asset('/') }}" :conjunto_residencial="{{ $conjunto_residencial }}"></citofonias-component>
@endsection

@section('asset_end')
    
@endsection