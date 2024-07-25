<?php

namespace App\Http\Controllers\Administracion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\Parking;
use App\Models\User;

/** Expprts */
use App\Exports\ParkingsExport;

class ParkingController extends Controller
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

        return view('pages.administracion.parking.index', compact('conjunto_residencial'));
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

        $parkings = Parking::where('parkings.conjunto_id', $owner_id)
        ->leftJoin('residentes_conjuntos as rc', 'rc.id', 'parkings.residente_conjunto_id')
        ->leftJoin('users as use', 'use.id', '=', 'rc.user_id')
        ->join('users as u', 'u.id', '=', 'parkings.acceso_permitido')
        ->leftJoin('users as us', 'us.id', '=', 'parkings.acceso_salida');

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $parkings->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento, t.nombre_propietario';
        }
        else{
            $parkings->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero, t.nombre_propietario';
        }

        /** Filtramos los datos */
        if($request->numero_parking){
            $parkings->where('parkings.numero_parking', $request->numero_parking);
        }

        if($request->placa){
            $parkings->where('parkings.placa', 'like', '%' . $request->placa . '%');
        }

        if($request->check_show_all == 'false'){
            $parkings->whereNull('parkings.fecha_salida');
        }

        $parkings->select([
            'parkings.*', 't.telefono', 'u.name as usuario_ingreso',
            'us.name as usuario_salida', \DB::raw($select), 'use.name as residente'
        ]);

        $parkings = $parkings->orderBy('parkings.id', 'DESC')->paginate(30);

        return response()->json(compact('parkings'), 201);
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

        $parkings = Parking::where('parkings.conjunto_id', $owner_id)
        ->leftJoin('residentes_conjuntos as rc', 'rc.id', 'parkings.residente_conjunto_id')
        ->leftJoin('users as use', 'use.id', '=', 'rc.user_id')
        ->join('users as u', 'u.id', '=', 'parkings.acceso_permitido')
        ->leftJoin('users as us', 'us.id', '=', 'parkings.acceso_salida');

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $parkings->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento, t.nombre_propietario';
        }
        else{
            $parkings->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero, t.nombre_propietario';
        }

        /** Filtramos los datos */
        if($request->numero_parking){
            $parkings->where('parkings.numero_parking', $request->numero_parking);
        }

        if($request->placa){
            $parkings->where('parkings.placa', 'like', '%' . $request->placa . '%');
        }

        $parkings->select([
            'parkings.numero_parking', \DB::raw($select), 'use.name as residente',
            'parkings.placa', 'parkings.tipo_vehiculo', 'parkings.jornada', 'parkings.fecha_ingreso',
            'parkings.fecha_salida', 'u.name as usuario_ingreso',
            'us.name as usuario_salida', 'parkings.total'
        ]);

        $parkings = $parkings->orderBy('parkings.id', 'DESC')->get();

        $jornadas = [
            'Por hora', 'Diurna', 'Nocturna', 'Completa'
        ];

        foreach ($parkings as $key => $value) {
            $value->tipo_vehiculo = $value->tipo_vehiculo == 1 ? 'Moto' : 'Automóvil';
            $value->jornada = $jornadas[$value->jornada - 1];
        }

        return (new ParkingsExport($conjunto_residencial, $parkings))->download('parqueaderos' . time() . '.xlsx');
    }
}
