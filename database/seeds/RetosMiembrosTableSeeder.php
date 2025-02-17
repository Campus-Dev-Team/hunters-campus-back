<?php



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RetosMiembrosTableSeeder extends Seeder
{
    public function run()
    {
        $adminId = DB::table('users')->where('tipo', 2)->first()->id;
        $retos = DB::table('retos')->where('estado', 3)->get();

        foreach ($retos as $reto) {
            // Obtener miembros de las tribus participantes
            $tribusParticipantes = [$reto->created_by];
            if ($reto->id_user_2) $tribusParticipantes[] = $reto->id_user_2;
            if ($reto->id_user_3) $tribusParticipantes[] = $reto->id_user_3;
            if ($reto->id_user_4) $tribusParticipantes[] = $reto->id_user_4;

            foreach ($tribusParticipantes as $tribuId) {
                $miembros = DB::table('users_miembros')
                    ->where('id_user', $tribuId)
                    ->inRandomOrder()
                    ->limit(rand(1, 3))
                    ->get();

                foreach ($miembros as $miembro) {
                    DB::table('retos_miembros')->insert([
                        'puntos' => 1,
                        'id_miembro' => $miembro->id,
                        'id_reto' => $reto->id,
                        'created_by' => $adminId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
}