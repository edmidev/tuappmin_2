@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <public-services-component asset="{{ asset('/') }}"></public-services-component>
@endsection

@section('asset_end')
    
@endsection