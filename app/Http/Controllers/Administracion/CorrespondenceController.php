<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;
use App\Models\Correspondence;

/** Expprts */
use App\Exports\CorrespondencesExport;

class CorrespondenceController extends Controller
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

        return view('pages.administracion.correspondence.index', compact('conjunto_residencial'));
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

        $correspondences = Correspondence::from('correspondences as c')
        ->join('residentes_conjuntos as rc', 'rc.id', 'c.residente_conjunto_id')
        ->join('users as u', 'u.id', '=', 'rc.user_id')
        ->leftJoin('public_services as ps', 'ps.id', '=', 'c.public_service_id')
        ->join('users as us', 'us.id', '=', 'c.acceso_ingreso')
        ->leftJoin('users as use', 'use.id', '=', 'c.acceso_salida')
        ->where('rc.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $correspondences->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento';
        }
        else{
            $correspondences->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero';
        }

        /** Filtramos los datos */
        if($request->correspondence_type){
            $correspondences->where('correspondence_type', $request->correspondence_type);
        }

        if($request->residente){
            $correspondences->where('u.name', 'like', '%' . $request->residente . '%');
        }

        $correspondences->select([
            'c.*', \DB::raw($select), 'u.name as residente', 'ps.image',
            'ps.name as public_service_name', 'us.name as usuario_ingreso',
            'use.name as usuario_salida'
        ])
        ->orderByRaw("c.estatus ASC, c.id DESC");

        $correspondences = $correspondences->paginate(15);

        return response()->json(compact('correspondences'), 201);
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

        $correspondences = Correspondence::from('correspondences as c')
        ->join('residentes_conjuntos as rc', 'rc.id', 'c.residente_conjunto_id')
        ->join('users as u', 'u.id', '=', 'rc.user_id')
        ->leftJoin('public_services as ps', 'ps.id', '=', 'c.public_service_id')
        ->join('users as us', 'us.id', '=', 'c.acceso_ingreso')
        ->leftJoin('users as use', 'use.id', '=', 'c.acceso_salida')
        ->where('rc.conjunto_id', $owner_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $correspondences->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento';
        }
        else{
            $correspondences->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero';
        }

        /** Filtramos los datos */
        if($request->correspondence_type){
            $correspondences->where('correspondence_type', $request->correspondence_type);
        }

        if($request->residente){
            $correspondences->where('u.name', 'like', '%' . $request->residente . '%');
        }

        $correspondences->select([
            \DB::raw($select), 'c.correspondence_type', 'ps.name as public_service_name', 
            'u.name as residente', 'c.imagen', 'c.observacion', 'c.created_at as created_at_aux', 'c.fecha_entregado',
            'us.name as usuario_ingreso', 'use.name as usuario_salida', 'c.estatus'
        ])
        ->orderBy('c.id', 'DESC');

        $correspondences = $correspondences->get();

        foreach ($correspondences as $key => $value) {
            switch ($value->correspondence_type) {
                case 1:
                    $correspondence_type = 'Servicios públicos';
                    break;
                
                case 2:
                    $correspondence_type = 'Correspondencia';
                    break;

                case 3:
                    $correspondence_type = 'Paquetería';
                    break;
            }

            switch ($value->estatus) {
                case 1:
                    $estatus = 'En porteria';
                    break;
                
                case 2:
                    $estatus = 'Entregado';
                    break;
            }

            $value->created_at_aux = date("Y-m-d H:i:s", strtotime($value->created_at_aux));
            $value->correspondence_type = $correspondence_type;
            $value->imagen = $value->imagen ? asset('storage/fotos_paquetes/' . $value->imagen) : null;
            $value->estatus = $estatus;
        }

        return (new CorrespondencesExport($conjunto_residencial, $correspondences))->download('correspondencias' . time() . '.xlsx');
    }
}
