@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <noticias-component asset="{{ asset('/') }}"></noticias-component>
@endsection

@section('asset_end')
    
@endsection