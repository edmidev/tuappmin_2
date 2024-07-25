@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <pqrs-chats-component asset="{{ asset('/') }}" :auth="{{ Auth::user() }}" :conjunto_residencial="{{ $conjunto_residencial }}"
    :pqrs="{{ $pqrs }}"></pqrs-chats-component>
@endsection

@section('asset_end')
    
@endsection