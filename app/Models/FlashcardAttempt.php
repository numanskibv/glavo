<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashcardAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['flashcard_id', 'user_id', 'correct', 'notes'];

    public function flashcard()
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
