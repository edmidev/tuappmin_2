<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ResidenteConjunto extends Model
{
    use SoftDeletes;

    protected $table = 'residentes_conjuntos';
}
