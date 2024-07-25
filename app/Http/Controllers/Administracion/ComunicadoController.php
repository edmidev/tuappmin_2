<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\Comunicado;
use App\Models\ResidenteConjunto;
use App\Models\TokenFirebaseUser;
use App\Models\User;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Models\ComunicadoComentario;

/** Events */
use App\Events\ComunicadoConversacionEvent;

class ComunicadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:2,3');
    }

    public  function index()
    {
        return view('pages.comunicado.index');
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

        $comunicados = Comunicado::from('comunicados as c')
        ->where('conjunto_id', $owner_id);

        /** Filtramos los datos */
        if($request->id){
            $comunicados->where('c.id', $request->id);
        }

        if($request->titulo){
            $comunicados->where('c.titulo', 'like', '%' . $request->titulo . '%');
        }        

        $comunicados->select([
            'c.*'
        ])
        ->orderBy('c.id', 'DESC');

        $comunicados = $comunicados->paginate(30);

        return response()->json(compact('comunicados'), 201);
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

        $comunicado = new Comunicado;
        if($request->file('image')){            
            $foto = $request->file('image');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('comunicados/' . $filename, file_get_contents($foto));

            $comunicado->image = $filename;
        }

        if($request->file('documento')){            
            $foto = $request->file('documento');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('comunicados/documentos/' . $filename, file_get_contents($foto));

            $comunicado->documento = $filename;
        }

        $comunicado->titulo = $request->titulo;
        $comunicado->description = $request->description;
        $comunicado->conjunto_id = $owner_id;
        $comunicado->save();

        /** Obtenemos los residentes del conjunto */
        $users = ResidenteConjunto::where('conjunto_id', $owner_id)
        ->distinct('user_id')
        ->pluck('user_id');

        /** Obtenemos los tokens del residente que envio la PQRS */
        $token = TokenFirebaseUser::from('token_firebase_users as tfu')
        ->whereIn('tfu.user_id', $users)
        ->pluck('tfu.token')->toArray();

        /** Enviamos la notificacion push al usuario */
        if(count($token) > 0){
            /** Enviamos la notificación al usuario */
            
            $action = new \stdClass;
            $action->stack = 'StackComunicado';
            $action->screen = 'Comunicado';

            $send_data = [
                'tipo' => 'nuevo_comunicado',
                'accion' => $action
            ];

            FirebasePushTrait::send('Nuevo comunicado', $request->titulo, $send_data, $token);
        }

        return response()->json('Comunicado creado correctamente.', 201);
    }
    
    public function update(Request $request, $id)
    {
        $comunicado = Comunicado::find($id);
        if($request->file('image')){
            if(Storage::disk('public')->exists('comunicados/' . $comunicado->image))
                Storage::disk('public')->delete('comunicados/' . $comunicado->image);

            $foto = $request->file('image');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('comunicados/' . $filename, file_get_contents($foto));

            $comunicado->image = $filename;
        }

        if($request->file('documento')){
            if(Storage::disk('public')->exists('comunicados/documentos/' . $comunicado->documento))
                Storage::disk('public')->delete('comunicados/documentos/' . $comunicado->documento);

            $foto = $request->file('documento');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('comunicados/documentos/' . $filename, file_get_contents($foto));

            $comunicado->documento = $filename;
        }

        $comunicado->titulo = $request->titulo;
        $comunicado->description = $request->description;
        $comunicado->save();

        return response()->json('Comunicado modificado correctamente.', 201);
    }

    public function destroy($id)
    {
        $comunicado = Comunicado::find($id);

        if(Storage::disk('public')->exists('comunicados/' . $comunicado->image))
            Storage::disk('public')->delete('comunicados/' . $comunicado->image);

        $comunicado->delete();

        return response()->json('Datos eliminados correctamente.', 201);
    }

    public function comentarios_get_all_paginate(Request $request)
    {
        $comentarios = ComunicadoComentario::from('comunicados_comentarios as cc')
        ->join('users as u', 'u.id', '=', 'cc.user_id')
        ->join('roles as r', 'r.id', '=', 'u.rol_id')
        ->where('cc.comunicado_id', $request->comunicado_id)
        ->select([
            'cc.*', 'u.name', 'u.avatar', 'r.name as rol'
        ])
        ->orderBy('cc.id', 'ASC');

        $comentarios = $comentarios->get();

        return response()->json(compact('comentarios'), 201);
    }

    public function send_message(Request $request)
    {
        $comunicado = Comunicado::find($request->comunicado_id);

        if(!$comunicado == 2){
            return response()->json('El comunicado ya no existe', 200);
        }

        if($comunicado->estatus == 2){
            return response()->json('El comunicado fue cerrado', 200);
        }

        $message = new ComunicadoComentario;
        $message->message = $request->message;
        $message->user_id = Auth::id();
        $message->comunicado_id = $request->comunicado_id;
        $message->save();

        $message_aux = new \stdClass;
        $message_aux->id = $message->id;
        $message_aux->message = $message->message;
        $message_aux->user_id = $message->user_id;
        $message_aux->comunicado_id = $message->comunicado_id;
        $message_aux->name = Auth::user()->name;
        $message_aux->rol = User::find(Auth::user()->id)->rol()->first()->name;

        /** Emitimos el evento para actualizar las conversaciones */
        broadcast(new ComunicadoConversacionEvent($message_aux));

        return response()->json('Mensaje enviado', 201);
    }
}
