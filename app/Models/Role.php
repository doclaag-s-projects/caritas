<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','estado',];

    
    protected $attributes = [
        'estado' => 0,
    ];

    public function usuariosRoles()
    {
        return $this->hasMany(UsuarioRol::class, 'rol_id');
    }
}