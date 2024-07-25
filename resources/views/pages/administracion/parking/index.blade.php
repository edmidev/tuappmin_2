@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <parking-component :auth="{{ Auth::user() }}" :conjunto_residencial="{{ $conjunto_residencial }}"></parking-component>
@endsection

@section('asset_end')
    
@endsection