@extends('layouts.app')

@section('asset_init')
    <style>
        .tarjeta-add, .tarjeta-image{
            width: 55px;
            height: 55px;
            border: 2px solid #37b24d;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: ease .3s;
            color: #37b24d;
            margin-right: 10px;
            padding: 3px;
        }

        .tarjeta-add:hover{
            background: #37b24d;
            color: #fff;
        }

        .icon-delete-image{
            position: absolute;
            top: -10px;
            right: -10px;
            background: #868e96;
            border-radius: 100px;
            padding: 3px;
            width: 20px;
            height: 20px;
            color: #fff;
        }

        .icon-delete-image i{
            position: absolute;
            top: 0px;
        }
    </style>
@endsection

@section('content')
    <zonas-comunes-component url="{{ url('/') }}" asset="{{ asset('/') }}" :auth="{{ Auth::user() }}"></zonas-comunes-component>
@endsection

@section('asset_end')
    
@endsection