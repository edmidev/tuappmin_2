@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <casas-component :auth="{{ Auth::user() }}"></casas-component>
@endsection

@section('asset_end')
    
@endsection