@extends('layouts.app')

@section('asset_init')
    <style>
        table tr th, table tr td{
            text-align: center;
        }

        table tr td:hover{
            background: rgba(0, 0, 0, .1);
        }
    </style>
@endsection

@section('content')
    <pagos-component asset="{{ asset('/') }}"
    :conjunto_residencial="{{ $conjunto_residencial }}"
    :years="{{ json_encode($years) }}"
    :year_actual="{{ $year_actual }}"
    :request="{{ json_encode(Request::all()) }}"></pagos-component>
@endsection

@section('asset_end')
    
@endsection