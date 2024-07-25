@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <apartamentos-component :auth="{{ Auth::user() }}"></apartamentos-component>
@endsection

@section('asset_end')
    
@endsection