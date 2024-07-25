<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenFirebaseUser extends Model
{
    use HasFactory;

    protected $table = 'token_firebase_users';
}
