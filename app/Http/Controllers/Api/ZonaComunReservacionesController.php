<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\ZonaComunReservacion;
use App\Models\ZonaComunHorario;
use App\Models\ZonaComunImagen;

class ZonaComunReservacionesController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:5');
        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_all_paginate(Request $request)
    {
        //** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $reservaciones = ZonaComunReservacion::from('zonas_comunes_reservaciones as zcr')
        ->join('zonas_comunes_horarios as h', 'h.id', '=', 'zcr.horario_id')
        ->join('zonas_comunes as zc', 'zc.id', '=', 'h.zona_comun_id')
        ->where('zc.conjunto_id', $owner_id)
        ->join('residentes_conjuntos as rc', 'rc.id', '=', 'zcr.residente_conjunto_id')
        ->join('users as u', 'u.id', '=', 'rc.user_id')
        ->join('zonas_comunes_pagos as zcp', 'zcp.reservacion_id', '=', 'zcr.id')
        ->select([
            'zcr.*', 'zc.name', 'u.name as residente', 'zc.id as zona_comun_id',
            'zcp.x_amount'
        ]);

        $reservaciones->orderBy('id', 'DESC');
        $reservaciones = $reservaciones->paginate(15);

        foreach ($reservaciones as $key => $value) {
            /** Obtenemos la primera imagen de la zona común */
            $imagen = null;

            $zona_imagen = ZonaComunImagen::where('zona_comun_id', $value->zona_comun_id)
            ->first();

            if($zona_imagen){
                $imagen = $zona_imagen->imagen;
            }

            $value->image = $imagen;
        }

        return response()->json(compact('reservaciones'), 201);
    }
}
