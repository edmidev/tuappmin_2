<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministracionPago extends Model
{
    use HasFactory;

    protected $table = 'administracion_pagos';

    protected $fillable = [
        'cantidad_meses', 'total', 'valor_administracion', 'valor_mora',
        'descuento_pronto_pago', 'descuento_pago_semestre', 'descuento_pago_anual',
        'metodo_pago', 'estatus_pago', 'comprobante', 'change_estatus_user_id',
        'residente_conjunto_id'
    ];
}
