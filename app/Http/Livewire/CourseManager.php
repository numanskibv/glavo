<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Validator;

class CourseManager extends Component
{
    public $courses;
    public $newTitle;
    public $editingId = null;
    public $editingTitle = '';
    public $lessonInputs = [];

    protected $rules = [
        'newTitle' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->loadCourses();
    }

    public function loadCourses()
    {
        $this->courses = Course::where('teacher_id', auth()->id())->with('lessons')->get();
    }

    public function createCourse()
    {
        $this->validate();
        Course::create(['title' => $this->newTitle, 'teacher_id' => auth()->id()]);
        $this->newTitle = '';
        $this->loadCourses();
    }

    public function startEdit($id)
    {
        $c = Course::findOrFail($id);
        $this->editingId = $id;
        $this->editingTitle = $c->title;
    }

    public function updateCourse()
    {
        if (! $this->editingId) return;
        $c = Course::findOrFail($this->editingId);
        $c->update(['title' => $this->editingTitle]);
        $this->editingId = null;
        $this->editingTitle = '';
        $this->loadCourses();
    }

    public function deleteCourse($id)
    {
        $c = Course::findOrFail($id);
        $c->delete();
        $this->loadCourses();
    }

    public function addLesson($courseId)
    {
        $title = isset($this->lessonInputs[$courseId]) ? trim($this->lessonInputs[$courseId]) : '';
        $validator = Validator::make(['title' => $title], ['title' => 'required|string|max:255']);
        if ($validator->fails()) {
            $this->addError('lesson_' . $courseId, $validator->errors()->first('title'));
            return;
        }

        Lesson::create(['course_id' => $courseId, 'title' => $title]);
        $this->resetErrorBag();
        $this->lessonInputs[$courseId] = '';
        $this->loadCourses();
    }

    public function deleteLesson($id)
    {
        $l = Lesson::findOrFail($id);
        $l->delete();
        $this->loadCourses();
    }

    public function render()
    {
        return view('livewire.course-manager');
    }
}
