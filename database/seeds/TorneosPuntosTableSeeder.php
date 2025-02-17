<?php



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TorneosPuntosTableSeeder extends Seeder
{
    public function run()
    {
        $torneos = DB::table('torneos')->get();
        $tribus = DB::table('users')->where('tipo', 1)->get();
        $adminId = DB::table('users')->where('tipo', 2)->first()->id;

        foreach ($torneos as $torneo) {
            // Seleccionar 3-4 tribus participantes por torneo
            $tribusParticipantes = $tribus->random(rand(3, 4));
            
            foreach ($tribusParticipantes as $tribu) {
                $juegos = rand(3, 8);
                $victorias = rand(0, $juegos);
                $puntos = ($victorias * 100) + (($juegos - $victorias) * 30);

                DB::table('torneos_puntos')->insert([
                    'puntos' => $puntos,
                    'juegos' => $juegos,
                    'victorias' => $victorias,
                    'id_torneo' => $torneo->id,
                    'id_user' => $tribu->id,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}