<?php

namespace App\Http\Livewire;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Term;
use App\Models\Flashcard;
use App\Models\Language;
use App\Models\WordCluster;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class TeacherDashboard extends Component
{
    use WithFileUploads;

    public $courseTitle;
    public $file; // CSV/Excel upload
    public $courseId;
    public $lessonId;

    protected $rules = [
        'courseTitle' => 'required|string|max:255',
    ];

    public function render()
    {
        $courses = Course::where('teacher_id', auth()->id())->with('lessons')->get();
        return view('livewire.teacher-dashboard', compact('courses'));
    }

    public function createCourse()
    {
        $this->validate();
        Course::create(['title' => $this->courseTitle, 'teacher_id' => auth()->id()]);
        $this->courseTitle = '';
        $this->dispatch('saved');
    }

    public function importExcel()
    {
        if (! $this->file) return $this->addError('file', 'Upload an Excel/CSV file first.');

        $count = 0;

        try {
            // Read file into array (works for xlsx and csv via maatwebsite/excel)
            $sheets = Excel::toArray([], $this->file);
            if (empty($sheets) || ! isset($sheets[0])) {
                return $this->addError('file', 'No data found in file.');
            }

            $rows = $sheets[0];

            // Normalize header detection
            $header = null;
            foreach ($rows as $i => $row) {
                // detect header if first row contains string keys
                if ($i === 0) {
                    $possible = array_map(function ($v) {
                        return is_string($v) ? trim($v) : $v;
                    }, $row);
                    // if header contains 'дума' or 'обяснение' treat it as header
                    $lower = array_map(function ($v) {
                        return is_string($v) ? mb_strtolower(trim($v)) : $v;
                    }, $possible);
                    if (in_array('дума', $lower, true) || in_array('обяснение', $lower, true) || in_array('word', $lower, true)) {
                        $header = $possible;
                        continue; // header row consumed
                    }
                }

                // Build associative row
                if ($header) {
                    $assoc = [];
                    foreach ($header as $k => $colName) {
                        $assoc[trim($colName)] = isset($row[$k]) ? $row[$k] : null;
                    }
                } else {
                    // If no header, try fixed columns: [word, definition]
                    $assoc = [0 => $row[0] ?? null, 1 => $row[1] ?? null];
                }

                // Support columns 'дума' and 'обяснение' (Bulgarian)
                $bgWord = $assoc['дума'] ?? $assoc['word'] ?? $assoc[0] ?? null;
                $nlDef = $assoc['обяснение'] ?? $assoc['definition'] ?? $assoc[1] ?? null;

                if (! $bgWord) continue;

                // Normalize strings to UTF-8, trim
                $bgWord = trim((string) $bgWord);
                $nlDef = $nlDef !== null ? trim((string)$nlDef) : null;

                // Ensure languages exist
                $bgLang = Language::firstOrCreate(['code' => 'bg'], ['name' => 'Bulgarian']);
                $nlLang = Language::firstOrCreate(['code' => 'nl'], ['name' => 'Dutch']);

                // Skip if a flashcard with this bg word already exists in this lesson (if lessonId provided)
                if ($this->lessonId) {
                    $exists = Flashcard::where('lesson_id', $this->lessonId)
                        ->whereHas('term', function ($q) use ($bgWord, $bgLang) {
                            $q->where('word', $bgWord)->where('language_id', $bgLang->id);
                        })->exists();
                    if ($exists) continue;
                }

                // If the spreadsheet contains a lesson column, create/find that lesson
                $lessonName = null;
                foreach ($assoc as $k => $v) {
                    if (is_string($k) && mb_strtolower(trim($k)) === 'lesson') {
                        $lessonName = $v;
                        break;
                    }
                }

                $targetLessonId = null;
                if ($lessonName && trim((string)$lessonName) !== '') {
                    $lessonName = trim((string)$lessonName);
                    // Ensure we have a valid course_id because lessons.course_id is NOT NULL in SQLite
                    $courseIdForLesson = $this->courseId;
                    if (! $courseIdForLesson) {
                        $importCourse = Course::firstOrCreate([
                            'title' => 'Imported',
                            'teacher_id' => auth()->id(),
                        ]);
                        $courseIdForLesson = $importCourse->id;
                    }

                    $lesson = Lesson::firstOrCreate([
                        'title' => $lessonName,
                        'course_id' => $courseIdForLesson,
                    ], [
                        'notes' => null,
                    ]);
                    $targetLessonId = $lesson->id;
                } elseif ($this->lessonId) {
                    $targetLessonId = $this->lessonId;
                }

                // Use provided category if present, otherwise default to the word
                $category = $assoc['category'] ?? $assoc['Category'] ?? null;
                $clusterTitle = $category ? trim((string)$category) : $bgWord;

                // Create word clusters per language
                $clusterBg = WordCluster::firstOrCreate(['title' => $clusterTitle, 'language_id' => $bgLang->id]);
                $clusterNl = WordCluster::firstOrCreate(['title' => $nlDef ?? $clusterTitle, 'language_id' => $nlLang->id]);

                // Create terms (bg and nl counterpart)
                $termBg = Term::firstOrCreate([
                    'word' => $bgWord,
                    'language_id' => $bgLang->id,
                ], [
                    'definition' => $nlDef,
                    'word_cluster_id' => $clusterBg->id,
                ]);

                $termNl = Term::firstOrCreate([
                    'word' => $nlDef ?? $bgWord,
                    'language_id' => $nlLang->id,
                ], [
                    'definition' => $bgWord,
                    'word_cluster_id' => $clusterNl->id,
                ]);

                // Create flashcard for Bulgarian term and attach to lesson if set (either from file or selected)
                $flashcardAttrs = ['term_id' => $termBg->id];
                if ($targetLessonId) $flashcardAttrs['lesson_id'] = $targetLessonId;
                $flash = Flashcard::firstOrCreate($flashcardAttrs, [
                    'teacher_id' => auth()->id(),
                    'mastery' => 0,
                    'seen_count' => 0,
                ]);

                $count++;
            }

            $this->file = null;
            $this->dispatch('imported', $count);
        } catch (\Throwable $e) {
            $this->addError('file', 'Import failed: ' . $e->getMessage());
        }
    }
}
