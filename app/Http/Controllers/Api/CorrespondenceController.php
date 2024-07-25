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
use App\Models\Correspondence;
use App\Models\ResidenteConjunto;
use App\Models\TokenFirebaseUser;
use App\Models\User;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Http\Traits\NotificacionTrait;
use App\Models\TipoNotificacion;

class CorrespondenceController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:2,3,4')->only('store', 'correspondencia_entregada');
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

        $correspondences = Correspondence::from('correspondences as c')
        ->join('residentes_conjuntos as rc', 'rc.id', 'c.residente_conjunto_id')
        ->join('users as u', 'u.id', '=', 'rc.user_id')
        ->leftJoin('public_services as ps', 'ps.id', '=', 'c.public_service_id')
        ->where('rc.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $correspondences->join('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento';
        }
        else{
            $correspondences->join('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero';
        }

        if($this->auth->rol_id == 5){
            if($conjunto_residencial->tipo == 'Apartamento'){
                $correspondences->where('rc.apartamento_id', $residencia->residencia_id);
            }
            else{
                $correspondences->where('rc.casa_id', $residencia->residencia_id);
            }

            $correspondences->where('rc.user_id', $this->auth->id);
        }

        /** Filtramos los datos */
        if($request->correspondence_type){
            $correspondences->where('correspondence_type', $request->correspondence_type);
        }

        $correspondences->select([
            'c.*', \DB::raw($select), 'u.name as residente', 'ps.image'
        ])
        ->orderByRaw("c.estatus ASC, c.id DESC");

        $correspondences = $correspondences->paginate(15);

        return response()->json(compact('correspondences'), 201);
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

        /** Obtenemos los residentes de este conjunto */
        $residentes = ResidenteConjunto::where('conjunto_id', $owner_id);

        /** Si solo se envia a un departamento o casa en especifico */
        if($request->send_to_all == false){
            if($conjunto_residencial->tipo == 'Apartamento'){
                $residentes->where('apartamento_id', $request->apartamento_id);
            }else{
                $residentes->where('casa_id', $request->casa_id);
            }
        }
        
        $residentes = $residentes->get();
        $guardado = false;

        $filename = null;
        if($request->correspondence_type == 3){
            if($request->foto && $request->foto_name){
                $img = substr($request->foto, strpos($request->foto, ',') + 1);            
                $img = base64_decode($img);
                
                $img_extension = pathinfo($request->foto_name, PATHINFO_EXTENSION);
                $filename = time() . Str::random(5) . '.' . $img_extension;
    
                Storage::disk('public')->put('fotos_paquetes/' . $filename, $img);
            }
        }

        /** Iteramos los resindentes para guadar cada registro */
        foreach ($residentes as $key => $value) {
            if(!$guardado){
                $guardado = true;
            }

            $correspondence = new Correspondence;
            $correspondence->correspondence_type = $request->correspondence_type;

            /** Si el tipo de correspondencia es de servicio publico */
            if($correspondence->correspondence_type == 1){
                $correspondence->public_service_id = $request->public_service_id;
            }
            else if($request->correspondence_type == 3){
                $correspondence->imagen = $filename;
                $correspondence->observacion = $request->observacion;
            }

            $correspondence->residente_conjunto_id = $value->id;
            $correspondence->acceso_ingreso = $this->auth->id;
            $correspondence->save();
        }

        /** Obtenemos los tokens de los usuarios a enviar la notificacion */
        $tokens = TokenFirebaseUser::whereIn('user_id', $residentes->pluck('user_id'))
        ->pluck('token')->toArray();

        // Guardamos notificacion en la base de datos
        $notificationType = TipoNotificacion::where('class', 'app')->where('icon', 'correspondencia')->value('id');
        foreach ($residentes->pluck('user_id') as $residendId) {
            NotificacionTrait::store($notificationType, $this->auth->id, 1,'Nueva Correspondencia', $residendId, 'Tienes una nueva correspondencia en porteria');
        }
        if(count($tokens) > 0){
            /** Enviamos la notificación al usuario */
            $action = new \stdClass;
            $action->stack = 'StackCorrespondencias';
            $action->screen = 'Correspondencias';

            $send_data = [
                'tipo' => 'nueva_correspondencia',
                'accion' => $action
            ];

            FirebasePushTrait::send('Nueva correspondencia', 'Tienes una nueva correspondencia en porteria', $send_data, $tokens);
        }

        if($guardado){
            return response()->json('Correspondencia guardada', 201);
        }
        else{
            return response()->json('No se encontro ningun residente', 200);
        }
    }

    public function marcar_entregada(Request $request)
    {
        $correspondence = Correspondence::find($request->correspondence_id);

        if($correspondence->estatus == '2'){
            return response()->json('Correspondencia entregada', 201);
        }

        $correspondence->estatus = '2';
        $correspondence->acceso_salida = $this->auth->id;
        $correspondence->fecha_entregado = date('Y-m-d H:i:s');
        $correspondence->save();

        return response()->json('Correspondencia entregada', 201);
    }
}
