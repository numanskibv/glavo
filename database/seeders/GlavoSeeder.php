<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\WordCluster;
use App\Models\Term;
use App\Models\Flashcard;

class GlavoSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure UTF-8 on MySQL only (SQLite doesn't support SET NAMES)
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
            \DB::statement("SET NAMES 'utf8mb4'");
        }

        $bg = Language::firstOrCreate(['code' => 'bg'], ['name' => 'Bulgarian']);
        $nl = Language::firstOrCreate(['code' => 'nl'], ['name' => 'Dutch']);

        $categories = [
            ['bg' => 'Поздрави', 'nl' => 'Groeten', 'terms' => [
                ['bg' => 'Добро утро', 'nl' => 'Goedemorgen'],
                ['bg' => 'Здравей', 'nl' => 'Hallo']
            ]],
            ['bg' => 'Храна', 'nl' => 'Eten', 'terms' => [
                ['bg' => 'ябълка', 'nl' => 'appel'],
                ['bg' => 'хляб', 'nl' => 'brood']
            ]],
            ['bg' => 'Семейство', 'nl' => 'Familie', 'terms' => [
                ['bg' => 'майка', 'nl' => 'moeder'],
                ['bg' => 'баща', 'nl' => 'vader'],
                ['bg' => 'куче', 'nl' => 'hond'],
                ['bg' => 'котка', 'nl' => 'kat']
            ]],
        ];

        foreach ($categories as $cat) {
            $clusterBg = WordCluster::firstOrCreate([
                'title' => $cat['bg'],
                'language_id' => $bg->id,
            ]);

            $clusterNl = WordCluster::firstOrCreate([
                'title' => $cat['nl'],
                'language_id' => $nl->id,
            ]);

            foreach ($cat['terms'] as $t) {
                // Bulgarian term
                $termBg = Term::firstOrCreate([
                    'word' => $t['bg'],
                    'language_id' => $bg->id,
                ], [
                    'definition' => $t['nl'],
                    'word_cluster_id' => $clusterBg->id,
                ]);

                // Dutch counterpart
                $termNl = Term::firstOrCreate([
                    'word' => $t['nl'],
                    'language_id' => $nl->id,
                ], [
                    'definition' => $t['bg'],
                    'word_cluster_id' => $clusterNl->id,
                ]);

                // Create flashcards for Bulgarian terms (teacher_id null)
                Flashcard::firstOrCreate([
                    'term_id' => $termBg->id,
                ], [
                    'mastery' => 0,
                    'seen_count' => 0,
                ]);
            }
        }
    }
}
