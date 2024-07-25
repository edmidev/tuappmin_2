<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwilloProxy extends Model
{
    use HasFactory;

    protected $fillable = [
        'twillo_number_id',
        'target_number',
        'active',
    ];

    public function twilloNumber()
    {
        return $this->belongsTo(TwilloNumber::class);
    }
}
