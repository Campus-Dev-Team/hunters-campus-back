<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetosTable extends Migration
{
    public function up()
    {
        Schema::create('retos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 60);
            $table->text('descripcion');
            $table->date('fecha');
            $table->time('hora');
            $table->string('lugar', 40);
            $table->integer('cantidad')->comment('2,3,4 participantes');
            $table->integer('puntos');
            $table->tinyInteger('estado')->default(1)->comment('1: Pendiente, 2: En Progreso, 3: Completado, 11: Eliminado pendiente, 12: Eliminado en progreso');
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('id_user_2')->nullable()->constrained('users');
            $table->foreignId('id_user_3')->nullable()->constrained('users');
            $table->foreignId('id_user_4')->nullable()->constrained('users');
            $table->foreignId('id_user_ganador')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retos');
    }
};