<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;
use App\Models\Citofonia;

/** Expprts */
use App\Exports\CitofoniasExport;

class CitofoniaController extends Controller
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

        return view('pages.administracion.citofonia.index', compact('conjunto_residencial'));
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

        $citofonias = Citofonia::from('citofonias as c')
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

        /** Filtramos la informacion */
        if($request->residente){
            $citofonias->where('u.name', 'LIKE', '%' . $request->residente . '%');
        }

        $citofonias->select([
            'c.*', \DB::raw($select), 'u.name as residente', 'portero_entrada.name as portero_entrada',
            'portero_entrada.rol_id as rol_id_entrada'
        ])
        ->orderBy('c.id', 'DESC');

        $citofonias = $citofonias->paginate(15);

        return response()->json(compact('citofonias'), 201);
    }

    public function export(Request $request)
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

        $citofonias = Citofonia::from('citofonias as c')
        ->leftJoin('residentes_conjuntos as rc', 'rc.id', 'c.residente_conjunto_id')
        ->leftJoin('users as u', 'u.id', '=', 'rc.user_id')
        ->leftJoin('users as portero_entrada', 'portero_entrada.id', '=', 'c.acceso_ingreso')
        ->where('c.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $citofonias->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento';
        }
        else{
            $citofonias->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero';
        }

        /** Filtramos la informacion */
        if($request->residente){
            $citofonias->where('u.name', 'LIKE', '%' . $request->residente . '%');
        }

        $citofonias->select([
            \DB::raw($select), 'u.name as residente', 'c.motivo', 'c.fecha_ingreso',
            'portero_entrada.name as portero_entrada', 'c.estatus_acceso'
        ])
        ->orderBy('c.id', 'DESC');

        $citofonias = $citofonias->get();

        foreach ($citofonias as $key => $value) {
            switch ($value->estatus_acceso) {
                case 1:
                    $estatus_acceso = 'Esperando autorización';
                    break;
                
                case 2:
                    $estatus_acceso = 'Denegado';
                    break;

                case 3:
                    $estatus_acceso = 'Permitido';
                    break;
            }

            $value->estatus_acceso = $estatus_acceso;
        }

        return (new CitofoniasExport($conjunto_residencial, $citofonias))->download('citofonias' . time() . '.xlsx');
    }
}
