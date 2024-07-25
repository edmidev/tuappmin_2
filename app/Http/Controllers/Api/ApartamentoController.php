<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\Apartamento;
use App\Models\ResidenteInformacion;
use App\Models\ResidenteConjunto;

class ApartamentoController extends Controller
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

        $apartamentos = Apartamento::where('conjunto_id', $owner_id)->get();

        return response()->json(compact('apartamentos'), 201);
    }

    public function get_all_group_by_bloques()
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $apartamentos = Apartamento::where('conjunto_id', $owner_id)
        ->select('bloque')
        ->groupBy('bloque')
        ->get();

        foreach ($apartamentos as $key => $value) {
            $value->apartamentos = Apartamento::where('bloque', $value->bloque)
            ->where('conjunto_id', $owner_id)
            ->get();

            foreach ($value->apartamentos as $key => $v) {
                $v->residente = ResidenteConjunto::where('apartamento_id', $v->id)
                ->join('users as u', 'u.id', '=', 'residentes_conjuntos.user_id')
                ->orderBy('residentes_conjuntos.id', 'DESC')
                ->select([
                    'u.name'
                ])
                ->first();
            }
        }

        return response()->json(compact('apartamentos'), 201);
    }
}
