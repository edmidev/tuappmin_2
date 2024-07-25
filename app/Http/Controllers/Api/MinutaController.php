<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\Minuta;

class MinutaController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:4')->only([
            'store', 'get_all_paginate'
        ]);

        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_all_paginate(Request $request)
    {
        $minutas = Minuta::where('user_id', $this->auth->id)
        ->orderBy('id', 'DESC')
        ->paginate(15);

        return response()->json(compact('minutas'), 201);
    }

    public function store(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if($this->auth->rol_id == 2){
            $owner_id = $this->auth->id;
        }
        else{
            $owner_id = $this->auth->owner_id;
        }

        $minuta = new minuta;

        /** Guardamos el audio en caso de que se haya enviado */
        if(!is_null($request->audioBase64)){
            $file = base64_decode($request->audioBase64['base64File']);
        
            $file_extencion = pathinfo($request->audioBase64['name'], PATHINFO_EXTENSION);
            $filename = $this->auth->id . time() . Str::random(5) . '.' . $file_extencion;

            if(Storage::disk('public')->put('/minuta/audios/' . $filename, $file)){
                $minuta->audio = $filename;
            }
        }

        $minuta->descripcion = $request->descripcion;

        if($request->foto && is_array($request->foto)){
            $img = substr($request->foto['imageURL'], strpos($request->foto['imageURL'], ',') + 1);            
            $img = base64_decode($img);
            
            $img_extension = pathinfo($request->foto['name'], PATHINFO_EXTENSION);
            $filename = time() . Str::random(5) . '.' . $img_extension;

            if(Storage::disk('public')->put('minuta/fotos/' . $filename, $img)){
                $minuta->foto = $filename;
            }
        }

        $minuta->user_id = $this->auth->id;
        $minuta->conjunto_id = $owner_id;
        $minuta->save();

        return response()->json('Minuta guardada', 201);
    }
}
