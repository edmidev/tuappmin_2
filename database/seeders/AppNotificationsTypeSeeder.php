<?php

namespace Database\Seeders;

use App\Models\TipoNotificacion;
use Illuminate\Database\Seeder;

class AppNotificationsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!TipoNotificacion::where('class', 'app')->where('icon', 'correspondencia')->first()){
            $tipo = new TipoNotificacion;
            $tipo->title = 'Correspondencia';
            $tipo->message = 'Nueva notificacion de correspondencia';
            $tipo->icon = 'correspondencia';
            $tipo->class = 'app';
            $tipo->save();
        }

        if (!TipoNotificacion::where('class', 'app')->where('icon', 'visitantes')->first()){
            $tipo = new TipoNotificacion;
            $tipo->title = 'Visitantes';
            $tipo->message = 'Nueva notificacion de visitantes';
            $tipo->icon = 'visitantes';
            $tipo->class = 'app';
            $tipo->save();
        }

        if (!TipoNotificacion::where('class', 'app')->where('icon', 'parqueadero')->first()){
            $tipo = new TipoNotificacion;
            $tipo->title = 'Parqueadero';
            $tipo->message = 'Nueva notificacion de parqueadero';
            $tipo->icon = 'parqueadero';
            $tipo->class = 'app';
            $tipo->save();
        }
    }
}
