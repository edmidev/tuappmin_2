@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <users-component :auth="{{ Auth::user() }}"></users-component>
@endsection

@section('asset_end')
    
@endsection