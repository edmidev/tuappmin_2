@extends('layouts.app')

@section('asset_init')
    <style>
        table td.descripcion {
            width: 250px;
            overflow: hidden;
            display: inline-block;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    </style>
@endsection

@section('content')
    <comunicados-component asset="{{ asset('/') }}" id="{{ Request::get('id') }}"
    :auth="{{ Auth::user() }}"></comunicados-component>
@endsection

@section('asset_end')
    
@endsection