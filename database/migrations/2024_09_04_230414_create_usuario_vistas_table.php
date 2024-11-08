<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuarioVistasTable extends Migration
{
    public function up()
    {
        Schema::create('usuario_vistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vista_id')->constrained('vistas')->onDelete('no action')->onUpdate('no action');
            $table->foreignId('usuarios_roles_id')->constrained('usuarios_roles')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuario_vistas');
    }
}