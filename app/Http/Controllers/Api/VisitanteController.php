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
use App\Models\Visitante;
use App\Models\TokenFirebaseUser;
use App\Models\User;
use App\Models\ResidenteConjunto;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Http\Traits\NotificacionTrait;
use App\Models\TipoNotificacion;

class VisitanteController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:2,3,4')->only([
            'store', 'reportar_salida', 'count'
        ]);

        $this->middleware('permisosRolesApi:5')->only([
            'autorizar_ingreso'
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

        $visitantes = Visitante::from('visitantes as v')
        ->leftJoin('residentes_conjuntos as rc', 'rc.id', 'v.residente_conjunto_id')
        ->leftJoin('users as u', 'u.id', '=', 'rc.user_id')
        ->leftJoin('users as portero_entrada', 'portero_entrada.id', '=', 'v.acceso_ingreso')
        ->leftJoin('users as portero_salida', 'portero_salida.id', '=', 'v.acceso_salida')
        ->where('v.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $visitantes->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento, t.nombre_propietario';
        }
        else{
            $visitantes->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero, t.nombre_propietario';
        }

        if($this->auth->rol_id == 5){
            if($conjunto_residencial->tipo == 'Apartamento'){
                $visitantes->where('rc.apartamento_id', $residencia->residencia_id);
            }
            else{
                $visitantes->where('rc.casa_id', $residencia->residencia_id);
            }

            $visitantes->where('rc.user_id', $this->auth->id);
        }

        /** Filtramos los datos */
        if($request->name){
            $visitantes->where('v.name', 'LIKE', '%' . $request->name . '%');
        }

        if($request->numero_documento){
            $visitantes->where('v.numero_documento', $request->numero_documento);
        }

        /*if($request->check_show_all != 'true'){
            $visitantes->where(function($query){
                return $query->whereNull('v.fecha_salida')
                ->orWhere('v.estatus_acceso', 1);
            });
        }*/

        $visitantes->select([
            'v.*', \DB::raw($select), 'u.name as residente', 'portero_entrada.name as portero_entrada',
            'portero_salida.name as portero_salida', 'u.telefono', 'u.id as user_id'
        ])
        ->orderBy('v.id', 'DESC');

        $visitantes = $visitantes->paginate(15);

        return response()->json(compact('visitantes'), 201);
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

        $visitantes = Visitante::where('conjunto_id', $owner_id)
        ->whereNull('acceso_salida')
        ->where('estatus_acceso', 3)
        ->count();

        return response()->json(compact('visitantes'), 201);
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

        $residente = null;
        if($request->solicitar_acceso){
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

        /** Verificamos que no existe una persona con el número de identificacion dentro del conjunto */
        $verficar_visitante = Visitante::where('numero_documento', $request->numero_documento)
        ->where('conjunto_id', $owner_id)
        ->whereNull('fecha_salida')
        ->where('estatus_acceso', 3)
        ->first();

        if($verficar_visitante){
            return response()->json('Este número de documento coincide con el de una persona dentro del conjunto.', 200);
        }

        $visitante = new Visitante;
        if($request->foto && $request->foto_name){
            $img = substr($request->foto, strpos($request->foto, ',') + 1);            
            $img = base64_decode($img);
            
            $img_extension = pathinfo($request->foto_name, PATHINFO_EXTENSION);
            $filename = time() . Str::random(5) . '.' . $img_extension;

            Storage::disk('public')->put('fotos_visitantes/' . $filename, $img);
            $visitante->imagen = $filename;
        }
        
        $visitante->name = $request->name;
        $visitante->numero_documento = $request->numero_documento;
        $visitante->fecha_nacimiento = $request->fecha_nacimiento;
        $visitante->rh = $request->rh;
        $visitante->sexo = $request->sexo;
        $visitante->observacion = $request->observacion;
        $visitante->tipo = $request->tipo;
        $visitante->acceso_ingreso = $this->auth->id;
        $visitante->conjunto_id = $owner_id;

        if($request->solicitar_acceso){
            $visitante->residente_conjunto_id = $residente->id;
            /** Si se solicita el acceso guardamos el estatus como Esperando confirmación */
            $visitante->estatus_acceso = 1;
            $visitante->save();

            // Guardamos notificacion en la base de datos
            $notificationType = TipoNotificacion::where('class', 'app')->where('icon', 'visitantes')->value('id');
            NotificacionTrait::store($notificationType, $this->auth->id, $visitante->id,'Solicitud de autorización a visitante', $residente->user_id, 'Tienes una solicitud de autorización a un visitante');

            /** Obtenemos los tokens del usuario que se cuentra en esta residencia */
            $tokens = TokenFirebaseUser::where('user_id', $residente->user_id)
            ->pluck('token')->toArray();

            if(count($tokens) > 0){
                /** Enviamos la notificación al usuario */
                $action = new \stdClass;
                $action->stack = 'StackVisitantesPortero';
                $action->screen = 'VisitantesPortero';

                $send_data = [
                    'tipo' => 'nuevo_visitante',
                    'accion' => $action
                ];

                FirebasePushTrait::send('Solicitud de autorización a visitante', 'Tienes una solicitud de autorización a un visitante', $send_data, $tokens);
            }
        }
        else{
            /** Si no se solicita el acceso guardamos el estatus como Permitido */
            $visitante->estatus_acceso = 3;
            $visitante->fecha_ingreso = date('Y-m-d H:i:s');
            $visitante->save();
        }

        return response()->json('Visitante registrado', 201);
    }

    public function reportar_salida($id)
    {
        $visitante = Visitante::find($id);
        $visitante->fecha_salida = date('Y-m-d H:i:s');
        $visitante->acceso_salida = $this->auth->id;
        $visitante->save();

        return response()->json('Salida reportada', 201);
    }

    public function autorizar_ingreso($id)
    {
        $visitante = Visitante::find($id);
        $visitante->fecha_ingreso = date('Y-m-d H:i:s');
        $visitante->estatus_acceso = 3;
        $visitante->fecha_change_estatus = date('Y-m-d H:i:s');
        $visitante->save();

        // Guardamos notificacion en la base de datos
        $notificationType = TipoNotificacion::where('class', 'app')->where('icon', 'visitantes')->value('id');
        NotificacionTrait::store($notificationType, $this->auth->id, 1,'Visitante autorizado', $visitante->acceso_ingreso, 'El residente ' . $this->auth->name . ' ha autorizado al visitante ' . $visitante->name);

        /** Obtenemos los tokens del portero o usuario que registro este visitante */
        $tokens = TokenFirebaseUser::where('user_id', $visitante->acceso_ingreso)
        ->pluck('token')->toArray();

        if(count($tokens) > 0){
            /** Enviamos la notificación al usuario */
            $action = new \stdClass;
            $action->stack = 'StackVisitantesPortero';
            $action->screen = 'VisitantesPortero';

            $send_data = [
                'tipo' => 'nuevo_visitante',
                'accion' => $action
            ];

            FirebasePushTrait::send('Visitante autorizado', 'El residente ' . $this->auth->name . ' ha autorizado a un visitante', $send_data, $tokens);
        }

        return response()->json('Visitante autorizado', 201);
    }

    public function denegar_ingreso($id)
    {
        $visitante = Visitante::find($id);
        $visitante->estatus_acceso = 2;
        $visitante->fecha_change_estatus = date('Y-m-d H:i:s');
        $visitante->save();

        // Guardamos notificacion en la base de datos
        $notificationType = TipoNotificacion::where('class', 'app')->where('icon', 'visitantes')->value('id');
        NotificacionTrait::store($notificationType, $this->auth->id, 1,'Visitante denegado', $visitante->acceso_ingreso, 'El residente ' . $this->auth->name . ' ha denegado al visitante ' . $visitante->name);

        /** Obtenemos los tokens del portero o usuario que registro este visitante */
        $tokens = TokenFirebaseUser::where('user_id', $visitante->acceso_ingreso)
        ->pluck('token')->toArray();

        if(count($tokens) > 0){
            /** Enviamos la notificación al usuario */
            $action = new \stdClass;
            $action->stack = 'StackVisitantesPortero';
            $action->screen = 'VisitantesPortero';

            $send_data = [
                'tipo' => 'nuevo_visitante',
                'accion' => $action
            ];

            FirebasePushTrait::send('Visitante denegado', 'El residente ' . $this->auth->name . ' ha denegado a un visitante', $send_data, $tokens);
        }

        return response()->json('Visitante denegado', 201);
    }

    public function notificar(Request $request)
    {
        if($request->user_id){
            // Guardamos notificacion en la base de datos
            $notificationType = TipoNotificacion::where('class', 'app')->where('icon', 'visitantes')->value('id');
            NotificacionTrait::store($notificationType, $this->auth->id, 1,'Solicitud de autorización a visitante ', $request->user_id, 'Tienes una solicitud de autorización a un visitante');
        }

        /** Obtenemos los tokens del usuario que se cuentra en esta residencia */
        $tokens = TokenFirebaseUser::where('user_id', $request->user_id)
        ->pluck('token')->toArray();

        if(count($tokens) > 0){
            /** Enviamos la notificación al usuario */
            $action = new \stdClass;
            $action->stack = 'StackVisitantesPortero';
            $action->screen = 'VisitantesPortero';

            $send_data = [
                'tipo' => 'nuevo_visitante',
                'accion' => $action
            ];

            FirebasePushTrait::send('Solicitud de autorización a visitante', 'Tienes una solicitud de autorización a un visitante', $send_data, $tokens);
        }

        return response()->json('Notificación enviada', 201);
    }

    public function show_numero_documento(Request $request)
    {
        $visitante = Visitante::where('numero_documento', $request->numero_documento)
        ->orderBy('id', 'DESC')
        ->first();

        if($visitante){
            return response()->json(compact('visitante'), 201);
        }
        else{
            return response()->json(compact('visitante'), 200);
        }
    }
}
