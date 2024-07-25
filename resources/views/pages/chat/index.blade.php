@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <chats-component asset="{{ asset('/') }}" :auth="{{ Auth::user() }}"></chats-component>
@endsection

@section('asset_end')
    
@endsection