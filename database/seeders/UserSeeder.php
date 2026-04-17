<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nome' => 'Pry — Vendedora',
            'celular' => '11999999999',
            'email' => 'pry@tudodapry.com.br',
            'role' => 'vendedor',
            'ativo' => true,
        ]);

        User::create([
            'nome' => 'Ana Silva',
            'celular' => '11988881111',
            'role' => 'cliente',
            'ativo' => true,
        ]);

        User::create([
            'nome' => 'Beatriz Costa',
            'celular' => '11977772222',
            'role' => 'cliente',
            'ativo' => true,
        ]);

        User::create([
            'nome' => 'Carla Souza',
            'celular' => '11966663333',
            'role' => 'cliente',
            'ativo' => true,
        ]);
    }
}
