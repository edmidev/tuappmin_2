<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\Minuta;

class MinutaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:2,3');
    }

    public function index()
    {
        return view('pages.administracion.minuta.index');
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $minutas = Minuta::from('minutas as m')
        ->join('users as u', 'u.id', '=', 'm.user_id')
        ->where('m.conjunto_id', $owner_id);

        if($request->fecha_inicio){
            $minutas->whereDate('m.created_at', '>=', $request->fecha_inicio);
        }

        if($request->fecha_fin){
            $minutas->whereDate('m.created_at', '<=', $request->fecha_fin);
        }

        $minutas->select([
            'm.*', 'u.name as portero'
        ])
        ->orderBy('m.id', 'DESC');
        $minutas = $minutas->paginate(15);

        return response()->json(compact('minutas'), 201);
    }
}
