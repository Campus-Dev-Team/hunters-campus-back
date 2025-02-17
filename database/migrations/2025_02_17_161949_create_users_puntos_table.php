<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPuntosTable extends Migration
{
    public function up()
    {
        Schema::create('users_puntos', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('tipo')->comment('1: Reto, 2: Torneo, 3: Manual');
            $table->integer('puntos_afectado');
            $table->integer('puntos_anteriores');
            $table->integer('puntos_nuevos');
            $table->tinyInteger('afectacion')->comment('1: Suma, 2: Resta');
            $table->string('manual_nombre')->nullable();
            $table->text('manual_descripcion')->nullable();
            
            $table->foreignId('id_user')->constrained('users');
            $table->foreignId('id_reto')->nullable()->constrained('retos');
            $table->foreignId('id_torneo')->nullable()->constrained('torneos');
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_puntos');
    }
};