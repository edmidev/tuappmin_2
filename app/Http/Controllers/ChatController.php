<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Models */
use App\Events\ChatEvent;
use App\Models\Message;
use App\Models\User;
use App\Models\Notificacion;

/** Traits */
use App\Http\Traits\NotificacionTrait;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('pages.chat.index');
    }

    public function getMessages(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 1){
            $owner_id = Auth::id();
        }
        else if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $auth = User::find($owner_id);

        /** Marcamos los mensajes como ledidos */
        $messages = Message::where('codigo', $request->codigo)
        ->where('receptor_id', $auth->id)
        ->where('visto', 0)
        ->get();

        foreach ($messages as $key => $value) {
            $message = Message::find($value->id);
            $message->visto = 1;
            $message->save();
        }

        $messages = Message::from('messages as m')
        ->join('users as u', 'u.id', '=', 'm.emisor_id')
        ->join('users as us', 'us.id', '=', 'm.receptor_id')
        ->where('codigo', $request->codigo)
        ->select([
            'm.*', 'u.name as emisor', 'us.name as receptor'
        ])
        ->orderBy('m.id', 'ASC')
        ->get();

        return response()->json(compact('messages'), 201);  
    }

    public function get_conversaciones()
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 1){
            $owner_id = Auth::id();
        }
        else if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $auth = User::find($owner_id);

        $messages = Message::from('messages as m')
        ->join('users as u', 'u.id', '=', 'm.emisor_id')
        ->join('users as us', 'us.id', '=', 'm.receptor_id')
        ->where('m.emisor_id', $auth->id)
        ->orWhere('m.receptor_id', $auth->id)
        ->select([
            'm.*', 'u.name as emisor', 'us.name as receptor'
        ])        
        ->orderBy('m.id', 'DESC')
        ->groupBy('m.codigo')
        ->get();

        /** Contamos los mensajes que tiene sin leer */
        foreach ($messages as $key => $value) {
            $value->last_message = Message::where('codigo', $value->codigo)
            ->orderBy('id', 'DESC')
            ->select('message', 'created_at', 'image')
            ->first();

            $value->count_no_vistos = Message::where('codigo', $value->codigo)
            ->where('receptor_id', $auth->id)
            ->where('visto', 0)
            ->count();
        }

        return response()->json(compact('messages'), 201);
    }

    public function sendMessage(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 1){
            $owner_id = Auth::id();
        }
        else if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $auth = User::find($owner_id);

        $chat = Message::where('codigo', $request->codigo)->orderBy('id', 'DESC')->first();

        /** Guardamos el mensaje */
        $message = New Message;

        /** Verificamos si existe una imagen */
        if($request->file('image')){            
            $foto = $request->file('image');
            $extension = pathinfo($foto->getClientOriginalName(), PATHINFO_EXTENSION);  
            $filename = time() . Str::random(5) . '.' . $extension;                        
            Storage::disk('public')->put('images_chats/' . $filename, file_get_contents($foto));

            $message->image = $filename;
        }

        $message->message = $request->message;
        $message->emisor_id = $auth->id;
        $message->receptor_id = $auth->id == $chat->emisor_id ? $chat->receptor_id : $chat->emisor_id;
        $message->codigo = $request->codigo;
        $message->save();

        /** Marcamos los mensajes como ledidos */
        $messages = Message::where('codigo', $request->codigo)
        ->where('receptor_id', $auth->id)
        ->where('visto', 0)
        ->get();

        foreach ($messages as $key => $value) {
            $message = Message::find($value->id);
            $message->visto = 1;
            $message->save();
        }

        /** Emitimos el evento para actualizar la conversacion */
        broadcast(new ChatEvent());

        /** Verificamos que no hata ninguna notificacion de este tipo - created_by_id - recurso - visto */
        $notificacion = Notificacion::where('tipo_notificacion_id', 3)
        ->where('created_by_id', $owner_id)
        ->where('receiver_id', 1)
        ->where('visto', 0)
        ->first();

        if(!$notificacion){
            /** Guardamos la notificacion para el conjunto residencial */
            NotificacionTrait::store(3, $owner_id, $message->id, 'chat', 1);
        }

        return response()->json('Mensaje enviado', 201);
    }

    public function nuevoChat(Request $request)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 1){
            $owner_id = Auth::id();
        }
        else if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $auth = User::find($owner_id);

        $contacto = User::where('email', $request->email)->where('id', '!=', $owner_id)->first();

        /** Verificamos que el email exiata en lista de usuarios */
        if(!$contacto)
            return response()->json('Email de contacto no encontrado', 200);        

        $codigos[0] = $auth->id . $contacto->id;
        $codigos[1] = $contacto->id . $auth->id;
        $chat = Message::orWhereIn('codigo', $codigos)->get();

        /** Verificamos que no exista una conversacion entre estos 2 usuarios */
        if(count($chat) > 0)
            return response()->json('Ya tienes agregada una conversación con este contacto', 200);

        $message = New Message;
        $message->message = $request->message;
        $message->emisor_id = $auth->id;
        $message->receptor_id = $contacto->id;
        $message->codigo = $auth->id . $contacto->id;
        $message->save();

        $message = Message::from('messages as m')
        ->join('users as u', 'u.id', '=', 'm.emisor_id')
        ->join('users as us', 'us.id', '=', 'm.receptor_id')
        ->where('m.id', $message->id)
        ->select([
            'm.*', 'u.name as emisor', 'us.name as receptor'
        ])
        ->first();

        broadcast(new ChatEvent())->toOthers();

        /** Guardamos la notificacion para el conjunto residencial */
        NotificacionTrait::store(3, $owner_id, $message->id, 'chat', 1);

        return response()->json(compact('message'), 201);
    }
}
