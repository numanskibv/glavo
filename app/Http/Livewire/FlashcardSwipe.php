<?php

namespace App\Http\Livewire;

use App\Models\Flashcard;
use App\Models\FlashcardAttempt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.game')]
class FlashcardSwipe extends Component
{
    public $drawPile = [];
    public $current;
    public $contextLabel = null;
    public $backUrl = null;
    public $shownTranslation = null;
    public $isCorrectPair = true;
    public $decoyPool = [];
    public $sessionLog = [];

    public function mount($lessonId = null, $courseId = null)
    {
        $query = Flashcard::with('term');

        if ($lessonId) {
            $query->where('lesson_id', $lessonId);
            $lesson = \App\Models\Lesson::with('course')->find($lessonId);
            $this->contextLabel = $lesson ? $lesson->title : null;
        } elseif ($courseId) {
            // Load all flashcards that belong to lessons of this course
            $lessonIds = \App\Models\Lesson::where('course_id', $courseId)->pluck('id');
            $query->whereIn('lesson_id', $lessonIds);
            $course = \App\Models\Course::find($courseId);
            $this->contextLabel = $course ? $course->title : null;
        }

        $this->backUrl = url()->previous(config('app.url'));
        $flashcards = $query->orderBy('mastery', 'asc')->limit(200)->get();
        $this->drawPile = $flashcards->map->id->toArray();
        $this->decoyPool = $flashcards->pluck('term.word')->filter()->unique()->values()->toArray();
        $this->advance();
    }

    public function advance()
    {
        if (empty($this->drawPile)) {
            $this->current = null;
            return;
        }
        $id = array_shift($this->drawPile);
        $this->current = Flashcard::with('term')->find($id);

        $correctWord = $this->current->term->word;
        $others = array_values(array_filter($this->decoyPool, fn($w) => $w !== $correctWord));

        if (count($others) >= 2 && rand(0, 1) === 0) {
            $this->shownTranslation = $others[array_rand($others)];
            $this->isCorrectPair = false;
        } else {
            $this->shownTranslation = $correctWord;
            $this->isCorrectPair = true;
        }
    }

    #[On('swipeRight')]
    public function markCorrect()
    {
        if (! $this->current) return;
        // "Goed" = user thinks the shown translation IS correct → right iff isCorrectPair
        $this->recordAndAdvance($this->isCorrectPair);
    }

    #[On('swipeLeft')]
    public function markWrong()
    {
        if (! $this->current) return;
        // "Fout" = user thinks the shown translation is NOT correct → right iff !isCorrectPair
        $this->recordAndAdvance(! $this->isCorrectPair);
    }

    private function recordAndAdvance(bool $correct): void
    {
        // Capture current card state before advancing
        $this->sessionLog[] = [
            'dutch'    => $this->current->term->definition ?? $this->current->term->word,
            'correct_bg' => $this->current->term->word,
            'shown_bg'   => $this->shownTranslation,
            'was_pair'   => $this->isCorrectPair,
            'ok'         => $correct,
        ];

        FlashcardAttempt::create([
            'flashcard_id' => $this->current->id,
            'user_id' => auth()->id(),
            'correct' => $correct,
        ]);

        $user = auth()->user();

        if ($correct) {
            $this->current->markCorrect();
            if ($user) {
                $user->addXp(10);
                $user->updatePracticeStreak();
            }
        } else {
            $this->current->markWrong();
            if ($user) {
                $user->updatePracticeStreak();
            }
            // Re-insert wrong card a few positions later, not immediately
            $pos = min(3, count($this->drawPile));
            array_splice($this->drawPile, $pos, 0, [$this->current->id]);
        }

        $this->advance();
    }

    public function getEfficiencyProperty()
    {
        $attempts = FlashcardAttempt::where('user_id', auth()->id())->count();
        $correct = FlashcardAttempt::where('user_id', auth()->id())->where('correct', true)->count();
        if ($attempts === 0) return 0;
        return (int) round(($correct / $attempts) * 100);
    }

    public function render()
    {
        return view('livewire.flashcard-swipe');
    }
}
