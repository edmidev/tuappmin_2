<?php

namespace App\Http\Traits;

/** Models */
use App\Models\Notificacion;

trait NotificacionTrait
{
    public static function store($tipo, $created_by_id, $recurso_id, $accion, $receiver_id = null, $message = null)
    {
        $new = new Notificacion;
        $new->tipo_notificacion_id = $tipo;
        $new->created_by_id = $created_by_id;
        $new->recurso_id = $recurso_id;
        $new->receiver_id = $receiver_id;
        $new->accion = $accion;
        $new->message = $message;
        $new->save();

        return $new;
    }
}