<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\TeacherDashboard;
use App\Http\Livewire\FlashcardSwipe;
use App\Http\Livewire\CourseManager;
use App\Http\Livewire\LessonEditor;

Route::get('/', function () {
    $courses = \App\Models\Course::with('lessons')->get();
    return view('welcome', compact('courses'));
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/')->name('dashboard');

    // Teacher dashboard (requires auth & teacher role in real app)
    Route::get('/teacher', TeacherDashboard::class)->name('teacher.dashboard')->middleware(\App\Http\Middleware\EnsureTeacher::class);
    Route::get('/teacher/courses', CourseManager::class)->name('teacher.courses')->middleware(\App\Http\Middleware\EnsureTeacher::class);
    Route::get('/learn', FlashcardSwipe::class)->name('learn.flashcards');
    Route::get('/learn/les/{lessonId}', FlashcardSwipe::class)->name('learn.lesson');
    Route::get('/learn/cursus/{courseId}', FlashcardSwipe::class)->name('learn.course');
    Route::get('/teacher/lessons/{id}', LessonEditor::class)->name('teacher.lessons.edit')->middleware(\App\Http\Middleware\EnsureTeacher::class);

    Route::post('/learn/les/{lessonId}/reset', function ($lessonId) {
        $flashcardIds = \App\Models\Flashcard::where('lesson_id', $lessonId)->pluck('id');

        \App\Models\FlashcardAttempt::whereIn('flashcard_id', $flashcardIds)
            ->where('user_id', auth()->id())
            ->delete();

        \App\Models\Flashcard::where('lesson_id', $lessonId)->update([
            'mastery'        => 0,
            'seen_count'     => 0,
            'next_review_at' => null,
        ]);

        return back()->with('reset_lesson', $lessonId);
    })->name('learn.lesson.reset');
});

require __DIR__ . '/settings.php';
