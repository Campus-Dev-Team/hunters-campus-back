<?php



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersMiembrosTableSeeder extends Seeder
{
    public function run()
    {
        // Obtener IDs de las tribus (excluyendo al admin)
        $tribusIds = DB::table('users')
            ->where('tipo', 1)
            ->pluck('id');

        foreach ($tribusIds as $tribuId) {
            // Crear líder para cada tribu
            DB::table('users_miembros')->insert([
                'nombre' => 'Líder Tribu ' . $tribuId,
                'empresa' => 'Empresa Principal',
                'lider' => true,
                'comercial' => true,
                'inicio' => now(),
                'estado' => 1,
                'id_user' => $tribuId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Crear 3-5 miembros regulares para cada tribu
            $numMiembros = rand(3, 5);
            for ($i = 1; $i <= $numMiembros; $i++) {
                DB::table('users_miembros')->insert([
                    'nombre' => 'Miembro ' . $i . ' Tribu ' . $tribuId,
                    'empresa' => 'Empresa ' . $i,
                    'lider' => false,
                    'comercial' => false,
                    'inicio' => now(),
                    'estado' => 1,
                    'id_user' => $tribuId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}