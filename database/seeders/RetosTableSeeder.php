<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RetosTableSeeder extends Seeder
{
    public function run()
    {
        $tribusIds = DB::table('users')
            ->where('tipo', 1)
            ->pluck('id')
            ->toArray();

        $lugares = ['Sala A', 'Sala B', 'Sala Virtual', 'Campo Principal', 'Auditorio'];
        
        // Crear 10 retos con diferentes estados y participantes
        for ($i = 1; $i <= 10; $i++) {
            $cantidadParticipantes = rand(2, 4);
            $creadorId = $tribusIds[array_rand($tribusIds)];
            
            // Asignar participantes aleatorios diferentes al creador
            $participantesDisponibles = array_diff($tribusIds, [$creadorId]);
            $participantes = array_rand(array_flip($participantesDisponibles), $cantidadParticipantes - 1);
            
            if (!is_array($participantes) && $cantidadParticipantes == 2) {
                $participantes = [$participantes];
            }

            $estado = rand(1, 3);
            $fecha = now()->addDays(rand(1, 30));

            $reto = [
                'titulo' => 'Reto #' . $i,
                'descripcion' => 'Descripción del reto número ' . $i,
                'fecha' => $fecha,
                'hora' => sprintf('%02d:00:00', rand(8, 17)),
                'lugar' => $lugares[array_rand($lugares)],
                'cantidad' => $cantidadParticipantes,
                'puntos' => rand(10, 50) * 10,
                'estado' => $estado,
                'created_by' => $creadorId,
                'id_user_2' => $participantes[0] ?? null,
                'id_user_3' => $participantes[1] ?? null,
                'id_user_4' => $participantes[2] ?? null,
                'id_user_ganador' => $estado == 3 ? $creadorId : null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            DB::table('retos')->insert($reto);
        }
    }
}