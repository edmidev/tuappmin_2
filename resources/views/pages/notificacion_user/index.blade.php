@extends('layouts.app')

@section('asset_init')
    <style>
        table td.notificacion {
            width: 400px;
            overflow: hidden;
            display: inline-block;
            white-space: pre-wrap;
            text-overflow: ellipsis;
        }
    </style>
@endsection

@section('content')
    <notificaciones-component asset="{{ asset('/') }}" :auth="{{ Auth::user() }}"></notificaciones-component>
@endsection

@section('asset_end')
    
@endsection