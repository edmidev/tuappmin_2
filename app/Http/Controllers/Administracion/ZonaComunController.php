<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Models */
use App\Models\ZonaComun;
use App\Models\ZonaComunImagen;
use App\Models\ZonaComunHorario;

class ZonaComunController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:2,3');
    }

    public function index()
    {
        return view('pages.administracion.zona_comun.index');
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

        $zonas = ZonaComun::where('conjunto_id', $owner_id)
        ->orderBy('id', 'ASC')
        ->get();

        return response()->json(compact('zonas'), 201);
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

        $zonas = ZonaComun::where('conjunto_id', $owner_id)
        ->with(['imagenes', 'horarios']);

        /** Filtramos por nombre */
        if($request->name){
            $zonas->where('name', 'like', '%' . $request->name . '%');
        }

        $zonas->orderBy('id', 'ASC');
        $zonas = $zonas->paginate(30);

        return response()->json(compact('zonas'), 201);
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

        /** Creamos la zona común */
        $zona = new ZonaComun;
        $zona->name = $request->name;
        $zona->descripcion = $request->descripcion;
        $zona->conjunto_id = $owner_id;
        $zona->save();

        /** Guardamos las imagenes de la zona */
        for ($i = 0; $i < $request->num_files; $i++) {
            $file = $request->file('imagen_' . $i);
            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('zonas_comunes/' . $filename, file_get_contents($file));

            $imagen = new ZonaComunImagen;
            $imagen->imagen = $filename;
            $imagen->zona_comun_id = $zona->id;
            $imagen->save();
        }

        /** Creamos los horarios */
        $horarios = json_decode($request->horarios);
        foreach ($horarios as $key => $value) {
            $horario = new ZonaComunHorario;
            $horario->hora_inicial = $value->hora_inicial;
            $horario->hora_final = $value->hora_final;
            $horario->valor = $value->valor;
            $horario->zona_comun_id = $zona->id;
            $horario->save();
        }

        return response()->json('Zona guardada', 201);
    }

    public function update($id, Request $request)
    {
        /** Creamos la zona común */
        $zona = ZonaComun::find($id);
        $zona->name = $request->name;
        $zona->descripcion = $request->descripcion;
        $zona->save();

        /** Eliminamos las imagenes de la zona */
        foreach (json_decode($request->imagenes_eliminadas) as $key => $value) {
            $zona_imagen = ZonaComunImagen::find($value);
            if(Storage::disk('public')->exists('zonas_comunes/' . $zona_imagen->imagen))
                Storage::disk('public')->delete('zonas_comunes/' . $zona_imagen->imagen);

            $zona_imagen->delete();
        }

        /** Guardamos las imagenes de la zona */
        for ($i = 0; $i < $request->num_files; $i++) {
            $file = $request->file('imagen_' . $i);
            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('zonas_comunes/' . $filename, file_get_contents($file));

            $imagen = new ZonaComunImagen;
            $imagen->imagen = $filename;
            $imagen->zona_comun_id = $zona->id;
            $imagen->save();
        }

        /** Eliminamos los horarios del array horarios eliminados */
        $horarios_eliminados = json_decode($request->horarios_eliminados);
        foreach ($horarios_eliminados as $key => $value) {
            ZonaComunHorario::find($value)->delete();
        }

        /** Iteramos el array de horarios */
        $horarios = json_decode($request->horarios);
        foreach ($horarios as $key => $value) {
            /** Verificamos si tiene un id */
            if(isset($value->id) && $value->id){
                $horario = ZonaComunHorario::find($value->id);
            }
            else{
                $horario = new ZonaComunHorario;
                $horario->zona_comun_id = $zona->id;
            }
            
            $horario->hora_inicial = $value->hora_inicial;
            $horario->hora_final = $value->hora_final;
            $horario->valor = $value->valor;
            $horario->save();
        }

        return response()->json('Zona modificada', 201);
    }

    public function destroy($id)
    {
        $zona = ZonaComun::find($id);
        $zona->delete();

        return response()->json('Zona eliminada', 201);
    }

    public function get_horarios_disponibles(Request $request)
    {
        /** Verificamos los horarios disponibles para el conjunto */
        $horarios = ZonaComunHorario::from('zonas_comunes_horarios as zch')
        ->where('zona_comun_id', $request->zona_comun_id)
        ->where('zch.status', 1)
        ->whereNotExists(function($query) use($request){
            return $query->from('zonas_comunes_reservaciones as zcr')->whereRaw('zcr.horario_id = zch.id')
            ->whereDate('fecha_inicio', $request->fecha);
        })
        ->get();

        return response()->json(compact('horarios'), 201);
    }
}
