<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConjuntoInformacionPago extends Model
{
    use HasFactory;

    protected $table = 'conjuntos_informacion_pagos';
    
    protected $fillable = [
        'year', 'valor_administracion', 'limite_pago', 'interes_mora',
        'descuento_pronto_pago', 'limite_pronto_pago',
        'descuento_pago_semestre', 'descuento_pago_anual',
        'conjunto_id'
    ];
}
