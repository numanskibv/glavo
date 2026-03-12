<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = ['term_id', 'lesson_id', 'teacher_id', 'mastery', 'seen_count', 'next_review_at'];
    protected $casts = [
        'next_review_at' => 'datetime',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function attempts()
    {
        return $this->hasMany(FlashcardAttempt::class);
    }

    public function markCorrect()
    {
        $this->increment('seen_count');
        $this->increment('mastery');
        // schedule next review 24 hours later
        $this->next_review_at = now()->addDay();
        $this->save();
    }

    public function markWrong()
    {
        $this->increment('seen_count');
        $this->decrement('mastery');
        if ($this->mastery < 0) $this->mastery = 0;
        // review immediately
        $this->next_review_at = now();
        $this->save();
    }
}
