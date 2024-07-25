<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Noticia;

class NoticiaController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public  function index()
    {
        return view('pages.noticia.index');
    }

    public function get_all_paginate(Request $request)
    {
        $noticias = Noticia::from('noticias as n');

        /** Filtramos por nombre */
        if($request->name){
            $noticias->where('n.titulo', 'like', '%' . $request->name . '%');
        }        

        $noticias->select([
            'n.*'
        ])
        ->orderBy('n.id', 'ASC');

        $noticias = $noticias->paginate(30);

        return response()->json(compact('noticias'), 201);
    }   

    public function store(Request $request)
    {
        $noticia = new Noticia;
        if($request->file('image')){            
            $foto = $request->file('image');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('images_noticias/' . $filename, file_get_contents($foto));

            $noticia->image = $filename;
        }

        $noticia->titulo = $request->titulo;
        $noticia->link = $request->link;
        $noticia->save();

        return response()->json('Noticia creada correctamente.', 201);
    }
    
    public function update(Request $request, $id)
    {
        $noticia = Noticia::find($id);
        if($request->file('image')){
            if(Storage::disk('public')->exists('images_noticias/' . $noticia->image))
                Storage::disk('public')->delete('images_noticias/' . $noticia->image);

            $foto = $request->file('image');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('images_noticias/' . $filename, file_get_contents($foto));

            $noticia->image = $filename;
        }

        $noticia->titulo = $request->titulo;
        $noticia->link = $request->link;
        $noticia->save();

        return response()->json('Noticia modificada correctamente.', 201);
    }

    public function destroy($id)
    {
        $noticia = Noticia::find($id);

        if(Storage::disk('public')->exists('images_noticias/' . $noticia->image))
            Storage::disk('public')->delete('images_noticias/' . $noticia->image);

        $noticia->delete();

        return response()->json('Datos eliminados correctamente.', 201);
    }
}
