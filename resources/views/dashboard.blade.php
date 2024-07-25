@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
<tarjetas-component :auth="{{ Auth::user() }}"></tarjetas-component>

<!--end row-->
@if(Auth::user()->rol_id == 1)
    <grafica-conjuntos-anuales-component></grafica-conjuntos-anuales-component>
@endif

<grafica-residentes-anuales-component></grafica-residentes-anuales-component>

@if(Auth::user()->rol_id != 1)
    <grafica-ingresos-parkings-component :prop_data="{{ json_encode($data) }}"></grafica-ingresos-parkings-component>
    <grafica-ingresos-administracion-component :prop_data="{{ json_encode($data) }}"></grafica-ingresos-administracion-component>
@endif

@endsection

@section('asset_end')
    <script src="{{asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js')}}"></script>
    <!--<script src="{{asset('assets/js/index2.js')}}"></script>-->
@endsection