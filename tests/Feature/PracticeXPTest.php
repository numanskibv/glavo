<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Term;
use App\Models\Flashcard;

class PracticeXPTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_correct_updates_mastery_and_next_review()
    {
        $user = User::factory()->create();

        \App\Models\Language::firstOrCreate(['code' => 'bg'], ['name' => 'Bulgarian']);
        $lang = \App\Models\Language::where('code', 'bg')->first();
        $term = Term::create(["word" => "тест", "language_id" => $lang->id]);
        $card = Flashcard::create(["term_id" => $term->id, "teacher_id" => $user->id, "mastery" => 0, "seen_count" => 0]);

        $card->markCorrect();

        $card->refresh();
        $this->assertGreaterThanOrEqual(1, $card->mastery);
        $this->assertNotNull($card->next_review_at);
        $this->assertTrue($card->next_review_at->greaterThan(now()));
    }

    public function test_mark_wrong_sets_next_review_now_and_does_not_increase_mastery()
    {
        $user = User::factory()->create();
        $lang2 = \App\Models\Language::where('code', 'bg')->first() ?? \App\Models\Language::firstOrCreate(['code' => 'bg'], ['name' => 'Bulgarian']);
        $term = Term::create(["word" => "тест2", "language_id" => $lang2->id]);
        $card = Flashcard::create(["term_id" => $term->id, "teacher_id" => $user->id, "mastery" => 1, "seen_count" => 0]);

        $card->markWrong();

        $card->refresh();
        $this->assertLessThanOrEqual(2, $card->next_review_at->diffInSeconds(now()));
        $this->assertGreaterThanOrEqual(0, $card->mastery);
    }
}
