<?php
// app/Models/UsuarioPermission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioPermission extends Model
{
    use HasFactory;

    protected $table = 'permisos_usuarios';

    protected $fillable = [
        'vista_id',
        'permiso_id',
        'usuarios_roles_id',
    ];
}