<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;
use App\Models\Pqrs;
use App\Models\ResidenteConjunto;
use App\Models\TokenFirebaseUser;

/** Trais */
use App\Http\Traits\FirebasePushTrait;

class PqrsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:2,3');
    }

    public function index()
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        return view('pages.administracion.pqrs.index', compact('conjunto_residencial'));
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        $pqrs = Pqrs::from('pqrs')
        ->join('residentes_conjuntos as rc', 'rc.id', 'pqrs.residente_conjunto_id')
        ->join('users as u', 'u.id', '=', 'rc.user_id')
        ->where('rc.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $pqrs->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento';
        }
        else{
            $pqrs->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero';
        }

        /** Filtramos los datos */
        if($request->id){
            $pqrs->where('pqrs.id', $request->id);
        }

        if($request->residente){
            $pqrs->where('u.name', 'like', '%' . $request->residente . '%');
        }

        if($request->tipo){
            $pqrs->where('pqrs.tipo', $request->tipo);
        }

        $pqrs->select([
            'pqrs.*', \DB::raw($select), 'u.name as residente',
        ])
        ->orderByRaw("pqrs.estatus ASC, pqrs.id DESC");

        $pqrs = $pqrs->paginate(15);

        return response()->json(compact('pqrs'), 201);
    }

    public function finalizar($id)
    {
        $pqrs = Pqrs::find($id);
        $pqrs->estatus = 2;
        $pqrs->save();

        /** Obtenemos los datos de la relacion residente conjunto para saber el id del usuario */
        $residente_conjunto = ResidenteConjunto::from('residentes_conjuntos')
        ->join('users as conjunto', 'conjunto.id', '=', 'residentes_conjuntos.conjunto_id')
        ->where('residentes_conjuntos.id', $pqrs->residente_conjunto_id)
        ->select([
            'residentes_conjuntos.user_id', 'conjunto.name as conjunto'
        ])
        ->first();

        /** Obtenemos los tokens del residente que envio la PQRS */
        $token = TokenFirebaseUser::from('token_firebase_users as tfu')
        ->where('tfu.user_id', $residente_conjunto->user_id)
        ->pluck('tfu.token')->toArray();

        /** Enviamos la notificacion push al usuario */
        if(count($token) > 0){
            /** Enviamos la notificación al usuario */
            
            $action = new \stdClass;
            $action->stack = 'StackPQRS';
            $action->screen = 'PQRS';

            $send_data = [
                'tipo' => 'mensaje_pqrs',
                'accion' => $action
            ];

            FirebasePushTrait::send('PQRS finalizada', 'Se ha finalizado una PQRS', $send_data, $token);
        }

        return response()->json('PQRS finalizada', 201);
    }
}
