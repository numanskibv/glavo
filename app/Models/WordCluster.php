<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordCluster extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'language_id'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function terms()
    {
        return $this->hasMany(Term::class);
    }
}
