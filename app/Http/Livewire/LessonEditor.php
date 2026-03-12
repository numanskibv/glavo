<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\WordCluster;
use App\Models\Language;
use App\Models\Lesson;
use App\Models\Term;
use App\Models\Flashcard;
use Illuminate\Support\Facades\Validator;

class LessonEditor extends Component
{
    use WithFileUploads;
    public $lessonId;
    public $lesson;
    public $flashcards = [];

    public $newWord = '';
    public $newDefinition = '';
    public $newLanguage = 'bg';
    public $file;

    public $searchTerm = '';
    public $searchResults = [];

    public function mount($id)
    {
        $this->lessonId = $id;
        $this->loadLesson();
    }

    protected function loadLesson()
    {
        $this->lesson = Lesson::with('course')->findOrFail($this->lessonId);
        $this->flashcards = Flashcard::with('term')->where('lesson_id', $this->lessonId)->get();
    }

    public function addNewTerm()
    {
        $data = ['word' => trim($this->newWord), 'definition' => trim($this->newDefinition), 'language' => $this->newLanguage];
        $v = Validator::make($data, ['word' => 'required|string|max:255', 'definition' => 'nullable|string|max:1000', 'language' => 'required|string']);
        if ($v->fails()) {
            $this->addError('newTerm', $v->errors()->first());
            return;
        }

        $lang = Language::firstOrCreate(['code' => $this->newLanguage], ['name' => strtoupper($this->newLanguage)]);

        $term = Term::firstOrCreate([
            'word' => $data['word'],
            'language_id' => $lang->id,
        ], [
            'definition' => $data['definition'],
            'word_cluster_id' => null,
        ]);

        Flashcard::firstOrCreate([
            'term_id' => $term->id,
            'lesson_id' => $this->lessonId,
        ], [
            'teacher_id' => auth()->id(),
            'mastery' => 0,
            'seen_count' => 0,
        ]);

        $this->newWord = $this->newDefinition = '';
        $this->resetErrorBag();
        $this->loadLesson();
    }

    public function searchTerms()
    {
        $q = trim($this->searchTerm);
        if ($q === '') {
            $this->searchResults = [];
            return;
        }
        $this->searchResults = Term::where('word', 'like', "%{$q}%")->limit(10)->get();
    }

    public function attachTerm($termId)
    {
        $term = Term::findOrFail($termId);
        Flashcard::firstOrCreate([
            'term_id' => $term->id,
            'lesson_id' => $this->lessonId,
        ], [
            'teacher_id' => auth()->id(),
            'mastery' => 0,
            'seen_count' => 0,
        ]);
        $this->searchTerm = '';
        $this->searchResults = [];
        $this->loadLesson();
    }

    public function removeFlashcard($id)
    {
        $f = Flashcard::findOrFail($id);
        $f->delete();
        $this->loadLesson();
    }

    public function render()
    {
        $languages = Language::all();
        return view('livewire.lesson-editor', compact('languages'));
    }

    public function importExcel()
    {
        if (! $this->file) {
            $this->addError('file', 'Upload een Excel of CSV bestand.');
            return;
        }

        try {
            $sheets = Excel::toArray([], $this->file);
            if (empty($sheets) || ! isset($sheets[0])) {
                $this->addError('file', 'Geen data gevonden in bestand.');
                return;
            }

            $rows = $sheets[0];
            $header = null;
            $count = 0;

            foreach ($rows as $i => $row) {
                if ($i === 0) {
                    $possible = array_map(function ($v) {
                        return is_string($v) ? trim($v) : $v;
                    }, $row);
                    $lower = array_map(function ($v) {
                        return is_string($v) ? mb_strtolower(trim($v)) : $v;
                    }, $possible);
                    if (in_array('дума', $lower, true) || in_array('обяснение', $lower, true) || in_array('word', $lower, true)) {
                        $header = $possible;
                        continue;
                    }
                }

                if ($header) {
                    $assoc = [];
                    foreach ($header as $k => $colName) {
                        $assoc[trim($colName)] = isset($row[$k]) ? $row[$k] : null;
                    }
                } else {
                    $assoc = [0 => $row[0] ?? null, 1 => $row[1] ?? null];
                }

                $bgWord = $assoc['дума'] ?? $assoc['word'] ?? $assoc[0] ?? null;
                $nlDef = $assoc['обяснение'] ?? $assoc['definition'] ?? $assoc[1] ?? null;
                if (! $bgWord) continue;

                $bgWord = trim((string)$bgWord);
                $nlDef = $nlDef !== null ? trim((string)$nlDef) : null;

                $bgLang = Language::firstOrCreate(['code' => 'bg'], ['name' => 'Bulgarian']);

                $exists = Flashcard::where('lesson_id', $this->lessonId)
                    ->whereHas('term', function ($q) use ($bgWord, $bgLang) {
                        $q->where('word', $bgWord)->where('language_id', $bgLang->id);
                    })->exists();
                if ($exists) continue;

                $cluster = WordCluster::firstOrCreate(['title' => $bgWord, 'language_id' => $bgLang->id]);
                $term = Term::firstOrCreate([
                    'word' => $bgWord,
                    'language_id' => $bgLang->id,
                ], [
                    'definition' => $nlDef,
                    'word_cluster_id' => $cluster->id,
                ]);

                Flashcard::firstOrCreate([
                    'term_id' => $term->id,
                    'lesson_id' => $this->lessonId,
                ], [
                    'teacher_id' => auth()->id(),
                    'mastery' => 0,
                    'seen_count' => 0,
                ]);

                $count++;
            }

            $this->file = null;
            $this->loadLesson();
            $this->dispatch('imported', $count);
        } catch (\Throwable $e) {
            $this->addError('file', 'Import mislukt: ' . $e->getMessage());
        }
    }
}
