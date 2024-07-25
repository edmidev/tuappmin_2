<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\ResidenteConjunto;
use App\Models\TokenFirebaseUser;
use App\Models\Comunicado;
use App\Models\User;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Http\Traits\NotificacionTrait;

class ComunicadoController extends Controller
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
        $conjunto_residencial = User::find($owner_id);

        /** Obtenemos el id de la relacion residentes conjuntos */
        $residente = ResidenteConjunto::where('conjunto_id', $owner_id);
        if($conjunto_residencial->tipo == 'Apartamento'){
            $residente->where('apartamento_id', $residencia->residencia_id);
        }
        else{
            $residente->where('casa_id', $residencia->residencia_id);
        }

        $residente = $residente->orderBy('id', 'DESC')->first();

        $comunicados = Comunicado::from('comunicados as c')
        ->where('c.conjunto_id', $owner_id)
        ->select([
            'c.*'
        ])
        ->orderBy('c.id', 'DESC');

        $comunicados = $comunicados->paginate(15);

        return response()->json(compact('comunicados'), 201);
    }
}
