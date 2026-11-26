<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'usuarios';
    public $timestamps = true;
    protected $hidden = ['contrasena'];
    protected $casts = [
        'sesion_activa' => 'boolean',
    ];
}
