<?php



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RetosComentariosTableSeeder extends Seeder
{
    public function run()
    {
        $retos = DB::table('retos')->get();
        $tribus = DB::table('users')->where('tipo', 1)->get();
        
        $comentarios = [
            '¡Excelente reto! Nos vemos allí.',
            'Nuestra tribu está lista para este desafío.',
            'Interesante propuesta, ¡participamos!',
            'Va a estar muy competitivo.',
            'Necesitamos más información sobre el reto.',
            '¡Qué buen desafío! Contad con nosotros.',
            'Este reto será interesante.',
            '¿Podemos usar nuestras propias herramientas?',
            'La fecha nos viene perfecta.',
            'Tenemos algunas dudas sobre las reglas.'
        ];

        foreach ($retos as $reto) {
            // Generar 2-4 comentarios aleatorios por reto
            $numComentarios = rand(2, 4);
            
            for ($i = 0; $i < $numComentarios; $i++) {
                DB::table('retos_comentarios')->insert([
                    'comentario' => $comentarios[array_rand($comentarios)],
                    'id_reto' => $reto->id,
                    'created_by' => $tribus->random()->id,
                    'created_at' => now()->subMinutes(rand(1, 60)),
                    'updated_at' => now()
                ]);
            }
        }
    }
}