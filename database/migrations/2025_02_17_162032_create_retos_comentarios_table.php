<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetosComentariosTable extends Migration
{
    public function up()
    {
        Schema::create('retos_comentarios', function (Blueprint $table) {
            $table->id();
            $table->text('comentario');
            
            $table->foreignId('id_reto')->constrained('retos');
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retos_comentarios');
    }
};