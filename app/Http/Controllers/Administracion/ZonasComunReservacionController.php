<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\ZonaComunReservacion;
use App\Models\ZonaComunPago;
use App\Models\User;

class ZonasComunReservacionController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:2,3');
    }

    public function index()
    {
        return view('pages.administracion.zona_comun.reservacion.index');
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        $reservaciones = ZonaComunReservacion::from('zonas_comunes_reservaciones as zcr')
        ->join('zonas_comunes_horarios as h', 'h.id', '=', 'zcr.horario_id')
        ->join('zonas_comunes as zc', 'zc.id', '=', 'h.zona_comun_id')
        ->where('zc.conjunto_id', $owner_id)
        ->join('residentes_conjuntos as rc', 'rc.id', '=', 'zcr.residente_conjunto_id')
        ->join('users as u', 'u.id', '=', 'rc.user_id')
        ->join('zonas_comunes_pagos as zcp', 'zcp.reservacion_id', '=', 'zcr.id')
        ->select([
            'zcr.*', 'zc.name', 'u.name as residente', 'zcp.x_amount'
        ]);

        /** Filtramos por nombre */
        if($request->name){
            $reservaciones->where('u.name', 'like', '%' . $request->name . '%');
        }

        $reservaciones->orderBy('id', 'DESC');
        $reservaciones = $reservaciones->paginate(30);

        return response()->json(compact('reservaciones'), 201);
    }

    public function store(Request $request)
    {
        /** Verificamos que el horario de la zona no este reservado */
        $reservacion = ZonaComunReservacion::where('horario_id', $request->horario_id)
        ->whereDate('fecha_inicio', $request->fecha)
        ->first();

        if($reservacion){
            return response()->json('La zona común ya esta reservada para esta fecha.', 200);
        }

        $reservacion = new ZonaComunReservacion;
        $reservacion->created_by_id = Auth::id();
        $reservacion->horario_id = $request->horario_id;
        $reservacion->fecha_inicio = $request->fecha . ' ' . $request->hora_inicial;
        $reservacion->fecha_fin = $request->fecha . ' ' . $request->hora_final;
        $reservacion->residente_conjunto_id = $request->residente_conjunto_id;
        $reservacion->save();
        
        $pago = new ZonaComunPago;
        $pago->reservacion_id = $reservacion->id;
        $pago->x_amount = $request->total;
        $pago->x_respuesta = 'Aceptada';
        $pago->x_test_request = 'FALSE';
        $pago->save();

        return response()->json('Reservación guardada.', 201);
    }
}
