<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TorneosTableSeeder extends Seeder
{
    public function run()
    {
        $adminId = DB::table('users')->where('tipo', 2)->first()->id;
        
        $torneos = [
            [
                'titulo' => 'Torneo de Programación',
                'descripcion' => 'Competencia de habilidades de programación entre tribus',
                'fecha' => now()->addDays(15),
                'estado' => 1,
                'created_by' => $adminId
            ],
            [
                'titulo' => 'Hackathon Campus',
                'descripcion' => 'Desarrollo de soluciones innovadoras en 48 horas',
                'fecha' => now()->addDays(30),
                'estado' => 1,
                'created_by' => $adminId
            ],
            [
                'titulo' => 'Challenge de Base de Datos',
                'descripcion' => 'Competencia de diseño y optimización de bases de datos',
                'fecha' => now()->addDays(45),
                'estado' => 1,
                'created_by' => $adminId
            ]
        ];

        foreach ($torneos as $torneo) {
            $torneo['created_at'] = now();
            $torneo['updated_at'] = now();
            DB::table('torneos')->insert($torneo);
        }
    }
}