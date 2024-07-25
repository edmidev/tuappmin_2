<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonaComunReservacion extends Model
{
    use HasFactory;

    protected $table = 'zonas_comunes_reservaciones';

    protected $fillable = [
        'fecha_inicio', 'fecha_fin', 'horario_id', 'residente_conjunto_id'
    ];
}
