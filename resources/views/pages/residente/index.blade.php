@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <residentes-component :auth="{{ Auth::user() }}"
    :residencia="{{ $data['residencia'] }}"></residentes-component>
@endsection

@section('asset_end')
    
@endsection