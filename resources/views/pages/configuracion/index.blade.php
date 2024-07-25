@extends('layouts.app')

@section('asset_init')
    
@endsection

@section('content')
    <div class="card radius-15">
        <div class="card-header">
            Configuración de residencia
        </div>

        <div class="card-body">
            <form action="{{ route('configuracion.save') }}" method="post">
                @csrf

                <div class="row">
                    <div class="col-md-12">
                        <h6>Información de contacto</h6>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Teléfono porteria</label>
                            <input type="tel" maxlength="15" class="form-control" name="telefono_porteria"
                            value="{{ $informacion ? $informacion->telefono_porteria  : '' }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion"
                            value="{{ $informacion ? $informacion->direccion  : '' }}">
                        </div>
                    </div>

                    <hr class="w-100" />

                    <div class="col-md-12">
                        <h6>Datos de administración</h6>
                    </div>

                    <informacion-pagos-component :informacion_pagos="{{ $informacion_pagos }}"
                    :years="{{ json_encode($years) }}" :year_actual="{{ $year_actual }}"></informacion-pagos-component>

                    <hr class="w-100" />

                    <div class="col-md-12">
                        <h6>Datos de cuenta bancaria</h6>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Banco</label>
                            <input type="text" class="form-control" name="banco"
                            value="{{ $informacion ? $informacion->banco  : '' }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Número de cuenta</label>
                            <input type="text" class="form-control" name="numero_cuenta"
                            value="{{ $informacion ? $informacion->numero_cuenta  : '' }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Tipo de cuenta</label>
                            <select class="form-control" name="tipo_cuenta" id="tipo_cuenta">
                                <option value="1">Cuenta corriente</option>
                                <option value="2">Cuenta de ahorros</option>
                            </select>
                        </div>
                    </div>

                    <hr class="w-100" />

                    <div class="col-md-12">
                        <h6>Datos de parqueaderos</h6>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Número de parqueaderos</label>
                            <input type="number" class="form-control" name="numero_parqueaderos"
                            value="{{ $informacion ? $informacion->numero_parqueaderos  : '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Horas gratis</label>
                            <input type="number" class="form-control" name="horas_gratis"
                            value="{{ $informacion ? $informacion->horas_gratis  : '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Valor hora adicional <strong>(Moto)</strong></label>
                            <input type="number" class="form-control" name="valor_hora_adicional_moto"
                            value="{{ $informacion ? $informacion->valor_hora_adicional_moto  : '' }}" step=".01" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Valor hora adicional <strong>(Automóvil)</strong></label>
                            <input type="number" class="form-control" name="valor_hora_adicional_carro"
                            value="{{ $informacion ? $informacion->valor_hora_adicional_carro  : '' }}" step=".01" required>
                        </div>
                    </div>

                    <hr class="w-100" />

                    <div class="col-md-12">
                        <h6>Jornada diurna</h6>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Desde</label>
                            <input type="time" class="form-control" name="hour_diurno_init"
                            value="{{ $informacion ? $informacion->hour_diurno_init  : '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Hasta</label>
                            <input type="time" class="form-control" name="hour_diurno_end"
                            value="{{ $informacion ? $informacion->hour_diurno_end  : '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Costo de la jornada</label>
                            <input type="number" class="form-control" name="valor_diurno"
                            value="{{ $informacion ? $informacion->valor_diurno  : '' }}" step=".01" required>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <h6>Jornada nocturna</h6>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Desde</label>
                            <input type="time" class="form-control" name="hour_nocturno_init"
                            value="{{ $informacion ? $informacion->hour_nocturno_init  : '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Hasta</label>
                            <input type="time" class="form-control" name="hour_nocturno_end"
                            value="{{ $informacion ? $informacion->hour_nocturno_end  : '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Costo de la jornada</label>
                            <input type="number" class="form-control" name="valor_nocturno"
                            value="{{ $informacion ? $informacion->valor_nocturno  : '' }}" step=".01" required>
                        </div>
                    </div>

                    <hr class="w-100" />

                    <div class="col-md-12">
                        <h6>Jornada completa</h6>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Desde</label>
                            <input type="time" class="form-control" name="hour_completo_init"
                            value="{{ $informacion ? $informacion->hour_completo_init  : '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Hasta</label>
                            <input type="time" class="form-control" name="hour_completo_end"
                            value="{{ $informacion ? $informacion->hour_completo_end  : '' }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Costo de la jornada</label>
                            <input type="number" class="form-control" name="valor_completo"
                            value="{{ $informacion ? $informacion->valor_completo  : '' }}" step=".01" required>
                        </div>
                    </div>

                    <hr class="w-100" />

                    <div class="col-md-12">
                        <h6>Datos de integración epayco</h6>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">P_CUST_ID_CLIENTE</label>
                            <input type="text" class="form-control" name="p_cust_id_cliente"
                            value="{{ $informacion ? $informacion->p_cust_id_cliente  : '' }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">P_KEY</label>
                            <input type="text" class="form-control" name="p_key"
                            value="{{ $informacion ? $informacion->p_key  : '' }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">PUBLIC_KEY</label>
                            <input type="text" class="form-control" name="public_key"
                            value="{{ $informacion ? $informacion->public_key  : '' }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">PRIVATE_KEY</label>
                            <input type="text" class="form-control" name="private_key"
                            value="{{ $informacion ? $informacion->private_key  : '' }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('asset_end')
<script>
    $(document).ready(function(){
        $("#tipo_cuenta").val("{{ $informacion ? $informacion->tipo_cuenta : ''  }}");
    })
</script>
@endsection