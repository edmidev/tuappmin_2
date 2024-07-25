<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citofonia extends Model
{
    use HasFactory;

    protected $table = 'citofonias';

    public function conjunto()
    {
        return $this->belongsTo(User::class, 'conjunto_id', 'id');
    }
}
