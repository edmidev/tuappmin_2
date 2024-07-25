<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ConjuntoInformacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\Parking;
use App\Models\User;
use App\Models\Apartamento;
use App\Models\ResidenteConjunto;

/** Mails */
use App\Mail\ParkingMail;
use App\Models\TipoNotificacion;
use App\Http\Traits\NotificacionTrait;

class ParkingController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:1,2,3,4')->only('get_all');
        $this->middleware('permisosRolesApi:2,3,4')->only('store', 'reportar_salida');
        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        $parkings = Parking::leftJoin('residentes_conjuntos as rc', 'rc.id', 'parkings.residente_conjunto_id')
        ->leftJoin('users as u', 'u.id', '=', 'rc.user_id')
        ->where('parkings.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $parkings->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento, t.nombre_propietario';
        }
        else{
            $parkings->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero, t.nombre_propietario';
        }

        /** Filtramos los datos */
        if($request->numero_parking){
            $parkings->where('parkings.numero_parking', $request->numero_parking);
        }

        if($request->placa){
            $parkings->where('parkings.placa', 'like', '%' . $request->placa . '%');
        }

        if($request->check_show_all != 'true'){
            $parkings->whereNull('parkings.fecha_salida');
        }

        $parkings->select([
            'parkings.*', \DB::raw($select), 'u.name as residente',
            'u.telefono',
        ]);

        $parkings = $parkings->orderBy('parkings.id', 'DESC')->paginate(15);

        return response()->json(compact('parkings'), 201);
    }

    public function count_ocupados(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $count_parkings = Parking::where('conjunto_id', $owner_id)
        ->whereNull('fecha_salida')
        ->count();

        return response()->json(compact('count_parkings'), 201);
    }

    public function store(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);
        $conjunto_informacion = ConjuntoInformacion::where('conjunto_id', $owner_id)->first();
        $count_parking = Parking::where('conjunto_id', $owner_id)
        ->whereNull('fecha_salida')
        ->count();

        if($count_parking >= $conjunto_informacion->numero_parqueaderos){
            return response()->json('Ya no hay parkings disponibles', 200);
        }

        /** Verificamos que el parking no este ocupado */
        $parking = Parking::where('numero_parking', $request->numero_parking)
        ->whereNull('fecha_salida')
        ->where('conjunto_id', $owner_id)
        ->first();

        if($parking){
            return response()->json('Este parking esta ocupado', 200);
        }

        $residente = ResidenteConjunto::where('conjunto_id', $owner_id);
        if($conjunto_residencial->tipo == 'Apartamento'){
            $residente->where('apartamento_id', $request->apartamento_id);
        }
        else{
            $residente->where('casa_id', $request->casa_id);
        }

        $residente = $residente->orderBy('id', 'DESC')->first();
        $fecha_ingreso = date('Y-m-d H:i:s');
        $only_fecha_ingreso = date('Y-m-d', strtotime($fecha_ingreso));
        $hora_ingreso = strtotime($fecha_ingreso);
        $fecha_estimada_salida = null;
        $costo_jornada = null;

        $jornada = $request->jornada;
        if($jornada == 'hora'){
            $jornada = 1;
        }
        else if($jornada == 'diurno_nocturno'){
            $encontrado = false;
            $hour_diurno_init = strtotime($conjunto_informacion->hour_diurno_init);
            $hour_diurno_end = strtotime($conjunto_informacion->hour_diurno_end);

            if($hora_ingreso >= $hour_diurno_init && $hora_ingreso <= $hour_diurno_end){
                $fecha_estimada_salida = $only_fecha_ingreso . ' ' . $conjunto_informacion->hour_diurno_end;
                $costo_jornada = $conjunto_informacion->valor_diurno;
                $encontrado = true;
                $jornada = 2;
            }

            $hour_nocturno_init = strtotime($conjunto_informacion->hour_nocturno_init);
            $hour_nocturno_end = strtotime($conjunto_informacion->hour_nocturno_end);

            if(!$encontrado){
                if($hour_nocturno_init >= $hour_nocturno_end){
                    if($hora_ingreso >= $hour_nocturno_init && $hora_ingreso >= $hour_nocturno_end){
                        $fecha_estimada_salida = date('Y-m-d', strtotime("+1 day", strtotime($only_fecha_ingreso)));
                        $fecha_estimada_salida = $fecha_estimada_salida . ' ' . $conjunto_informacion->hour_nocturno_end;
                        $costo_jornada = $conjunto_informacion->valor_nocturno;
                        $encontrado = true;
                        $jornada = 3;
                    }
                }
                else{
                    if($hora_ingreso >= $hour_nocturno_init && $hora_ingreso <= $hour_nocturno_end){
                        $fecha_estimada_salida = $only_fecha_ingreso . ' ' . $conjunto_informacion->hour_nocturno_end;
                        $costo_jornada = $conjunto_informacion->valor_nocturno;
                        $encontrado = true;
                        $jornada = 3;
                    }
                }
            }

            if(!$encontrado){
                return response()->json('Jornada no disponible', 200);
            }
        }
        else{
            $encontrado = false;
            $hour_completo_init = strtotime($conjunto_informacion->hour_completo_init);
            $hour_completo_end = strtotime($conjunto_informacion->hour_completo_end);

            if($hour_completo_init >= $hour_completo_end){
                if($hora_ingreso >= $hour_completo_init && $hora_ingreso >= $hour_completo_end){
                    $fecha_estimada_salida = date('Y-m-d', strtotime("+1 day", strtotime($only_fecha_ingreso)));
                    $fecha_estimada_salida = $fecha_estimada_salida . ' ' . $conjunto_informacion->hour_completo_end;
                    $costo_jornada = $conjunto_informacion->valor_completo;
                    $encontrado = true;
                    $jornada = 4;
                }
            }
            else{
                if($hora_ingreso >= $hour_completo_init && $hora_ingreso <= $hour_completo_end){
                    $fecha_estimada_salida = $only_fecha_ingreso . ' ' . $conjunto_informacion->hour_completo_end;
                    $costo_jornada = $conjunto_informacion->valor_completo;
                    $encontrado = true;
                    $jornada = 4;
                }
            }
            
            if(!$encontrado){
                return response()->json('Jornada no disponible', 200);
            }
        }

        $parking = new Parking;
        $parking->residente_conjunto_id = $residente ? $residente->id : null;
        $parking->placa = $request->placa;
        $parking->tipo_vehiculo = $request->tipo_vehiculo;
        $parking->numero_parking = $request->numero_parking;
        $parking->fecha_ingreso = $fecha_ingreso;
        $parking->fecha_estimada_salida = $fecha_estimada_salida;
        $parking->jornada = $jornada;
        $parking->costo_jornada = $costo_jornada;
        $parking->acceso_permitido = $this->auth->id;
        $parking->conjunto_id = $owner_id;
        $parking->save();

        // Guardamos notificacion en la base de datos
        $notificationType = TipoNotificacion::where('class', 'app')->where('icon', 'parqueadero')->value('id');
        NotificacionTrait::store($notificationType, $this->auth->id, $parking->id,'Ingreso al parqueadero registrado', $residente->user_id, 'Se ha registrado un ingreso de parqueadero');

        return response()->json('Ingreso guardado', 201);
    }

    public function calcular_total(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $conjunto_informacion = ConjuntoInformacion::where('conjunto_id', $owner_id)->first();

        $parking = Parking::find($request->parking_id);
        $parking->fecha_salida = date('Y-m-d H:i:s');
        
        if($parking->jornada == 1){
            /** Calculamos el total a pagar por el parking */
            $t1 = strtotime($parking->fecha_salida);
            $t2 = strtotime($parking->fecha_ingreso);

            $diff = $t1 - $t2;
            $horas = $diff / ( 60 * 60 );
            $horas = ceil($horas);

            $horas = $horas - ($conjunto_informacion->horas_gratis ? $conjunto_informacion->horas_gratis : 0);
            if($horas > 0){
                $valor_hora_adicional = $parking->tipo_vehiculo == 1 ? $conjunto_informacion->valor_hora_adicional_moto : $conjunto_informacion->valor_hora_adicional_carro;
                $parking->total = $horas * $valor_hora_adicional;
                $parking->total = round($parking->total);
            }
            else{
                $parking->total = 0;
            }
        }
        else{
            /** Calculamos el total a pagar por el parking */
            $t1 = strtotime($parking->fecha_salida);
            $t2 = strtotime($parking->fecha_estimada_salida);

            $diff = $t1 - $t2;
            $horas = $diff / ( 60 * 60 );
            $horas = ceil($horas);

            if($horas > 0){
                $valor_hora_adicional = $parking->tipo_vehiculo == 1 ? $conjunto_informacion->valor_hora_adicional_moto : $conjunto_informacion->valor_hora_adicional_carro;
                $total = $horas * $valor_hora_adicional;
                $total = round($total);
                $parking->total = $parking->costo_jornada + $total;
            }
            else{
                $parking->total = $parking->costo_jornada;
            }
        }

        return response()->json(compact('parking'), 201);
    }

    public function reportar_salida(Request $request)
    {
        $parking = Parking::find($request->parking_id);

        if($parking->fecha_salida){
            return response()->json('Esta salida ya fue reportada', 200);
        }

        $parking->fecha_salida = $request->fecha_salida;
        $parking->acceso_salida = $this->auth->id;
        $parking->total = $request->total;
        $parking->save();

        return response()->json('Salida reportada', 201);
    }

    public function send_comprobante(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);
        
        $parking = Parking::leftJoin('residentes_conjuntos as rc', 'rc.id', 'parkings.residente_conjunto_id')
        ->leftJoin('users as u', 'u.id', '=', 'rc.user_id')
        ->join('users as us', 'us.id', '=', 'parkings.conjunto_id')
        ->where('parkings.id', $request->parking_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $parking->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento, t.nombre_propietario';
        }
        else{
            $parking->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero, t.nombre_propietario';
        }

        $parking->select([
            'parkings.*', \DB::raw($select), 'u.name as residente',
            'u.telefono', 'us.name as conjunto', 'us.nit', 'us.telefono', 'us.direccion'
        ]);

        $parking = $parking->first();

        Mail::to($request->email)->send(new ParkingMail($parking));

        return response()->json('Email enviado', 201);
    }
}
