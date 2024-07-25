<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\PublicService;

class PublicServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:2,3');
    }

    public  function index()
    {
        return view('pages.public_service.index');
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

        $services = PublicService::from('public_services as s')
        ->where('conjunto_id', $owner_id);

        /** Filtramos por nombre */
        if($request->name){
            $services->where('s.name', 'like', '%' . $request->name . '%');
        }        

        $services->select([
            's.*'
        ])
        ->orderBy('s.id', 'DESC');

        $services = $services->paginate(30);

        return response()->json(compact('services'), 201);
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

        $service = new PublicService;
        if($request->file('image')){            
            $foto = $request->file('image');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('public_services/' . $filename, file_get_contents($foto));

            $service->image = $filename;
        }

        $service->name = $request->name;
        $service->description = $request->description;
        $service->conjunto_id = $owner_id;
        $service->save();

        return response()->json('Servicio creada correctamente.', 201);
    }
    
    public function update(Request $request, $id)
    {
        $service = PublicService::find($id);
        if($request->file('image')){
            if(Storage::disk('public')->exists('public_services/' . $service->image))
                Storage::disk('public')->delete('public_services/' . $service->image);

            $foto = $request->file('image');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('public_services/' . $filename, file_get_contents($foto));

            $service->image = $filename;
        }

        $service->name = $request->name;
        $service->description = $request->description;
        $service->save();

        return response()->json('Servicio modificada correctamente.', 201);
    }

    public function destroy($id)
    {
        $service = PublicService::find($id);

        if(Storage::disk('public')->exists('public_services/' . $service->image))
            Storage::disk('public')->delete('public_services/' . $service->image);

        $service->delete();

        return response()->json('Datos eliminados correctamente.', 201);
    }
}
