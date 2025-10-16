<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnswerKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'question_option_id',
        'key',
    ];

    /**
     * Get the question that owns this answer key
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the question option for this answer key
     */
    public function questionOption()
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
