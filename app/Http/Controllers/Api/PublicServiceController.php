<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\PublicService;

class PublicServiceController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->auth = JWTAuth::parseToken()->authenticate();   
    }

    public function get_all()
    {
        /** Obtenemos los servicios publicos del apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $services = PublicService::where('conjunto_id', $owner_id)->get();

        return response()->json(compact('services'), 201);
    }
}
