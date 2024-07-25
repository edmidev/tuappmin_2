<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ZonaComun extends Model
{
    use SoftDeletes;

    protected $table = 'zonas_comunes';

    public function imagenes()
    {
        return $this->hasMany(ZonaComunImagen::class);
    }

    public function horarios()
    {
        return $this->hasMany(ZonaComunHorario::class);
    }
}
