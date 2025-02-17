<?php



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Admin user
        DB::table('users')->insert([
            'nombre' => 'Administrador',
            'correo' => 'admin@campus.com',
            'password' => Hash::make('admin123'),
            'tipo' => 2, // Admin
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Tribus (equipos)
        $tribus = [
            [
                'nombre' => 'Tribu Alpha',
                'correo' => 'alpha@campus.com',
                'color' => '#FF5733',
                'password' => Hash::make('alpha123'),
            ],
            [
                'nombre' => 'Tribu Beta',
                'correo' => 'beta@campus.com',
                'color' => '#33FF57',
                'password' => Hash::make('beta123'),
            ],
            [
                'nombre' => 'Tribu Gamma',
                'correo' => 'gamma@campus.com',
                'color' => '#3357FF',
                'password' => Hash::make('gamma123'),
            ],
            [
                'nombre' => 'Tribu Delta',
                'correo' => 'delta@campus.com',
                'color' => '#F033FF',
                'password' => Hash::make('delta123'),
            ]
        ];

        foreach ($tribus as $tribu) {
            DB::table('users')->insert([
                'nombre' => $tribu['nombre'],
                'correo' => $tribu['correo'],
                'password' => $tribu['password'],
                'color' => $tribu['color'],
                'tipo' => 1, // Tribu
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}