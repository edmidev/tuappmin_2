<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\ConjuntoInformacionPago;
use App\Models\AdministracionPago;
use App\Models\TokenFirebaseUser;
use App\Models\User;
use stdClass;

/** Trais */
use App\Http\Traits\FirebasePushTrait;

class PagoAdministracionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:2,3');
    }

    public  function index()
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        /** Obtenemos la informacion del conjunto residencial */
        $conjunto_residencial = User::find($owner_id);
        /** Obtenemos el año en que fue registrado el conjunto residencial */
        $year_init = (int)date('Y', strtotime($conjunto_residencial->created_at));

        $year_actual = date('Y');
        $years = [];
        for ($i = $year_init; $i < $year_actual + 3; $i++) { 
            array_push($years, $i);
        }

        return view('pages.administracion.pago.index', compact('conjunto_residencial', 'years', 'year_actual'));
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        /** Obtenemos la informacion del conjunto residencial */
        $conjunto_residencial = User::find($owner_id);

        /** Obtenemos la informacion del conjunto año seleccionado */
        $informacion_pago = ConjuntoInformacionPago::where('conjunto_id', $owner_id)
        ->where('year', $request->year)
        ->first();

        if(!$informacion_pago){
            $administraciones_pagos = [];

            return response()->json('No se encontro información', 200);
        }

        /** Obtenemos los residentes que ha estado en dicho apartamento */
        $residentes = \DB::table('residentes_conjuntos as rc');

        if($conjunto_residencial->tipo == 'Apartamento'){
            $residentes->where('apartamento_id', $request->apartamento);
        }
        else{
            $residentes->where('casa_id', $request->casa);
        }

        $residentes->join('users as u', 'u.id', '=', 'rc.user_id')
        ->where('conjunto_id', $owner_id)
        ->select([
            'rc.*', 'u.name'
        ]);
        
        $residentes = $residentes->paginate(30);

        foreach ($residentes as $key => $value) {
            /** Obtenemos los pagos de la administracion para el año seleccionado */
            $administraciones_pagos = AdministracionPago::from('administracion_pagos as ap')
            ->join('residentes_conjuntos as rc', 'rc.id', '=', 'ap.residente_conjunto_id')
            ->join('administracion_pagos_months as apm', 'apm.administracion_pago_id', '=', 'ap.id')
            ->where('apm.year', $request->year)
            ->where('ap.residente_conjunto_id', $value->id)
            ->select([
                'apm.month', 'ap.estatus_pago', 'apm.id'
            ])
            ->where('ap.estatus_pago', '!=', 2)
            ->orderBy('apm.month', 'ASC')
            ->get();

            /** Obtenemos la fecha de ingreso del residente */
            $fecha_ingreso = date('Y-m-d', strtotime($value->created_at));

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
                        $administraciones_pagos[$j]->id = $administraciones_pagos[$j]->id;
                        $administraciones_pagos[$j]->correspondido = true;
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
                            $object->mora = true;
                            $object->correspondido = true;
                            array_push($data, $object);
                        }
                        else{
                            $object = new stdClass;
                            $object->month = $i;
                            $object->pagado = false;
                            $object->correspondido = true;
                            array_push($data, $object);
                        }
                    }
                    else{
                        $object = new stdClass;
                        $object->month = $i;
                        $object->pagado = false;
                        $object->correspondido = false;
                        array_push($data, $object);
                    }
                }
            }

            $value->administraciones_pagos = $data;
        }

        return response()->json(compact('residentes'), 201);
    }

    public function detalles_pago($pago_month_id)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        /** Verificamos si ya fue pagada la administracion de este apartamento para el mes y año seleccionado */
        $administracion_pago = AdministracionPago::from('administracion_pagos as ap')
        ->join('residentes_conjuntos as rc', 'rc.id', '=', 'ap.residente_conjunto_id')
        ->join('administracion_pagos_months as apm', 'apm.administracion_pago_id', '=', 'ap.id')
        ->leftJoin('administracion_pagos_epayco as ape', 'ape.administracion_pago_id', '=', 'ap.id')
        ->where('apm.id', $pago_month_id)
        ->where('rc.conjunto_id', $owner_id)
        ->select([
            'ap.*', 'ape.x_ref_payco', 'ape.x_cardnumber', 'apm.month', 'apm.year',
        ])
        ->first();

        return response()->json(compact('administracion_pago'), 201);
    }

    public function aprobar_pago(Request $request)
    {
        $administracion_pago = AdministracionPago::find($request->pago_month_id);
        $administracion_pago->estatus_pago = 3;
        $administracion_pago->change_estatus_user_id = Auth::id();
        $administracion_pago->save();

        /** Obtenemos los tokens del residente que pago la administracion */
        $token = TokenFirebaseUser::from('token_firebase_users as tfu')
        ->join('residentes_conjuntos as rc', 'rc.user_id', '=', 'tfu.user_id')
        ->where('rc.id', $administracion_pago->residente_conjunto_id)
        ->pluck('tfu.token')->toArray();

        /** Enviamos la notificacion push al usuario */
        if(count($token) > 0){
            /** Enviamos la notificación al usuario */
            
            $action = new \stdClass;
            $action->stack = 'StackAdministracion';
            $action->screen = 'Administracion';

            $send_data = [
                'tipo' => 'pago_administracion',
                'accion' => $action
            ];

            FirebasePushTrait::send('Pago aprobado', 'Se ha aprobado un comprobante de pago.', $send_data, $token);
        }

        return response()->json('Pago aprobado', 201);
    }

    public function rechazar_pago(Request $request)
    {
        $administracion_pago = AdministracionPago::find($request->pago_month_id);
        $administracion_pago->estatus_pago = 2;
        $administracion_pago->change_estatus_user_id = Auth::id();
        $administracion_pago->save();

        /** Obtenemos los tokens del residente que pago la administracion */
        $token = TokenFirebaseUser::from('token_firebase_users as tfu')
        ->join('residentes_conjuntos as rc', 'rc.user_id', '=', 'tfu.user_id')
        ->where('rc.id', $administracion_pago->residente_conjunto_id)
        ->pluck('tfu.token')->toArray();

        /** Enviamos la notificacion push al usuario */
        if(count($token) > 0){
            /** Enviamos la notificación al usuario */
            
            $action = new \stdClass;
            $action->stack = 'StackAdministracion';
            $action->screen = 'Administracion';

            $send_data = [
                'tipo' => 'pago_administracion',
                'accion' => $action
            ];

            FirebasePushTrait::send('Pago rechazado', 'Se ha rechazado un comprobante de pago.', $send_data, $token);
        }

        return response()->json('Pago rechazado', 201);
    }
}
