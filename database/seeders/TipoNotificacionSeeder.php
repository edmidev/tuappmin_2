<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Models */
use App\Models\TipoNotificacion;

class TipoNotificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tipos = array(
            [
                'title' => 'Nueva PQRS', 
                'message' => 'El residente :name ha registrado una nueva PQRS.',
                'icon' => 'bx bx-folder-open', 
                'class' => 'bg-light-primary text-primary',
            ],
            [
                'title' => 'PQRS - Nuevo mensaje', 
                'message' => 'El residente :name ha enviando un nuevo mensaje.',
                'icon' => 'bx bx-message-dots', 
                'class' => 'bg-light-danger text-danger',
            ],
            [
                'title' => 'Mensaje de soporte', 
                'message' => ':name ha enviando un nuevo mensaje.',
                'icon' => 'bx bx-support', 
                'class' => 'bg-light-secondary text-secondary',
            ],
            [
                'title' => 'Notificación del administrador', 
                'message' => ':name ha enviando una notificación.',
                'icon' => 'bx bx-notification', 
                'class' => 'bg-light-default text-default',
            ],
            [
                'title' => 'Comunicado - Nuevo comentario', 
                'message' => ':name ha comentado un comunicado.',
                'icon' => 'bx bx-comment',
                'class' => 'bg-light-warning text-warning',
            ],
            [
                'title' => 'Pago de administración mensual', 
                'message' => ':name ha cargado un comprobante de transferencia bancaria. Esperando aprobación.',
                'icon' => 'bx bx-transfer',
                'class' => 'bg-light-info text-info',
            ],
            [
                'title' => 'Pago de administración semestral', 
                'message' => ':name ha cargado un comprobante de transferencia bancaria. Esperando aprobación.',
                'icon' => 'bx bx-transfer',
                'class' => 'bg-light-info text-info',
            ],
            [
                'title' => 'Pago de administración anual',
                'message' => ':name ha cargado un comprobante de transferencia bancaria. Esperando aprobación.',
                'icon' => 'bx bx-transfer',
                'class' => 'bg-light-info text-info',
            ],
            [
                'title' => 'Pago de administración mensual',
                'message' => ':name ha pagado la administración.',
                'icon' => 'bx bx-credit-card',
                'class' => 'bg-light-primary text-primary',
            ],
            [
                'title' => 'Pago de administración semestral',
                'message' => ':name ha pagado la administración.',
                'icon' => 'bx bx-credit-card',
                'class' => 'bg-light-primary text-primary',
            ],
            [
                'title' => 'Pago de administración anual',
                'message' => ':name ha pagado la administración.',
                'icon' => 'bx bx-credit-card',
                'class' => 'bg-light-primary text-primary',
            ],
            [
                'title' => 'Reservación de zona',
                'message' => ':name ha reservado una zona común.',
                'icon' => 'bx bx-calendar-week',
                'class' => 'bg-light-primary text-primary',
            ],
        );

        foreach ($tipos as $key => $value) {
            $tipo = TipoNotificacion::find($key + 1);

            if(!$tipo)
                $tipo = new TipoNotificacion;

            $tipo->title = $value['title'];
            $tipo->message = $value['message'];
            $tipo->icon = $value['icon'];
            $tipo->class = $value['class'];
            $tipo->save();
        }
    }
}
