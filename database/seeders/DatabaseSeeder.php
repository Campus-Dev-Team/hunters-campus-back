<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            \Database\Seeders\UsersTableSeeder::class,
            \Database\Seeders\UsersMiembrosTableSeeder::class,
            \Database\Seeders\RetosTableSeeder::class,
            \Database\Seeders\TorneosTableSeeder::class,
            \Database\Seeders\UsersPuntosTableSeeder::class,
            \Database\Seeders\RetosMiembrosTableSeeder::class,
            \Database\Seeders\RetosComentariosTableSeeder::class,
            \Database\Seeders\TorneosComentariosTableSeeder::class,
            \Database\Seeders\TorneosPuntosTableSeeder::class,
        ]);
    }
}