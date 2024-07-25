@extends('layouts.app')

@section('asset_init')
    <style>
        table td.motivo {
            width: 200px;
            overflow: hidden;
            display: inline-block;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    </style>
@endsection

@section('content')
    <pqrs-component url="{{ url('/') }}" :auth="{{ Auth::user() }}" asset="{{ asset('/') }}" :conjunto_residencial="{{ $conjunto_residencial }}"
    id="{{ Request::get('id') }}"></pqrs-component>
@endsection

@section('asset_end')
    
@endsection