<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendedor = User::where('role', 'vendedor')->first();

        $produtos = [
            [
                'nome' => 'Bolsa Couro Caramelo',
                'descricao' => 'Bolsa feminina em couro legítimo, cor caramelo, com alça removível.',
                'preco' => 289.90,
                'estoque' => 5,
                'categoria' => 'bolsas',
            ],
            [
                'nome' => 'Bolsa Retrô Vinho',
                'descricao' => 'Bolsa estilo retrô em couro sintético premium, cor vinho.',
                'preco' => 179.90,
                'estoque' => 8,
                'categoria' => 'bolsas',
            ],
            [
                'nome' => 'Sapato Scarpin Nude',
                'descricao' => 'Scarpin clássico em couro envernizado, salto 7cm, cor nude.',
                'preco' => 219.90,
                'estoque' => 6,
                'categoria' => 'sapatos',
            ],
            [
                'nome' => 'Sandália Plataforma Preta',
                'descricao' => 'Sandália plataforma em couro sintético, solado de 4cm.',
                'preco' => 159.90,
                'estoque' => 10,
                'categoria' => 'sapatos',
            ],
            [
                'nome' => 'Perfume Floral 100ml',
                'descricao' => 'Fragrância floral com notas de rosa, jasmim e baunilha. 100ml.',
                'preco' => 129.90,
                'estoque' => 15,
                'categoria' => 'perfumes',
            ],
            [
                'nome' => 'Perfume Oriental 50ml',
                'descricao' => 'Fragrância oriental intensa com notas de âmbar e sândalo. 50ml.',
                'preco' => 99.90,
                'estoque' => 12,
                'categoria' => 'perfumes',
            ],
            [
                'nome' => 'Relógio Feminino Dourado',
                'descricao' => 'Relógio analógico feminino com pulseira em aço inox dourado.',
                'preco' => 349.90,
                'estoque' => 4,
                'categoria' => 'relogios',
            ],
            [
                'nome' => 'Relógio Minimalista Rosé',
                'descricao' => 'Relógio de pulso minimalista com caixa 32mm e pulseira rosé.',
                'preco' => 249.90,
                'estoque' => 7,
                'categoria' => 'relogios',
            ],
        ];

        foreach ($produtos as $produto) {
            Product::create(array_merge($produto, [
                'ativo' => true,
                'created_by' => $vendedor->id,
            ]));
        }
    }
}
