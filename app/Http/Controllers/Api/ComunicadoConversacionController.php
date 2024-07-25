<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\Comunicado;
use App\Models\ResidenteConjunto;
use App\Models\TokenFirebaseUser;
use App\Models\User;
use App\Models\Notificacion;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Http\Traits\NotificacionTrait;
use App\Models\ComunicadoComentario;

/** Events */
use App\Events\ComunicadoConversacionEvent;

class ComunicadoConversacionController extends Controller
{
    public $auth = null;

    public function __construct()
    {
        $this->middleware('permisosRolesApi:5');
        $this->auth = JWTAuth::parseToken()->authenticate();
    }

    public function get_messages(Request $request)
    {
        $conversacion = new \StdClass;

        $conversacion->messages = ComunicadoComentario::from('comunicados_comentarios as cc')
        ->join('users as u', 'u.id', '=', 'cc.user_id')
        ->join('roles as r', 'r.id', '=', 'u.rol_id')
        ->where('cc.comunicado_id', $request->comunicado_id)
        ->select([
            'cc.*', 'u.name', 'u.avatar', 'r.name as rol'
        ])
        ->orderBy('cc.id', 'DESC')
        ->paginate(10);

        return response()->json(compact('conversacion'), 201);
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

        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        $message = new ComunicadoComentario;
        $message->message = $request->message;
        $message->user_id = $this->auth->id;
        $message->comunicado_id = $request->comunicado_id;
        $message->save();

        $message_aux = new \stdClass;
        $message_aux->id = $message->id;
        $message_aux->message = $message->message;
        $message_aux->user_id = $message->user_id;
        $message_aux->comunicado_id = $message->comunicado_id;
        $message_aux->name = $this->auth->name;
        $message_aux->rol = User::find($this->auth->id)->rol()->first()->name;

        /** Emitimos el evento para actualizar las conversaciones */
        broadcast(new ComunicadoConversacionEvent($message_aux));
        
        /** Verificamos que no haya ninguna notificacion de este tipo - created_by_id - recurso - vista */
        $notificacion = Notificacion::where('tipo_notificacion_id', 5)
        ->where('created_by_id', $this->auth->id)
        ->where('recurso_id', $request->comunicado_id)
        ->where('receiver_id', $owner_id)
        ->where('visto', 0)
        ->first();

        if(!$notificacion){
            /** Guardamos la notificacion para el conjunto residencial */
            NotificacionTrait::store(5, $this->auth->id, $comunicado->id, 'comunicado?id=' . $comunicado->id, $owner_id);
        }

        /** Obtenemos los tokens del conjunto residencial para notificarle */
        $token = TokenFirebaseUser::from('token_firebase_users as tfu')
        ->where('tfu.user_id', $owner_id)
        ->pluck('tfu.token')->toArray();

        if(count($token) > 0){
            /** Enviamos la notificación al usuario */
            
            $action = new \stdClass;

            $send_data = [
                'tipo' => 'mensaje_comunicado',
                'accion' => $action
            ];

            FirebasePushTrait::send('Comunicado - Nuevo comentario', $this->auth->name . ' ha comentado un comunicado', $send_data, $token);
        }

        return response()->json('Mensaje enviado', 201);
    }
}
