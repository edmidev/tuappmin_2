<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\Notificacion;
use App\Models\User;
use App\Models\Comunicado;
use App\Models\TipoNotificacion;

class NotificationController extends Controller
{
    public function get_take()
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
            $owner_id = Auth::id();
        }

        $owner = User::find($owner_id);

        $rol = $owner->rol_id;
        $user_id = $owner_id;

        /** Obtenemos que tipo de notificaciones puede ver dependiendo el rol */
        $tipos = [];
        if($rol == 1){
            $tipos = [3];
        }
        else if($rol == 2 || $rol == 4 || $rol == 5){
            $tipos = TipoNotificacion::where('class', 'app')->whereIn('icon', ['visitantes','correspondencia', 'parqueadero'])->get()->pluck('id');
        }

        /** Obtenemos el número de notificaciones pendientes */
        $count = Notificacion::from('notificaciones as n')
        ->whereIn('n.tipo_notificacion_id', $tipos)
        ->where('n.receiver_id', $user_id)
        ->where('n.visto', 0)
        ->count();

        /** Obtenemos las ultimas notificaciones pendientes */
        $notificaciones = Notificacion::from('notificaciones as n')
        ->join('tipos_notificaciones as tn', 'tn.id', '=', 'n.tipo_notificacion_id')
        ->join('users as u', 'u.id', '=', 'n.created_by_id')
        ->whereIn('n.tipo_notificacion_id', $tipos)
        ->where('n.receiver_id', $user_id)
        // ->where('n.visto', 0)
        ->select([
            'n.*', 'tn.title', 'tn.message', 'tn.id as tipo',
            'u.name as user', 'tn.icon', 'tn.class',
            'n.message as message_notificacion'
        ])
        ->take(30)
        ->orderBy('n.id', 'DESC')
        ->get();

        return response()->json(compact('count', 'notificaciones'), 201);
    }

    public function latestNews()
    {
        $news = Comunicado::orderBy('id', 'desc')->take(5)->get();
        foreach ($news as $new) {
            // $new->imageUrl = 'http://192.168.0.119/storage/comunicados/' . $new->image;
            // $new->documentUrl = 'http://192.168.0.119/storage/comunicados/documentos/'. $new->documento;
            $new->imageUrl = url('storage/comunicados/' . $new->image);
            $new->documentUrl = url('storage/comunicados/documentos/'. $new->documento);
            
        }
        return response()->json(compact('news'), 201);
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $notification = Notificacion::find($id);
        info($notification);

        if (!$notification) {
            return response()->json('Notificacion no encontrada', 404);
        }

        if ($notification->visto === 0){
            $notification->visto = 1;
            $notification->save();
    
            return response()->json('Notificacion leida', 201);
        }
    }
}
