<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;
use App\Models\ResidenteConjunto;
use App\Models\Parking;
use App\Models\AdministracionPago;
use stdClass;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:1')->only([
            'count_conjuntos_residenciales', 'get_conjuntos_anuales'
        ]);
    }

    public function index()
    {
        $data = new stdClass;
        $data->year_actual = date('Y');
        $data->month_actual = date('m');

        return view('dashboard', compact('data'));
    }

    public function count_conjuntos_residenciales(Request $request)
    {
        $cantidad_conjuntos_residenciales = User::where('rol_id', 2)
        ->where('status', 'Activo')
        ->where('tipo', $request->tipo)
        ->count();

        return response()->json(compact('cantidad_conjuntos_residenciales'), 201);
    }

    public function count_residentes()
    {
        $cantidad_residentes = ResidenteConjunto::join('users as u', 'u.id', '=', 'residentes_conjuntos.conjunto_id')
        ->where('u.rol_id', 2);

        /** Si el usuario authenticado no es superadministrador
         * filtramos solo los conjuntos propios
         */

        if(Auth::user()->rol_id != 1){
            if(Auth::user()->rol_id == 2){
                $owner_id = Auth::id();
            }
            else{
                $owner_id = Auth::user()->owner_id;
            }

            $cantidad_residentes->where('residentes_conjuntos.conjunto_id', $owner_id);
        }

        $cantidad_residentes = $cantidad_residentes->count();

        return response()->json(compact('cantidad_residentes'), 201);
    }

    public function get_conjuntos_anuales()
    {
        $data = User::where('rol_id', 2)
        ->select([
            \DB::raw("COUNT(id) as cantidad_registros"),
            \DB::raw('YEAR(created_at) year, MONTH(created_at) month'),
        ])
        ->orderByRaw('year ASC, month ASC')
        ->groupby('year','month')
        ->get();

        return response()->json(compact('data'), 201);
    }

    public function get_residentes_anuales()
    {
        $data = \DB::table('residentes_conjuntos')
        ->join('users as u', 'u.id', '=', 'residentes_conjuntos.conjunto_id')
        ->where('u.rol_id', 2);

        /** Si el usuario authenticado no es superadministrador
         * filtramos solo los conjuntos propios
         */

        if(Auth::user()->rol_id != 1){
            if(Auth::user()->rol_id == 2){
                $owner_id = Auth::id();
            }
            else{
                $owner_id = Auth::user()->owner_id;
            }

            $data->where('residentes_conjuntos.conjunto_id', $owner_id);
        }

        $data->select([
            \DB::raw("COUNT(residentes_conjuntos.id) as cantidad_registros"),
            \DB::raw('YEAR(residentes_conjuntos.created_at) year, MONTH(residentes_conjuntos.created_at) month'),
        ])
        ->orderByRaw('year ASC, month ASC')
        ->groupby('year','month');

        $data = $data->get();

        return response()->json(compact('data'), 201);
    }

    public function get_ingresos_parkings(Request $request)
    {
        /** Obtenemos el conjunto propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $data = Parking::where('conjunto_id', $owner_id)
        ->whereNotNull('fecha_salida')
        ->whereYear('fecha_salida', $request->year)
        ->select([
            \DB::raw('sum(total) as total'),
            \DB::raw("DATE_FORMAT(fecha_salida,'%m') as month")
        ])
        ->groupBy('month')
        ->orderBy('fecha_salida', 'ASC')
        ->get();

        return response()->json(compact('data'), 201);
    }

    public function get_ingresos_administracion(Request $request)
    {
        /** Obtenemos el conjunto propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $data = AdministracionPago::from('administracion_pagos as ap')
        ->join('residentes_conjuntos as rc', 'rc.id', '=', 'ap.residente_conjunto_id')
        ->whereYear('ap.created_at', $request->year)
        ->where('rc.conjunto_id', $owner_id)
        ->select([
            \DB::raw('sum(ap.total) as total'),
            \DB::raw("DATE_FORMAT(ap.created_at,'%m') as month")
        ])
        ->groupBy('month')
        ->orderBy('ap.created_at', 'ASC')
        ->get();

        return response()->json(compact('data'), 201);
    }
}
