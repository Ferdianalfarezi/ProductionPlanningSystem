<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Mesin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default users
        User::create([
            'nama' => 'Super Administrator',
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        User::create([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'nama' => 'Operator User',
            'username' => 'operator',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        // Create sample mesin data
        $mesinData = [
            ['nama' => 'Press Machine 01', 'gsph' => 120, 'struk' => 4, 'tonase' => '500 Ton'],
            ['nama' => 'Press Machine 02', 'gsph' => 150, 'struk' => 6, 'tonase' => '800 Ton'],
            ['nama' => 'Press Machine 03', 'gsph' => 100, 'struk' => 3, 'tonase' => '300 Ton'],
            ['nama' => 'Stamping Line A', 'gsph' => 200, 'struk' => 8, 'tonase' => '1000 Ton'],
            ['nama' => 'Stamping Line B', 'gsph' => 180, 'struk' => 6, 'tonase' => '750 Ton'],
        ];

        foreach ($mesinData as $mesin) {
            Mesin::create($mesin);
        }
    }
}