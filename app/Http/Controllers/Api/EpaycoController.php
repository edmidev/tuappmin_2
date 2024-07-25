<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** Models */
use App\Models\ConjuntoInformacion;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Trais */
use App\Http\Traits\Epayco;

class EpaycoController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:5');

        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_banks(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $informacion_adicional = ConjuntoInformacion::where('conjunto_id', $owner_id)
        ->select([
            'public_key', 'private_key'
        ])
        ->first();

        $data = Epayco::get_token($informacion_adicional->public_key, $informacion_adicional->private_key);

        if(!$data || isset($data->error)){
            return response()->json('Ocurrio un error. Intentalo mas tarde.', 200);
        }

        /** Guardamos el token */
        $token = $data->token;
        
        /** Obtenemos los bancos */
        $response = Epayco::get_banks($token);

        $response = json_decode(json_encode($response));

        $banks = [];
        foreach ($response->data as $key => $value) {
            if($key > 0){
                $banco = new \stdClass;
                $banco->id = $value->bankCode;
                $banco->name = $value->bankName;

                array_push($banks, $banco);
            }
        }

        return response()->json(compact('banks'), 201);
    }
}
