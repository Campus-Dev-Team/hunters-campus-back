<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersMiembrosTable extends Migration
{
    public function up()
    {
        Schema::create('users_miembros', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('empresa')->nullable();
            $table->boolean('lider')->default(false);
            $table->string('cliente_id')->nullable();
            $table->boolean('comercial')->default(false);
            $table->date('inicio');
            $table->date('fin')->nullable();
            $table->tinyInteger('estado')->default(1);
            
            $table->foreignId('id_user')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_miembros');
    }
};