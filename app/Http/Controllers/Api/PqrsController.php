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
use App\Models\Pqrs;
use App\Models\ResidenteConjunto;
use App\Models\TokenFirebaseUser;
use App\Models\User;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Http\Traits\NotificacionTrait;

class PqrsController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:5');
        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_all_paginate(Request $request)
    {
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $pqrs = Pqrs::from('pqrs')
        ->join('residentes_conjuntos as rc', 'rc.id', 'pqrs.residente_conjunto_id')
        ->join('users as u', 'u.id', '=', 'rc.user_id')
        ->where('rc.conjunto_id', $owner_id)
        ->where('pqrs.residente_conjunto_id', $residencia->residente_conjunto_id)
        ->select([
            'pqrs.*'
        ])
        ->orderBy('pqrs.id', 'DESC');

        $pqrs = $pqrs->paginate(15);

        return response()->json(compact('pqrs'), 201);
    }

    public function store(Request $request)
    {
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $filename = null;
        if($request->foto && $request->foto_name){
            $img = substr($request->foto, strpos($request->foto, ',') + 1);            
            $img = base64_decode($img);
            
            $img_extension = pathinfo($request->foto_name, PATHINFO_EXTENSION);
            $filename = time() . Str::random(5) . '.' . $img_extension;

            Storage::disk('public')->put('fotos_pqrs/' . $filename, $img);
        }

        $pqrs = new Pqrs;
        $pqrs->tipo = $request->tipo;
        $pqrs->image = $filename;
        $pqrs->motivo = $request->motivo;
        $pqrs->residente_conjunto_id = $residencia->residente_conjunto_id;
        $pqrs->save();

        /** Obtenemos los administradores del conjunto residencial */
        $users = User::where('owner_id', $owner_id)
        ->where('rol_id', 3)->pluck('id')->toArray();

        /** Agregamos el id del conjunto al array de usuarios para que tambien se le notifique */
        array_push($users, $owner_id);
        
        /** Guardamos la notificacion para el conjunto residencial */
        NotificacionTrait::store(1, $this->auth->id, $pqrs->id, 'pqrs?id=' . $pqrs->id, $owner_id);

        /** Obtenemos los tokens de los usuarios a enviar la notificacion */
        $tokens = TokenFirebaseUser::whereIn('user_id', $users)
        ->where('acceso', 'Web')->pluck('token')->toArray();

        if(count($tokens) > 0){
            /** Enviamos la notificación al usuario */

            $send_data = [
                
            ];

            FirebasePushTrait::send('Nueva PQRS', $this->auth->name . ' ha creado una nueva PQRS', $send_data, $tokens);
        }

        return response()->json('PQRS enviada', 201);
    }

    public function destroy($id)
    {
        $pqrs = Pqrs::find($id);

        if($pqrs->estatus == 2){
            $data['message'] = 'Esta PQRS ya no puede ser eliminada';

            return response()->json(compact('data'), 200);
        }

        $pqrs->delete();

        $data['message'] = 'PQRS eliminada';
        
        return response()->json(compact('data'), 201);
    }
}
