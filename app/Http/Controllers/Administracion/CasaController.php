<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\Casa;
use App\Models\ResidenteConjunto;

class CasaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('casa');
        $this->middleware('permisosRoles:2,3');
    }

    public function index()
    {
        return view('pages.administracion.casa.index');
    }

    public function get_all_paginate(Request $request)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $casas = Casa::where('conjunto_id', $owner_id);

        /** Filtramos por número de casa */
        if($request->name){
            $casas->where('numero', $request->name);
        }

        $casas->orderBy('id', 'ASC');
        $casas = $casas->paginate(30);

        return response()->json(compact('casas'), 201);
    }

    public function get_all(Request $request)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $casas = Casa::where('conjunto_id', $owner_id)
        ->orderBy('id', 'ASC')
        ->get();

        return response()->json(compact('casas'), 201);
    }

    public function store(Request $request)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $casa = Casa::where('numero', $request->numero)
        ->where('conjunto_id', $owner_id)
        ->first();

        if($casa){
            return response()->json('Ya existen este número de casa', 200);
        }

        $casa = new Casa;
        $casa->numero = $request->numero;
        $casa->nombre_propietario = $request->nombre_propietario;
        $casa->telefono = $request->telefono;
        $casa->conjunto_id = $owner_id;
        $casa->save();

        return response()->json('Casa guardado', 201);
    }

    public function update($id, Request $request)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $casa = Casa::find($id);
        if($casa->conjunto_id == $owner_id){
            $casa->nombre_propietario = $request->nombre_propietario;
            $casa->telefono = $request->telefono;
            $casa->save();
        }

        return response()->json('Casa modificada', 201);
    }

    public function destroy($id)
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $residente_conjunto = ResidenteConjunto::where('casa_id', $id)
        ->where('conjunto_id', $owner_id)
        ->first();

        if($residente_conjunto){
            return response()->json('Existe un residente en esta casa', 200);
        }
        
        $casa = Casa::find($id);
        if($casa->conjunto_id != $owner_id){
            return response()->json('No puedes modificar esta casa', 200);
        }

        $casa->delete();

        return response()->json('Casa eliminada', 201);
    }
}
