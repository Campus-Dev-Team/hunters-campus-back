<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TorneosComentariosTableSeeder extends Seeder
{
    public function run()
    {
        $torneos = DB::table('torneos')->get();
        $tribus = DB::table('users')->where('tipo', 1)->get();
        
        $comentarios = [
            '¡Este torneo será épico!',
            'Nuestra tribu está entrenando duro para este torneo.',
            'Las reglas parecen muy interesantes.',
            'Esperamos una gran competencia.',
            '¿Habrá premios adicionales?',
            'Será un excelente espacio para demostrar habilidades.',
            'La fecha del torneo es perfecta.',
            '¿Cuántos miembros pueden participar por tribu?',
            'Nos estamos preparando para dar lo mejor.',
            'Este formato de torneo es muy innovador.'
        ];

        foreach ($torneos as $torneo) {
            // Generar 3-6 comentarios aleatorios por torneo
            $numComentarios = rand(3, 6);
            
            for ($i = 0; $i < $numComentarios; $i++) {
                DB::table('torneos_comentarios')->insert([
                    'comentario' => $comentarios[array_rand($comentarios)],
                    'id_torneo' => $torneo->id,
                    'created_by' => $tribus->random()->id,
                    'created_at' => now()->subMinutes(rand(1, 120)),
                    'updated_at' => now()
                ]);
            }
        }
    }
}