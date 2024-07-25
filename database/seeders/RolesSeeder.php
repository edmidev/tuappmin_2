<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = array(
            'Súper administrador',
            'Conjunto residencial',
            'Administrador',
            'Portero',
            'Residente',            
        );

        foreach ($roles as $key => $value) {
            if(!Rol::where('name', $value)->first()){
                $rol = new Rol;
                $rol->name = $value;
                $rol->save();
            }
        }
    }
}
