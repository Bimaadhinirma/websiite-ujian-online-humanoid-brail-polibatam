<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'question',
        'order',
        'type',
        'grade',
        'image',
    ];

    /**
     * Get the category that owns this question
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all options for this question
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    /**
     * Get the answer key for this question
     */
    public function answerKey()
    {
        return $this->hasOne(AnswerKey::class);
    }

    /**
     * Get all user answer items for this question
     */
    public function userAnswerItems()
    {
        return $this->hasMany(UserAnswerItem::class);
    }
}
