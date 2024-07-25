<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConjuntoInformacion extends Model
{
    use HasFactory;

    protected $table = 'conjuntos_informaciones';

    protected $fillable = [
        'telefono_porteria', 'direccion',
        'banco', 'numero_cuenta', 'tipo_cuenta',
        'numero_parqueaderos', 'horas_gratis', 'valor_hora_adicional_moto',
        'valor_hora_adicional_carro', 'hour_diurno_init', 'hour_diurno_end',
        'valor_diurno', 'hour_nocturno_init', 'hour_nocturno_end',
        'valor_nocturno', 'hour_completo_init', 'hour_completo_end',
        'valor_completo', 'conjunto_id',
        'public_key', 'private_key', 'p_cust_id_cliente', 'p_key'
    ];
}
