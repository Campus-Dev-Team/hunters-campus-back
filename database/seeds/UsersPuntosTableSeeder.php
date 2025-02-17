<?php



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersPuntosTableSeeder extends Seeder
{
    public function run()
    {
        $adminId = DB::table('users')->where('tipo', 2)->first()->id;
        $tribus = DB::table('users')->where('tipo', 1)->get();
        $retos = DB::table('retos')->where('estado', 3)->get();
        $torneos = DB::table('torneos')->get();

        // Puntos por retos
        foreach ($retos as $reto) {
            $puntosAnteriores = DB::table('users_puntos')
                ->where('id_user', $reto->id_user_ganador)
                ->orderBy('id', 'desc')
                ->first();

            $puntosActuales = $puntosAnteriores ? $puntosAnteriores->puntos_nuevos : 0;
            $puntosGanados = $reto->puntos * ($reto->cantidad - 1);

            // Registrar puntos para el ganador
            DB::table('users_puntos')->insert([
                'tipo' => 1, // Reto
                'puntos_afectado' => $puntosGanados,
                'puntos_anteriores' => $puntosActuales,
                'puntos_nuevos' => $puntosActuales + $puntosGanados,
                'afectacion' => 1, // Suma
                'id_user' => $reto->id_user_ganador,
                'id_reto' => $reto->id,
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Puntos por torneos
        foreach ($torneos as $torneo) {
            $tribusParticipantes = $tribus->random(rand(2, count($tribus)));
            
            foreach ($tribusParticipantes as $tribu) {
                $puntosAnteriores = DB::table('users_puntos')
                    ->where('id_user', $tribu->id)
                    ->orderBy('id', 'desc')
                    ->first();

                $puntosActuales = $puntosAnteriores ? $puntosAnteriores->puntos_nuevos : 0;
                $puntosGanados = rand(50, 200);

                DB::table('users_puntos')->insert([
                    'tipo' => 2, // Torneo
                    'puntos_afectado' => $puntosGanados,
                    'puntos_anteriores' => $puntosActuales,
                    'puntos_nuevos' => $puntosActuales + $puntosGanados,
                    'afectacion' => 1, // Suma
                    'id_user' => $tribu->id,
                    'id_torneo' => $torneo->id,
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}