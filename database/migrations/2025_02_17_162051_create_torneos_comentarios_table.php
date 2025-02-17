<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorneosComentariosTable extends Migration
{
    public function up()
    {
        Schema::create('torneos_comentarios', function (Blueprint $table) {
            $table->id();
            $table->text('comentario');
            
            $table->foreignId('id_torneo')->constrained('torneos');
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('torneos_comentarios');
    }
};