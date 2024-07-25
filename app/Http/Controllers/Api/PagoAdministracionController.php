<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\ConjuntoInformacionPago;
use App\Models\ConjuntoInformacion;
use App\Models\Apartamento;
use App\Models\Casa;
use App\Models\User;
use App\Models\AdministracionPago;
use App\Models\AdministracionPagoMonth;
use App\Models\TokenFirebaseUser;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Http\Traits\NotificacionTrait;
use App\Http\Traits\FunctionsTraits;
use App\Http\Traits\Epayco;
use stdClass;

class PagoAdministracionController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:5');

        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_conjunto_informacion(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;
        $conjunto_residencial = User::find($owner_id);
        
        $informacion_pagos = ConjuntoInformacionPago::where('conjunto_id', $owner_id)
        ->orderBy('year', 'ASC')
        ->get();

        $informacion_adicional = ConjuntoInformacion::where('conjunto_id', $owner_id)
        ->first();

        $informacion_adicional->p_cust_id_cliente = $informacion_adicional->p_cust_id_cliente ? true : false;
        $informacion_adicional->p_key = $informacion_adicional->p_key ? true : false;
        $informacion_adicional->public_key = $informacion_adicional->public_key ? true : false;
        $informacion_adicional->private_key = $informacion_adicional->public_key ? true : false;

        return response()->json(compact('informacion_pagos', 'informacion_adicional'), 201);
    }

    public function get_pagos(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        /** Obtenemos la informacion del conjunto año seleccionado */
        $informacion_pago = ConjuntoInformacionPago::where('conjunto_id', $owner_id)
        ->where('year', $request->year)
        ->first();

        if(!$informacion_pago){
            $administraciones_pagos = [];

            return response()->json(compact('administraciones_pagos'), 201);
        }

        /** Obtenemos los pagos de la administracion para el año seleccionado */
        $administraciones_pagos = AdministracionPago::from('administracion_pagos as ap')
        ->join('residentes_conjuntos as rc', 'rc.id', '=', 'ap.residente_conjunto_id')
        ->join('administracion_pagos_months as apm', 'apm.administracion_pago_id', '=', 'ap.id')
        ->where('apm.year', $request->year)
        ->where('ap.residente_conjunto_id', $residencia->residente_conjunto_id)
        ->select([
            'apm.month', 'ap.estatus_pago'
        ])
        ->where('ap.estatus_pago', '!=', 2)
        ->orderBy('apm.month', 'ASC')
        ->get();

        /** Obtenemos la fecha de ingreso del residente */
        $fecha_ingreso = date('Y-m-d', strtotime($residencia->created_at));

        $month = date('m');
        $fecha_actual = date('Y-m-d');
        
        $data = [];
        /** Iteramos 12 veces una vez por cada mes */
        for ($i = 1; $i <= 12; $i++) { 
            $pagado = false;
            for ($j = 0; $j < count($administraciones_pagos); $j++) { 
                /** Verificamos si el mes ya fue pagado  */
                if($i == $administraciones_pagos[$j]->month){
                    $administraciones_pagos[$j]->pagado = $administraciones_pagos[$j]->estatus_pago == 3 ? true : false;
                    $administraciones_pagos[$j]->bgcolor = $administraciones_pagos[$j]->estatus_pago == 3 ? '#4eaa03' : '#f7d547';
                    $administraciones_pagos[$j]->color = '#fff';
                    array_push($data, $administraciones_pagos[$j]);
                    $pagado = true;
                    break;
                }
            }

            /** Si no ha sido pagado hay una serie de condiciones que hay que revisar */
            if(!$pagado){
                /** Calculamos cual seria la fecha limite del pago del mes, 
                 * dependiendo la configuracion del conjunto 
                 * */
                switch ($informacion_pago->limite_pago) {
                    case 1:
                        $date = $request->year . '-' . $i . '-' . '1';
                        $fecha_limite_pago = date('Y-m-d', strtotime($date));
                        break;
                    
                    case 2:
                        $date = $request->year . '-' . $i . '-' . '15';
                        $fecha_limite_pago = date('Y-m-d', strtotime($date));
                        break;

                    case 3:
                        $date = $request->year . '-' . $i . '-' . '1';
                        $ultimo_dia = date('t', strtotime($date));
                        $date = $request->year . '-' . $i . '-' . $ultimo_dia;
                        $fecha_limite_pago = date('Y-m-d', strtotime($date));
                        break;
                }

                if($fecha_ingreso < $fecha_limite_pago){
                    if($fecha_actual > $fecha_limite_pago){
                        $object = new stdClass;
                        $object->month = $i;
                        $object->pagado = false;
                        $object->bgcolor = '#e00000';
                        $object->color = '#fff';
                        $object->mora = true;
                        array_push($data, $object);
                    }
                    else{
                        $object = new stdClass;
                        $object->month = $i;
                        $object->pagado = false;
                        array_push($data, $object);
                    }
                }
                else{
                    $object = new stdClass;
                    $object->month = $i;
                    $object->pagado = false;
                    array_push($data, $object);
                }
            }
        }

        $administraciones_pagos = $data;

        return response()->json(compact('administraciones_pagos'), 201);
    }

    public function calcular_pago(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;
        //$conjunto_residencial = User::find($owner_id);

        /** Verificamos si ya fue pagada la administracion de este apartamento para el mes y año seleccionado */
        $administracion_pago = AdministracionPago::from('administracion_pagos as ap')
        ->join('residentes_conjuntos as rc', 'rc.id', '=', 'ap.residente_conjunto_id')
        ->join('administracion_pagos_months as apm', 'apm.administracion_pago_id', '=', 'ap.id')
        ->leftJoin('administracion_pagos_epayco as ape', 'ape.administracion_pago_id', '=', 'ap.id')
        ->where('apm.year', $request->year)
        ->where('apm.month', $request->month)
        ->where('ap.residente_conjunto_id', $residencia->residente_conjunto_id)
        ->where('ap.estatus_pago', '!=', 2);

        /*if($conjunto_residencial->tipo == 'Apartamento'){
            $administracion_pago->where('rc.apartamento_id', $residencia->residencia_id);
        }
        else{
            $administracion_pago->where('rc.casa_id', $residencia->residencia_id);
        }*/

        $administracion_pago->select([
            'ap.*', 'ape.x_ref_payco', 'ape.x_cardnumber'
        ]);

        $administracion_pago = $administracion_pago->first();

        if($administracion_pago){
            $detalles_pago['administracion_pago'] = $administracion_pago;
        }

        $detalles_pago['total'] = 0;
        $detalles_pago['payment_possible'] = true;

        /** Si no se ha pagado la administracion */
        if(!$administracion_pago){
            /** Obtenemos la fecha de ingreso del residente */
            $fecha_ingreso = date('Y-m-d', strtotime($residencia->created_at));

            /** Obtenemos la informacion del conjunto para el año seleccionado */
            $informacion_pago = ConjuntoInformacionPago::where('conjunto_id', $owner_id)
            ->where('year', $request->year)
            ->first();

            if(!$informacion_pago){
                $detalles_pago['payment_possible'] = false;

                return response()->json(compact('detalles_pago'), 201);
            }

            /** Calculamos cual seria la fecha limite del pago, 
             * dependiendo la configuracion del conjunto 
             * */
            switch ($informacion_pago->limite_pago) {
                case 1:
                    $date = $request->year . '-' . $request->month . '-' . '1';
                    $fecha_limite_pago = date('Y-m-d', strtotime($date));
                    break;
                
                case 2:
                    $date = $request->year . '-' . $request->month . '-' . '15';
                    $fecha_limite_pago = date('Y-m-d', strtotime($date));
                    break;

                case 3:
                    $date = $request->year . '-' . $request->month . '-' . '1';
                    $ultimo_dia = date('t', strtotime($date));
                    $date = $request->year . '-' . $request->month . '-' . $ultimo_dia;
                    $fecha_limite_pago = date('Y-m-d', strtotime($date));
                    break;
            }

            $detalles_pago['fecha_limite_pago'] = $fecha_limite_pago;

            /** 
             * Comparamos si la fecha limite del pago es mayor a la fecha 
             * en la que el residente ingreso al conjunto
             *  */

            $fecha_actual = date('Y-m-d');
            $descuento_pronto_pago = false;
            if($fecha_limite_pago >= $fecha_ingreso){
                $detalles_pago['payment_possible'] = true;
                $detalles_pago['total'] = $informacion_pago->valor_administracion;
                $administracion_atrasada = false;

                /** Verificamos si la fecha limite de pago ya esta atrasada */
                if($fecha_limite_pago < $fecha_actual){
                    $administracion_atrasada = true;
                    $months_diff = FunctionsTraits::dateDiffInMonths($fecha_actual, $fecha_limite_pago);
                    $months_diff++;
                    $detalles_pago['total_mora'] = (($informacion_pago->valor_administracion * $informacion_pago->interes_mora) / 100) * $months_diff;
                }

                /** Verificamos si el conjunto tiene fecha limite para el descuento del pronto pago */
                if($informacion_pago->limite_pronto_pago){
                    /** 
                    * Calculamos la diferencia entre la fecha limite del pago y la fecha actual
                    * para ver si se le puede aplicar el descuento por el pronto pago
                    * */

                    $days_diff = FunctionsTraits::dateDiffInDays($fecha_actual, $fecha_limite_pago);

                    if($days_diff == 0){
                        $detalles_pago['fecha_limite_pago'] = 'Hoy';
                    }
                    else if($days_diff < 10 && $days_diff > 0){
                        $detalles_pago['fecha_limite_pago'] = 'En ' . $days_diff . ($days_diff > 1 ? ' días' : ' día');
                    }
                    else{
                        $detalles_pago['fecha_limite_pago'] = $fecha_limite_pago;
                    }

                    if($days_diff >= $informacion_pago->limite_pronto_pago){
                        $descuento_pronto_pago = true;

                        $detalles_pago['caducidad_descuento_pronto_pago'] = date('Y-m-d', strtotime('-' . $informacion_pago->limite_pronto_pago . ' day', strtotime($fecha_limite_pago)));
                    }
                }
                else{
                    $descuento_pronto_pago = true;
                }

                if($descuento_pronto_pago){
                    $detalles_pago['total_descuento_pronto_pago'] = ($informacion_pago->valor_administracion * $informacion_pago->descuento_pronto_pago) / 100;
                    $detalles_pago['porcentaje_descuento_pronto_pago'] = $informacion_pago->descuento_pronto_pago;
                }

                /** Si la administracion no esta en mora calculamos el descuento por pago semestral
                 * y el descuento por pago anual
                 */
                if(!$administracion_atrasada ){
                    $month = $request->month;
                    $year = $request->year;

                    $calcular_administracion_semestre = true;
                    for ($i = 1; $i < 6; $i++) {
                        if($month == 12){
                            $month = 0;
                            $year++;
                        }

                        $month = $month + 1;
                        /** Verificamos si ya fue pagada la administracion de este apartamento para el mes y año seleccionado */
                        $administracion_pago = AdministracionPago::from('administracion_pagos as ap')
                        ->join('residentes_conjuntos as rc', 'rc.id', '=', 'ap.residente_conjunto_id')
                        ->join('administracion_pagos_months as apm', 'apm.administracion_pago_id', '=', 'ap.id')
                        ->where('apm.year', $year)
                        ->where('apm.month', $month)
                        ->where('ap.residente_conjunto_id', $residencia->residente_conjunto_id)
                        ->select([
                            'ap.*'
                        ])
                        ->first();

                        if($administracion_pago){
                            $calcular_administracion_semestre = false;
                            break;
                        }
                    }

                    if($calcular_administracion_semestre){
                        /** Calculamos descuento semestral */
                        $pago_semestre = new stdClass;
                        $total_administracion = ($informacion_pago->valor_administracion * 6);
                        $descuento = ($total_administracion * $informacion_pago->descuento_pago_semestre) / 100;
                        $pago_semestre->total = $total_administracion - $descuento;
                        $pago_semestre->total_descuento = $descuento;
                        $pago_semestre->porcentaje_descuento = $informacion_pago->descuento_pago_semestre;
                        $pago_semestre->month_inicial = $request->month;
                        $pago_semestre->month_final = $month;
                        $pago_semestre->year_inicial = $request->year;
                        $pago_semestre->year_final = $year;
                        $detalles_pago['pago_semestre'] = $pago_semestre;

                        $month = $request->month;
                        $year = $request->year;

                        $calcular_administracion_anual = true;
                        for ($i = 1; $i < 12; $i++) {
                            if($month == 12){
                                $month = 0;
                                $year++;
                            }

                            $month = $month + 1;
                            /** Verificamos si ya fue pagada la administracion de este apartamento para el mes y año seleccionado */
                            $administracion_pago = AdministracionPago::from('administracion_pagos as ap')
                            ->join('residentes_conjuntos as rc', 'rc.id', '=', 'ap.residente_conjunto_id')
                            ->join('administracion_pagos_months as apm', 'apm.administracion_pago_id', '=', 'ap.id')
                            ->where('apm.year', $year)
                            ->where('apm.month', $month)
                            ->where('ap.residente_conjunto_id', $residencia->residente_conjunto_id)
                            ->select([
                                'ap.*'
                            ])
                            ->first();

                            if($administracion_pago){
                                $calcular_administracion_anual = false;
                                break;
                            }
                        }

                        if($calcular_administracion_anual){
                            /** Calculamos descuento anual */
                            $pago_anual = new stdClass;
                            $total_administracion = ($informacion_pago->valor_administracion * 12);
                            $descuento = ($total_administracion * $informacion_pago->descuento_pago_anual) / 100;
                            $pago_anual->total = $total_administracion - $descuento;
                            $pago_anual->total_descuento = $descuento;
                            $pago_anual->porcentaje_descuento = $informacion_pago->descuento_pago_anual;
                            $pago_anual->month_inicial = $request->month;
                            $pago_anual->month_final = $month;
                            $pago_anual->year_inicial = $request->year;
                            $pago_anual->year_final = $year;
                            $detalles_pago['pago_anual'] = $pago_anual;
                        }
                    }
                }
            }
        }

        return response()->json(compact('detalles_pago'), 201);
    }

    public function save_comprobante_transferencia(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        /** Verificamos si hay algun comprobante en revision para este mes y año seleccionado */
        $administracion_pago = AdministracionPago::from('administracion_pagos as ap')
        ->join('residentes_conjuntos as rc', 'rc.id', '=', 'ap.residente_conjunto_id')
        ->join('administracion_pagos_months as apm', 'apm.administracion_pago_id', '=', 'ap.id')
        ->where('apm.year', $request->year)
        ->where('apm.month', $request->month)
        ->where('ap.residente_conjunto_id', $residencia->residente_conjunto_id)
        ->where('ap.metodo_pago', 1)
        ->where('ap.estatus_pago', 1)
        ->select([
            'ap.*'
        ])
        ->first();

        if($administracion_pago){
            return response()->json('Tienes un comprobante en revisión', 201);
        }

        $data = $request->all();

        if($request->comprobante && is_array($request->comprobante)){
            $file = base64_decode($request->comprobante['fileURL']);
        
            $img_extension = pathinfo($request->comprobante['name'], PATHINFO_EXTENSION);
            $filename = time() . Str::random(5) . '.' . $img_extension;

            if(Storage::disk('public')->put('transferencias/' . $filename, $file)){
                $data['comprobante'] = $filename;
            }
        }

        $data['residente_conjunto_id'] = $residencia->residente_conjunto_id;
        $data['metodo_pago'] = 1;
        $data['estatus_pago'] = 1;
        $administracion_pago = AdministracionPago::create($data);

        $month = $request->month;
        $year = $request->year;
        for ($i = 0; $i < $request->cantidad_meses; $i++) {
            /** Guardamos el registro en la tabla de administracion pagos meses */
            $pago_month = new AdministracionPagoMonth;
            $pago_month->year = $year;
            $pago_month->month = $month;
            $pago_month->administracion_pago_id = $administracion_pago->id;
            $pago_month->save();
            $month++;
            
            if($month == 13){
                $month = 1;
                $year++;
            }
        }

        if($request->cantidad_meses == 1){
            $tipo_notificacion = 6;
        }
        else if($request->cantidad_meses == 6){
            $tipo_notificacion = 7;
        }
        else if($request->cantidad_meses == 12){
            $tipo_notificacion = 8;
        }

        if($residencia->tipo == 'Apartamento'){
            $apartamento = Apartamento::find($residencia->residencia_id);
            /** Guardamos la notificacion para el conjunto residencial */
            NotificacionTrait::store($tipo_notificacion, $this->auth->id, $administracion_pago->id, 'pago?year=' . $request->year . '&bloque=' . $apartamento->bloque . '&apartamento=' . $apartamento->apartamento, $owner_id);
        }
        else{
            $casa = Casa::find($residencia->residencia_id);
            /** Guardamos la notificacion para el conjunto residencial */
            NotificacionTrait::store($tipo_notificacion, $this->auth->id, $administracion_pago->id, 'pago?year=' . $request->year . '&casa=' . $casa->numero, $owner_id);
        }

        /** Obtenemos los tokens del conjunto residencial para notificarle */
        $token = TokenFirebaseUser::from('token_firebase_users as tfu')
        ->join('users as u', 'u.id', '=', 'tfu.user_id')
        ->where('tfu.acceso', 'Web')
        ->where(function($query) use($owner_id){
            return $query->where('u.id', $owner_id)->orWhere('u.owner_id', $owner_id);
        })
        ->pluck('tfu.token')->toArray();

        if(count($token) > 0){
            /** Enviamos la notificación al usuario */
            
            $action = new \stdClass;

            $send_data = [
                
            ];

            FirebasePushTrait::send('Pago de administración - Transferencia', $this->auth->name . ' ha cargado un comprobante de transferencia bancaria. Esperando aprobación.', $send_data, $token);
        }

        return response()->json('Comprobante enviado', 201);
    }

    public function procesar_pago(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $informacion_adicional = ConjuntoInformacion::where('conjunto_id', $owner_id)
        ->select([
            'public_key', 'private_key'
        ])
        ->first();

        if(!$informacion_adicional || !$informacion_adicional->public_key || !$informacion_adicional->private_key){
            return response()->json('No se pudo completar el pago', 200);
        }

        $pago = $request->except([
            'total', 'cantidad_meses', 'valor_administracion',
            'descuento_pronto_pago', 'year', 'month', 'residencia'
        ]);

        $pago['cardExpYear'] = '20' . $pago['cardExpYear'];
        $pago['description'] = 'Pago de administración tuappmin';
        $pago['dues'] = '1';
        $pago['testMode'] = config('app.debug') == 1 ? true : false;
        $pago['urlConfirmation'] = config('app.url_confirmation');
        $pago['currency'] = 'COP';
        $pago['cardNumber'] = str_replace(' ', '', $pago['cardNumber']);
        $pago['value'] = (string)$request->total;
        $pago['extra1'] = 'pago_administracion';

        $object = new stdClass;
        $object->a = (string)$request->cantidad_meses;
        $object->b = (string)$request->valor_administracion;
        $object->c = (string)$request->valor_mora ? $request->valor_mora : null;
        $object->d = (string)$request->descuento_pronto_pago ? $request->descuento_pronto_pago : null;
        $object->e = (string)$request->descuento_pago_semestre ? $request->descuento_pago_semestre : null;
        $object->f = (string)$request->descuento_pago_anual ? $request->descuento_pago_anual : null;
        $object->g = (string)$request->year;
        $object->h = (string)$request->month;
        $pago['extra2'] = json_encode($object);
        $pago['extra3'] = (string)$residencia->residente_conjunto_id;

        if($residencia->tipo == 'Apartamento'){
            $apartamento = Apartamento::find($residencia->residencia_id);
            $url = 'pago?year=' . $request->year . '/bloque=' . $apartamento->bloque . '/apartamento=' . $apartamento->apartamento;
        }
        else{
            $casa = Casa::find($residencia->residencia_id);
            $url = 'pago?year=' . $request->year . '/casa=' . $casa->numero;
        }

        $pago['extra4'] = $url;
        $pago['extra5'] = (string)$this->auth->id;
        $pago['extra6'] = (string)$owner_id;

        $data = Epayco::get_token($informacion_adicional->public_key, $informacion_adicional->private_key);

        if(!$data || isset($data->error)){
            return response()->json(!$data->error ? 'Ocurrio un error. Intentalo mas tarde.' : $data->error, 200);
        }

        /** Guardamos el token */
        $token = $data->token;
        
        /** Procesamos el pago */
        $pago = Epayco::payment_card($token, $pago);

        $pago = json_encode($pago);

        return response()->json(compact('pago'), 201);
    }

    public function procesar_pago_pse(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $informacion_adicional = ConjuntoInformacion::where('conjunto_id', $owner_id)
        ->select([
            'public_key', 'private_key'
        ])
        ->first();

        if(!$informacion_adicional || !$informacion_adicional->public_key || !$informacion_adicional->private_key){
            return response()->json('No se pudo completar el pago', 200);
        }

        $pago = $request->except([
            'total', 'cantidad_meses', 'valor_administracion',
            'descuento_pronto_pago', 'year', 'month', 'residencia'
        ]);

        $pago['description'] = 'Pago de administración tuappmin';
        $pago['dues'] = '1';
        $pago['testMode'] = config('app.debug') == 1 ? true : false;
        $pago['urlConfirmation'] = config('app.url_confirmation');
        $pago['methodConfimation'] = 'POST';
        $pago['urlResponse'] = url('/') . '/pago/response';
        $pago['currency'] = 'COP';
        $pago['value'] = (string)$request->total;
        $pago['extra1'] = 'pago_administracion';

        $object = new stdClass;
        $object->a = (string)$request->cantidad_meses;
        $object->b = (string)$request->valor_administracion;
        $object->c = (string)$request->valor_mora ? $request->valor_mora : null;
        $object->d = (string)$request->descuento_pronto_pago ? $request->descuento_pronto_pago : null;
        $object->e = (string)$request->descuento_pago_semestre ? $request->descuento_pago_semestre : null;
        $object->f = (string)$request->descuento_pago_anual ? $request->descuento_pago_anual : null;
        $object->g = (string)$request->year;
        $object->h = (string)$request->month;
        $pago['extra2'] = json_encode($object);
        $pago['extra3'] = (string)$residencia->residente_conjunto_id;

        if($residencia->tipo == 'Apartamento'){
            $apartamento = Apartamento::find($residencia->residencia_id);
            $url = 'pago?year=' . $request->year . '/bloque=' . $apartamento->bloque . '/apartamento=' . $apartamento->apartamento;
        }
        else{
            $casa = Casa::find($residencia->residencia_id);
            $url = 'pago?year=' . $request->year . '/casa=' . $casa->numero;
        }

        $pago['extra4'] = $url;
        $pago['extra5'] = (string)$this->auth->id;
        $pago['extra6'] = (string)$owner_id;

        $data = Epayco::get_token($informacion_adicional->public_key, $informacion_adicional->private_key);

        if(!$data || isset($data->error)){
            return response()->json(!$data->error ? 'Ocurrio un error. Intentalo mas tarde.' : $data->error, 200);
        }

        /** Guardamos el token */
        $token = $data->token;
        
        /** Procesamos el pago */
        $pago = Epayco::payment_pse($token, $pago);

        $pago = json_encode($pago);

        return response()->json(compact('pago'), 201);
    }
}
