@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <zonas-comunes-reservaciones-component url="{{ url('/') }}" asset="{{ asset('/') }}" :auth="{{ Auth::user() }}"></zonas-comunes-reservaciones-component>
@endsection

@section('asset_end')
    
@endsection