<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option',
        'order',
        'image', // path gambar opsi
    ];

    /**
     * Get the question that owns this option
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the answer key for this option
     */
    public function answerKey()
    {
        return $this->hasOne(AnswerKey::class);
    }

    /**
     * Get all user answer items for this option
     */
    public function userAnswerItems()
    {
        return $this->hasMany(UserAnswerItem::class);
    }
}
