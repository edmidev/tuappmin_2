<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/** JWT */
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/** Models */
use App\Models\Pqrs;
use App\Models\ResidenteConjunto;
use App\Models\TokenFirebaseUser;
use App\Models\User;
use App\Models\Notificacion;

/** Trais */
use App\Http\Traits\FirebasePushTrait;
use App\Http\Traits\NotificacionTrait;
use App\Models\PqrsMensaje;

/** Events */
use App\Events\PqrsConversacionEvent;

class PqrsConversacionController extends Controller
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

        $conversacion->messages = PqrsMensaje::from('pqrs_mensajes as m')
        ->join('users as u', 'u.id', '=', 'm.user_id')
        ->join('roles as r', 'r.id', '=', 'u.rol_id')
        ->where('m.pqrs_id', $request->pqrs_id)
        ->select([
            'm.*', 'u.name', 'u.avatar', 'r.name as rol'
        ])
        ->orderBy('m.id', 'DESC')
        ->paginate(10);

        return response()->json(compact('conversacion'), 201);
    }

    public function send_message(Request $request)
    {
        $pqrs = Pqrs::find($request->pqrs_id);

        if($pqrs->estatus == 2){
            return response()->json('La PQRS fue cerrada', 200);
        }

        $residencia = json_decode($request->residencia);
        $owner_id = $residencia->conjunto_id;

        /** Obtenemos los datos de la relacion residente conjunto para saber el id del usuario */
        $residente_conjunto = ResidenteConjunto::from('residentes_conjuntos')
        ->join('users as conjunto', 'conjunto.id', '=', 'residentes_conjuntos.conjunto_id')
        ->where('residentes_conjuntos.id', $pqrs->residente_conjunto_id)
        ->select([
            'residentes_conjuntos.user_id', 'conjunto.name as conjunto'
        ])
        ->first();

        $message = new PqrsMensaje;
        $message->message = $request->message;
        $message->user_id = $this->auth->id;
        $message->pqrs_id = $request->pqrs_id;
        $message->save();

        $message_aux = new \stdClass;
        $message_aux->id = $message->id;
        $message_aux->message = $message->message;
        $message_aux->user_id = $message->user_id;
        $message_aux->pqrs_id = $message->pqrs_id;
        $message_aux->name = $this->auth->name;
        $message_aux->rol = User::find($this->auth->id)->rol()->first()->name;

        /** Emitimos el evento para actualizar las conversaciones */
        broadcast(new PqrsConversacionEvent($message_aux));
        
        /** Verificamos que no hata ninguna notificacion de este tipo - created_by_id - recurso - visto */
        $notificacion = Notificacion::where('tipo_notificacion_id', 2)
        ->where('created_by_id', $this->auth->id)
        ->where('recurso_id', $request->pqrs_id)
        ->where('receiver_id', $owner_id)
        ->where('visto', 0)
        ->first();

        if(!$notificacion){
            /** Guardamos la notificacion para el conjunto residencial */
            NotificacionTrait::store(2, $this->auth->id, $pqrs->id, 'pqrs?id=' . $pqrs->id, $owner_id);
        }

        /** Obtenemos los tokens del residente que envio la PQRS */
        $token = TokenFirebaseUser::from('token_firebase_users as tfu')
        ->where('tfu.user_id', $residente_conjunto->user_id)
        ->pluck('tfu.token')->toArray();

        if(count($token) > 0){
            /** Enviamos la notificación al usuario */
            
            $action = new \stdClass;
            $action->stack = 'StackPQRS';
            $action->screen = 'PQRS';
            $action->params = $message;

            $send_data = [
                'tipo' => 'mensaje_pqrs',
                'accion' => $action
            ];

            FirebasePushTrait::send('PQRS - ' . $residente_conjunto->conjunto, $request->message, $send_data, $token);
        }

        return response()->json('Mensaje enviado', 201);
    }
}
