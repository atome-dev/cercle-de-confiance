<?php

namespace Database\Seeders;

use App\Models\Cartouche;
use Illuminate\Database\Seeder;

class CartoucheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cartouche::create([
            'icone' => '🤝',
            'titre' => 'Écoute confidentielle',
            'description' => 'Nous vous offrons une écoute professionnelle et bienveillante pour vous aider à traverser les moments difficiles. Notre équipe, composée de personnes engagées et expérimentées, est entièrement dédiée à vous apporter le meilleur soutien possible.',
        ]);

        Cartouche::create([
            'icone' => '⚖️',
            'titre' => 'Neutralité & impartialité',
            'description' => 'En toute indépendance, nous vous apportons un regard objectif sur votre situation, sans jugement ni a priori.',
        ]);

        Cartouche::create([
            'icone' => '💬',
            'titre' => 'Médiation bienveillante',
            'description' => 'Nous facilitons le dialogue entre les différentes parties prenantes pour trouver des solutions consensuelles et durables.',
        ]);
    }
}
