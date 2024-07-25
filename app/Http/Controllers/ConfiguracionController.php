<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\ConjuntoInformacion;
use App\Models\ConjuntoInformacionPago;
use App\Models\User;

class ConfiguracionController extends Controller
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

        $informacion = ConjuntoInformacion::where('conjunto_id', $owner_id)->first();
        $informacion_pagos = ConjuntoInformacionPago::where('conjunto_id', $owner_id)->get();

        $conjunto_residencial = User::find($owner_id);
        /** Obtenemos el año en que fue registrado el conjunto residencial */
        $year_init = (int)date('Y', strtotime($conjunto_residencial->created_at));

        $year_actual = date('Y');
        $years = [];
        for ($i = $year_init; $i < $year_actual + 3; $i++) { 
            array_push($years, $i);
        }

        return view('pages.configuracion.index', compact('informacion', 'informacion_pagos', 'years', 'year_actual'));
    }

    public function save(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $informacion = ConjuntoInformacion::where('conjunto_id', $owner_id)->first();

        $infomacion_request = $request->except([
            'year', 'valor_administracion', 'limite_pago', 'intereses_mora',
            'descuento_pronto_pago', 'limite_pronto_pago', 'descuento_pago_semestre',
            'descuento_pago_anual',
        ]);

        /** Si no existe el registto en la tabla conjuntos informaciones, creamos un nuevo registro */
        if(!$informacion){
            $infomacion_request['conjunto_id'] = $owner_id;
            ConjuntoInformacion::create($infomacion_request);
        }
        else{
            /** Si ya existe un registro para este conjunto actualizamos la información */
            $informacion->update($infomacion_request);
        }

        $informacion_pago = ConjuntoInformacionPago::where('conjunto_id', $owner_id)
        ->where('year', $request->year)
        ->first();

        $informacion_pago_request = $request->only([
            'year', 'valor_administracion', 'limite_pago', 'interes_mora',
            'descuento_pronto_pago', 'limite_pronto_pago', 'descuento_pago_semestre',
            'descuento_pago_anual',
        ]);

        if(!$informacion_pago){
            $informacion_pago_request['conjunto_id'] = $owner_id;
            ConjuntoInformacionPago::create($informacion_pago_request);
        }
        else{
            $informacion_pago->update($informacion_pago_request);
        }

        return redirect()->back()->with('success', 'Datos guardados correctamente.');
    }
}
