<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;
use App\Models\Visitante;

/** Expprts */
use App\Exports\VisitantesExport;

class VisitanteController extends Controller
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

        return view('pages.administracion.visitante.index', compact('conjunto_residencial'));
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

        /** Filtramos los datos */
        if($request->name){
            $visitantes->where('v.name', 'LIKE', '%' . $request->name . '%');
        }

        if($request->numero_documento){
            $visitantes->where('v.numero_documento', 'LIKE', '%' . $request->numero_documento . '%');
        }

        if($request->tipo){
            $visitantes->where('v.tipo', $request->tipo);
        }

        if($request->residente){
            $visitantes->where('u.name', 'LIKE', '%' . $request->residente . '%');
        }

        if($request->fecha_inicio){
            $visitantes->whereDate('v.created_at', '>=', $request->fecha_inicio);
        }

        if($request->fecha_fin){
            $visitantes->whereDate('v.created_at', '<=', $request->fecha_fin);
        }

        $visitantes->select([
            'v.*', \DB::raw($select), 'u.name as residente', 'portero_entrada.name as portero_entrada',
            'portero_salida.name as portero_salida'
        ])
        ->orderBy('v.id', 'DESC');

        $visitantes = $visitantes->paginate(15);

        return response()->json(compact('visitantes'), 201);
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

        $visitantes = Visitante::from('visitantes as v')
        ->leftJoin('residentes_conjuntos as rc', 'rc.id', 'v.residente_conjunto_id')
        ->leftJoin('users as u', 'u.id', '=', 'rc.user_id')
        ->leftJoin('users as portero_entrada', 'portero_entrada.id', '=', 'v.acceso_ingreso')
        ->leftJoin('users as portero_salida', 'portero_salida.id', '=', 'v.acceso_salida')
        ->where('v.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $visitantes->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento';
        }
        else{
            $visitantes->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero';
        }

        /** Filtramos los datos */
        if($request->name){
            $visitantes->where('v.name', 'LIKE', '%' . $request->name . '%');
        }

        if($request->numero_documento){
            $visitantes->where('v.numero_documento', 'LIKE', '%' . $request->numero_documento . '%');
        }

        if($request->tipo){
            $visitantes->where('v.tipo', $request->tipo);
        }

        if($request->residente){
            $visitantes->where('u.name', 'LIKE', '%' . $request->residente . '%');
        }

        if($request->fecha_inicio){
            $visitantes->whereDate('v.created_at', '>=', $request->fecha_inicio);
        }

        if($request->fecha_fin){
            $visitantes->whereDate('v.created_at', '<=', $request->fecha_fin);
        }

        $visitantes->select([
            \DB::raw($select), 'u.name as residente', 'v.tipo', 'v.imagen', 'v.name as visitante', 'v.numero_documento',
            'v.fecha_nacimiento', 'v.rh', 'v.sexo', 'v.fecha_ingreso', 'v.fecha_salida',
            'portero_entrada.name as portero_entrada', 'portero_salida.name as portero_salida',
            'v.estatus_acceso'
        ])
        ->orderBy('v.id', 'DESC');

        $visitantes = $visitantes->get();

        foreach ($visitantes as $key => $value) {
            switch ($value->tipo) {
                case 1:
                    $tipo = 'Visitante';
                    break;
                
                case 2:
                    $tipo = 'Domiciliario';
                    break;

                case 3:
                    $tipo = 'Técnico';
                    break;
            }

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

            $value->tipo = $tipo;
            $value->imagen = $value->imagen ? asset('storage/fotos_visitantes/' . $value->imagen) : null;
            $value->estatus_acceso = $estatus_acceso;
            $value->sexo = $value->sexo == 'M' ? 'Hombre' : 'Mujer';
        }

        return (new VisitantesExport($conjunto_residencial, $visitantes))->download('visitantes' . time() . '.xlsx');

        return response()->json(compact('visitantes'), 201);
    }
}
