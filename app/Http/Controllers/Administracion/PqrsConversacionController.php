<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\User;
use App\Models\Pqrs;
use App\Models\PqrsMensaje;
use App\Models\TokenFirebaseUser;
use App\Models\ResidenteConjunto;

/** Events */
use App\Events\PqrsConversacionEvent;

/** Trais */
use App\Http\Traits\FirebasePushTrait;

class PqrsConversacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permisosRoles:2,3');
    }

    public function chat($pqrs_id)
    {
        /** Obtenemos la casa o el apartamento principal */
        $owner_id = null;
        if(Auth::user()->rol_id == 2){
            $owner_id = Auth::id();
        }
        else{
            $owner_id = Auth::user()->owner_id;
        }

        $conjunto_residencial = User::find($owner_id);

        $pqrs = Pqrs::from('pqrs')
        ->join('residentes_conjuntos as rc', 'rc.id', 'pqrs.residente_conjunto_id')
        ->join('users as u', 'u.id', '=', 'rc.user_id')
        ->where('pqrs.id', $pqrs_id);

        $select = '';
        if($conjunto_residencial->tipo == 'Apartamento'){
            $pqrs->leftJoin('apartamentos as t', 't.id', '=', 'rc.apartamento_id');
            $select = 't.bloque, t.apartamento';
        }
        else{
            $pqrs->leftJoin('casas as t', 't.id', '=', 'rc.casa_id');
            $select = 't.numero';
        }

        $pqrs->select([
            'pqrs.*', \DB::raw($select), 'u.name as residente',
            'u.avatar'
        ]);

        $pqrs = $pqrs->first();

        return view('pages.administracion.pqrs.chat', compact('conjunto_residencial', 'pqrs'));
    }

    public function get_messages(Request $request)
    {
        $messages = PqrsMensaje::from('pqrs_mensajes as m')
        ->join('users as u', 'u.id', '=', 'm.user_id')
        ->join('roles as r', 'r.id', '=', 'u.rol_id')
        ->where('m.pqrs_id', $request->pqrs_id)
        ->select([
            'm.*', 'u.name', 'u.avatar', 'r.name as rol'
        ])
        ->orderBy('m.id', 'ASC')
        ->get();

        return response()->json(compact('messages'), 201);
    }

    public function send_message(Request $request)
    {
        $pqrs = Pqrs::find($request->pqrs_id);

        if($pqrs->estatus == 2){
            return response()->json('La PQRS fue cerrada', 200);
        }

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
        $message->user_id = Auth::id();
        $message->pqrs_id = $request->pqrs_id;
        $message->save();

        $message_aux = new \stdClass;
        $message_aux->id = $message->id;
        $message_aux->message = $message->message;
        $message_aux->user_id = $message->user_id;
        $message_aux->pqrs_id = $message->pqrs_id;
        $message_aux->name = Auth::user()->name;
        $message_aux->rol = User::find(Auth::id())->rol()->first()->name;

        /** Emitimos el evento para actualizar las conversaciones */
        broadcast(new PqrsConversacionEvent($message_aux));

        /** Obtenemos los tokens del residente que envio la PQRS */
        $token = TokenFirebaseUser::from('token_firebase_users as tfu')
        ->where('tfu.user_id', $residente_conjunto->user_id)
        ->pluck('tfu.token')->toArray();

        /** Enviamos la notificacion push al usuario */
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
