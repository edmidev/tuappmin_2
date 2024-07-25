@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <div class="content">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <img src="{{ asset('images/logo.png') }}" style="max-width: 180px;"
                        class="m-auto d-block mb-3">
        
                        <h4 class="text-dark text-center">Proceso finalizado</h4>
        
                        <img src="{{ asset('images/logo_pse.png') }}" style="max-width: 90px;"
                        class="m-auto d-block mb-3">
        
                        <p class="text-center mt-2 alert alert-info">Si su pago fue aprobado, es posible que tarde unos minutos en verse reflejado.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('asset_end')
    
@endsection