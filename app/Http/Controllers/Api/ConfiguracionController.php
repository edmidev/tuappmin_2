<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\ConjuntoInformacion;
use App\Models\ResidenteConjunto;

class ConfiguracionController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->auth = JWTAuth::parseToken()->authenticate();   
    }

    public function get_informacion_conjunto(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        $residencia = null;
        if($this->auth->rol_id != 5){
            if($this->auth->rol_id == 2){
                $owner_id = $this->auth->id;
            }
            else{
                $owner_id = $this->auth->owner_id;
            }
        }
        else{
            $residencia = json_decode($request->residencia);
            $owner_id = $residencia->conjunto_id;
        }

        $informacion = ConjuntoInformacion::where('conjunto_id', $owner_id)->first();

        return response()->json(compact('informacion'), 201);
    }

    public function get_residentes_conjuntos()
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $residentes_conjuntos = ResidenteConjunto::where('conjunto_id', $owner_id)
        ->join('users as u', 'u.id', '=', 'residentes_conjuntos.user_id')
        ->select([
            'residentes_conjuntos.id', 'residentes_conjuntos.apartamento_id', 'residentes_conjuntos.casa_id', 'u.telefono',
            'u.name as residente'
        ])
        ->get();

        return response()->json(compact('residentes_conjuntos'), 201);
    }
}
