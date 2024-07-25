<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Models */
use App\Models\Notificacion;
use App\Models\User;

class NotificacionController extends Controller
{
    public function index()
    {
        return view('pages.notificacion.index');
    }

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
            $owner_id = Auth::user()->owner_id;
        }

        $owner = User::find($owner_id);

        $rol = $owner->rol_id;
        $user_id = $owner_id;

        /** Obtenemos que tipo de notificaciones puede ver dependiendo el rol */
        $tipos = [];
        if($rol == 1){
            $tipos = [3];
        }
        else if($rol == 2){
            $tipos = [1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12];
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
        ->where('n.visto', 0)
        ->select([
            'n.*', 'tn.title', 'tn.message',
            'u.name as user', 'tn.icon', 'tn.class',
            'n.message as message_notificacion'
        ])
        ->orderBy('n.id', 'DESC')
        ->take(8)
        ->get();

        return response()->json(compact('count', 'notificaciones'), 201);
    }

    public function get_all_paginate(Request $request)
    {
        $rol = Auth::user()->rol_id;
        $user_id = Auth::id();

        $notificaciones = Notificacion::from('notificaciones as n')
        ->join('users as u', 'u.id', '=', 'n.created_by_id')
        ->join('tipos_notificaciones as tn', 'tn.id', '=', 'n.tipo_notificacion_id')
        ->where('n.receiver_id', $user_id);

        /** Filtramos por visto */
        if($request->visto){
            $notificaciones->where('visto', $request->visto);
        }

        $notificaciones->select([
            'n.*', 'tn.title', 'tn.message', 'u.name as user',
            'u.username'
        ])
        ->orderBy('n.id', 'DESC');

        $notificaciones = $notificaciones->paginate(30);

        return response()->json(compact('notificaciones'), 201);
    }

    public function marcar_vista(Request $request)
    {
        /** Marcamos la notificacion como vista */
        $notificacion = Notificacion::find($request->notificacion_id);
        $notificacion->visto = 1;
        $notificacion->save();

        return response()->json('Ok', 201);
    }

    public function marcar_vista_all(Request $request)
    {
        /** Marcamos todas las notificaciones como vistas */
        $notificaciones = Notificacion::where('visto', 0)
        ->where('receiver_id', Auth::id())
        ->select('id')
        ->get();

        foreach ($notificaciones as $key => $value) {
            $notificacion = Notificacion::find($value->id);
            $notificacion->visto = 1;
            $notificacion->save();
        }

        return response()->json('Ok', 201);
    }
}
