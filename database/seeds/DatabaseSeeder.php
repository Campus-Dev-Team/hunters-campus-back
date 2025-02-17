<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            UsersMiembrosTableSeeder::class,
            RetosTableSeeder::class,
            TorneosTableSeeder::class,
            UsersPuntosTableSeeder::class,
            RetosMiembrosTableSeeder::class,
            RetosComentariosTableSeeder::class,
            TorneosComentariosTableSeeder::class,
            TorneosPuntosTableSeeder::class,
        ]);
    }
}