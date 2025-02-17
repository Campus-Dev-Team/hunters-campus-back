<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorneosPuntosTable extends Migration
{
    public function up()
    {
        Schema::create('torneos_puntos', function (Blueprint $table) {
            $table->id();
            $table->integer('puntos');
            $table->integer('juegos');
            $table->integer('victorias');
            
            $table->foreignId('id_torneo')->constrained('torneos');
            $table->foreignId('id_user')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('torneos_puntos');
    }
};