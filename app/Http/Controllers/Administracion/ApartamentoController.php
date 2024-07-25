<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\Apartamento;
use App\Models\ResidenteConjunto;

class ApartamentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('apartamento');
        $this->middleware('permisosRoles:2,3');
    }

    public function index()
    {
        return view('pages.administracion.apartamento.index');
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

        $apartamentos = Apartamento::where('conjunto_id', $owner_id);

        /** Filtramos por bloque o apartamento */
        if($request->name){
            $apartamentos->where('bloque', $request->name)
            ->orWhere('apartamento', $request->name);
        }
        
        $apartamentos->orderBy('id', 'ASC');
        $apartamentos = $apartamentos->paginate(30);

        return response()->json(compact('apartamentos'), 201);
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

        $apartamentos = Apartamento::where('conjunto_id', $owner_id);

        /** filtramos por bloque */
        if($request->bloque){
            $apartamentos->where('bloque', $request->bloque);
        }

        $apartamentos->orderBy('id', 'ASC');
        $apartamentos = $apartamentos->get();

        return response()->json(compact('apartamentos'), 201);
    }

    public function get_all_group_by_bloques()
    {
        /** Obtenemos propietario principal */
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $apartamentos = Apartamento::where('conjunto_id', $owner_id)
        ->select('bloque')
        ->groupBy('bloque')
        ->get();

        foreach ($apartamentos as $key => $value) {
            $value->apartamentos = Apartamento::where('bloque', $value->bloque)
            ->where('conjunto_id', $owner_id)
            ->get();
        }

        return response()->json(compact('apartamentos'), 201);
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

        $apartamento = Apartamento::where('bloque', $request->bloque)
        ->where('apartamento', $request->apartamento)
        ->where('conjunto_id', $owner_id)
        ->first();

        if($apartamento){
            return response()->json('Ya existen este apartamento en este bloque', 200);
        }

        $apartamento = new Apartamento;
        $apartamento->bloque = $request->bloque;
        $apartamento->apartamento = $request->apartamento;
        $apartamento->nombre_propietario = $request->nombre_propietario;
        $apartamento->telefono = $request->telefono;
        $apartamento->conjunto_id = $owner_id;
        $apartamento->save();

        return response()->json('Apartamento guardado', 201);
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

        $apartamento = Apartamento::find($id);
        if($apartamento->conjunto_id == $owner_id){
            $apartamento->nombre_propietario = $request->nombre_propietario;
            $apartamento->telefono = $request->telefono;
            $apartamento->save();
        }

        return response()->json('Apartamento modificado', 201);
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

        $residente_conjunto = ResidenteConjunto::where('apartamento_id', $id)
        ->where('conjunto_id', $owner_id)
        ->first();

        if($residente_conjunto){
            return response()->json('Existe un residente en este apartamento', 200);
        }

        $apartamento = Apartamento::find($id);
        if($apartamento->conjunto_id != $owner_id){
            return response()->json('No puedes modificar esta casa', 200);
        }

        $apartamento->delete();

        return response()->json('Apartamento eliminado', 201);
    }
}
