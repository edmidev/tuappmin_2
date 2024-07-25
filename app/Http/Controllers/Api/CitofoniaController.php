<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\Citofonia;
use App\Models\TokenFirebaseUser;
use App\Models\User;
use App\Models\ResidenteConjunto;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Models\PhoneUser;

class CitofoniaController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:2,3,4')->only([
            'reportar_salida', 'count'
        ]);

        $this->middleware('permisosRolesApi:2,3,4,5')->only([
            'autorizar_ingreso', 'denegar_ingreso'
        ]);

        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        $residencia = null;
        if($this->auth->rol_id != 5){
            if($this->auth->rol_id == 2){
                $owner_id = $this->auth->id;
            }
            else{
                $owner_id = $this->auth->owner_id;
            }
        }
        else{
            $residencia = json_decode($request->residencia);
            $owner_id = $residencia->conjunto_id;
        }

        $conjunto_residencial = User::find($owner_id);

        $citofonias = Citofonia::with('conjunto')->from('citofonias as c')
        ->leftJoin('residentes_conjuntos as rc', 'rc.id', 'c.residente_conjunto_id')
        ->leftJoin('users as u', 'u.id', '=', 'rc.user_id')
        ->leftJoin('users as portero_entrada', 'portero_entrada.id', '=', 'c.acceso_ingreso')
        ->where('c.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $citofonias->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento, t.nombre_propietario';
        }
        else{
            $citofonias->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero, t.nombre_propietario';
        }

        if($this->auth->rol_id == 5){
            if($conjunto_residencial->tipo == 'Apartamento'){
                $citofonias->where('rc.apartamento_id', $residencia->residencia_id);
            }
            else{
                $citofonias->where('rc.casa_id', $residencia->residencia_id);
            }

            $citofonias->where('rc.user_id', $this->auth->id);
        }

        $citofonias->select([
            'c.*', \DB::raw($select), 'u.name as residente', 'portero_entrada.name as portero_entrada',
            'u.telefono', 'u.id as user_id', 'portero_entrada.rol_id as rol_id_entrada', 'u.masked_phone as masked_phone'
        ])
        ->orderBy('c.id', 'DESC');

        $citofonias = $citofonias->paginate(15);

        foreach ($citofonias as $citofonia) {
            $citofonia->phones = PhoneUser::where('user_id', $citofonia->user_id)->get();
        }

        return response()->json(compact('citofonias'), 201);
    }

    public function count()
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $citofonias = Citofonia::where('conjunto_id', $owner_id)
        ->whereNull('acceso_salida')
        ->where('estatus_acceso', 3)
        ->count();

        return response()->json(compact('citofonias'), 201);
    }

    public function store(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        $residencia = null;
        if($this->auth->rol_id != 5){
            if($this->auth->rol_id == 2){
                $owner_id = $this->auth->id;
            }
            else{
                $owner_id = $this->auth->owner_id;
            }
        }
        else{
            $residencia = json_decode($request->residencia);
            $owner_id = $residencia->conjunto_id;
        }

        $conjunto_residencial = User::find($owner_id);

        $residente = null;
        if($this->auth->rol_id != 5){
            /** Verificamos si existe algun residente en esta residencia*/
            $residente = ResidenteConjunto::where('conjunto_id', $owner_id);
            if($conjunto_residencial->tipo == 'Apartamento'){
                $residente->where('apartamento_id', $request->apartamento_id);
            }
            else{
                $residente->where('casa_id', $request->casa_id);
            }

            $residente = $residente->orderBy('id', 'DESC')->first();

            if(!$residente){
                return response()->json('No existe ningún residente a notificar', 200);
            }
        }
        else{
            /** Verificamos si existe algun residente en esta residencia*/
            $residente = ResidenteConjunto::where('conjunto_id', $owner_id);
            if($conjunto_residencial->tipo == 'Apartamento'){
                $residente->where('apartamento_id', $residencia->residencia_id);
            }
            else{
                $residente->where('casa_id', $residencia->residencia_id);
            }

            $residente = $residente->orderBy('id', 'DESC')->first();
        }

        $citofonia = new Citofonia;
        $citofonia->motivo = $request->motivo;
        $citofonia->residente_conjunto_id = $residente->id;
        /** Si se solicita el acceso guardamos el estatus como Esperando confirmación */
        if($this->auth->rol_id != 5){
            $citofonia->estatus_acceso = 1;

            /** Obtenemos los tokens del usuario que se cuentra en esta residencia */
            $tokens = TokenFirebaseUser::where('user_id', $residente->user_id)
            ->pluck('token')->toArray();

            if(count($tokens) > 0){
                /** Enviamos la notificación al usuario */
                $action = new \stdClass;
                $action->stack = 'StackCitofonia';
                $action->screen = 'Citofonias';

                $send_data = [
                    'tipo' => 'nuevo_visitante',
                    'accion' => $action
                ];

                FirebasePushTrait::send('Citofonia: Solicitud de autorización', 'Tienes una solicitud de autorización.', $send_data, $tokens);
            }
        }
        else{
            $citofonia->fecha_ingreso = date('Y-m-d H:i:s');
            $citofonia->estatus_acceso = 3;
            $citofonia->fecha_change_estatus = date('Y-m-d H:i:s');

            /** Obtenemos los tokens de los porteros de este conjunto */
            $tokens = TokenFirebaseUser::from('token_firebase_users as tfu')
            ->join('users as u', 'u.id', '=', 'tfu.user_id')
            ->where('owner_id', $owner_id)
            ->where('rol_id', 4)
            ->pluck('token')->toArray();

            if(count($tokens) > 0){
                /** Enviamos la notificación al usuario */
                $action = new \stdClass;
                $action->stack = 'StackCitofonia';
                $action->screen = 'Citofonias';

                $send_data = [
                    'tipo' => 'nuevo_visitante',
                    'accion' => $action
                ];

                FirebasePushTrait::send('Citofonia: Anuncio de residente', 'El residente ' . $this->auth->name . ' ha hecho un anuncio', $send_data, $tokens);
            }
        }

        $citofonia->acceso_ingreso = $this->auth->id;
        $citofonia->conjunto_id = $owner_id;
        $citofonia->save();

        return response()->json('Acceso registrado', 201);
    }

    public function autorizar_ingreso($id)
    {
        $citofonia = Citofonia::find($id);
        $citofonia->fecha_ingreso = date('Y-m-d H:i:s');
        $citofonia->estatus_acceso = 3;
        $citofonia->change_estatus_user_id = $this->auth->id;
        $citofonia->fecha_change_estatus = date('Y-m-d H:i:s');
        $citofonia->save();

        if($this->auth->rol_id == 5){
            /** Obtenemos los tokens del portero o usuario que registro este citofonia */
            $tokens = TokenFirebaseUser::where('user_id', $citofonia->acceso_ingreso)
            ->pluck('token')->toArray();

            if(count($tokens) > 0){
                /** Enviamos la notificación al usuario */
                $action = new \stdClass;
                $action->stack = 'StackCitofonia';
                $action->screen = 'Citofonias';

                $send_data = [
                    'tipo' => 'nuevo_visitante',
                    'accion' => $action
                ];

                FirebasePushTrait::send('Citofonia: Acceso autorizado', 'El residente ' . $this->auth->name . ' ha autorizado un acceso', $send_data, $tokens);
            }
        }

        return response()->json('Acceso autorizado', 201);
    }

    public function denegar_ingreso($id)
    {
        $citofonia = Citofonia::find($id);
        $citofonia->estatus_acceso = 2;
        $citofonia->change_estatus_user_id = $this->auth->id;
        $citofonia->fecha_change_estatus = date('Y-m-d H:i:s');
        $citofonia->save();

        if($this->auth->rol_id == 5){
            /** Obtenemos los tokens del portero o usuario que registro este citofonia */
            $tokens = TokenFirebaseUser::where('user_id', $citofonia->acceso_ingreso)
            ->pluck('token')->toArray();

            if(count($tokens) > 0){
                /** Enviamos la notificación al usuario */
                $action = new \stdClass;
                $action->stack = 'StackCitofonia';
                $action->screen = 'Citofonias';

                $send_data = [
                    'tipo' => 'nuevo_visitante',
                    'accion' => $action
                ];

                FirebasePushTrait::send('Citofonia: Acceso denegado', 'El residente ' . $this->auth->name . ' ha denegado un acceso', $send_data, $tokens);
            }
        }

        return response()->json('Acceso denegado', 201);
    }

    public function notificar(Request $request)
    {
        if($this->auth->rol_id != 5){
            /** Obtenemos los tokens del usuario que se cuentra en esta residencia */
            $tokens = TokenFirebaseUser::where('user_id', $request->user_id)
            ->pluck('token')->toArray();

            if(count($tokens) > 0){
                /** Enviamos la notificación al usuario */
                $action = new \stdClass;
                $action->stack = 'StackCitofonia';
                $action->screen = 'Citofonias';

                $send_data = [
                    'tipo' => 'nuevo_visitante',
                    'accion' => $action
                ];

                FirebasePushTrait::send('Citofonia: Solicitud de autorización', 'Tienes una solicitud de autorización.', $send_data, $tokens);
            }
        }
        else{
            $residencia = json_decode($request->residencia);
            $owner_id = $residencia->conjunto_id;

            /** Obtenemos los tokens de los porteros de este conjunto */
            $tokens = TokenFirebaseUser::from('token_firebase_users as tfu')
            ->join('users as u', 'u.id', '=', 'tfu.user_id')
            ->where('owner_id', $owner_id)
            ->where('rol_id', 4)
            ->pluck('token')->toArray();

            if(count($tokens) > 0){
                /** Enviamos la notificación al usuario */
                $action = new \stdClass;
                $action->stack = 'StackCitofonia';
                $action->screen = 'Citofonias';

                $send_data = [
                    'tipo' => 'nuevo_visitante',
                    'accion' => $action
                ];

                FirebasePushTrait::send('Citofonia: Anuncio de residente', 'El residente ' . $this->auth->name . ' ha hecho un anuncio', $send_data, $tokens);
            }
        }

        return response()->json('Notificación enviada', 201);
    }

    public function upload_data(Request $request)
    {
        $data = [];
        if($request->data){
            $data = json_decode($request->data);
        }

        foreach ($data as $key => $value) {
            $citofonia = new Citofonia;
            $citofonia->motivo = $value->motivo;
            $citofonia->fecha_ingreso = $value->fecha_ingreso ? $value->fecha_ingreso : null;
            $citofonia->residente_conjunto_id = $value->residente_conjunto_id;
            $citofonia->acceso_ingreso = $value->acceso_ingreso;
            $citofonia->estatus_acceso = $value->estatus_acceso;
            $citofonia->change_estatus_user_id = $value->change_estatus_user_id ? $value->change_estatus_user_id : null;
            $citofonia->fecha_change_estatus = $value->fecha_change_estatus ? $value->fecha_change_estatus : null;
            $citofonia->conjunto_id = $value->conjunto_id;
            $citofonia->save();
        }

        return response()->json('Registros guardados', 201);
    }
}
