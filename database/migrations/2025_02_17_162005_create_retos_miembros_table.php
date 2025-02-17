<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetosMiembrosTable extends Migration
{
    public function up()
    {
        Schema::create('retos_miembros', function (Blueprint $table) {
            $table->id();
            $table->integer('puntos')->default(0);
            $table->text('comentario')->nullable();
            
            $table->foreignId('id_miembro')->constrained('users_miembros');
            $table->foreignId('id_reto')->nullable()->constrained('retos');
            $table->foreignId('id_torneo')->nullable()->constrained('torneos');
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retos_miembros');
    }
};