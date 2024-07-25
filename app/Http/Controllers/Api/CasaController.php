<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\Casa;
use App\Models\ResidenteConjunto;

class CasaController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->auth = JWTAuth::parseToken()->authenticate();   
    }

    public function get_all()
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $casas = Casa::where('conjunto_id', $owner_id)->get();

        foreach ($casas as $key => $value) {
            $value->residente = ResidenteConjunto::where('casa_id', $value->id)
            ->join('users as u', 'u.id', '=', 'residentes_conjuntos.user_id')
            ->orderBy('residentes_conjuntos.id', 'DESC')
            ->select([
                'u.name'
            ])
            ->first();
        }

        return response()->json(compact('casas'), 201);
    }
}
