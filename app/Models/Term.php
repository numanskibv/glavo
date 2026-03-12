<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = ['word_cluster_id', 'language_id', 'word', 'definition', 'transliteration'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function cluster()
    {
        return $this->belongsTo(WordCluster::class, 'word_cluster_id');
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class);
    }
}
