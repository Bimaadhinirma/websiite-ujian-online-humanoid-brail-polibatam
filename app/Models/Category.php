<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'name',
        'order',
        'descriptions',
    ];

    /**
     * Get the period that owns this category
     */
    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Get all questions for this category
     */
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Calculate total grade (bobot) for this category
     */
    public function getTotalGrade()
    {
        return $this->questions()->sum('grade');
    }

    /**
     * Calculate user's grade for this category
     */
    public function calculateUserGrade(UserAnswer $userAnswer)
    {
        $totalGrade = 0;
        $earnedGrade = 0;

        foreach ($this->questions as $question) {
            $totalGrade += $question->grade;

            // Find user's answer for this question
            $userAnswerItem = $userAnswer->answerItems()
                ->where('question_id', $question->id)
                ->first();

            if ($userAnswerItem && $userAnswerItem->isCorrect()) {
                $earnedGrade += $question->grade;
            }
        }

        return [
            'category_name' => $this->name,
            'total_grade' => $totalGrade,
            'earned_grade' => $earnedGrade,
            'percentage' => $totalGrade > 0 ? ($earnedGrade / $totalGrade) * 100 : 0,
            'correct_answers' => $earnedGrade > 0 ? ($earnedGrade / ($totalGrade > 0 ? $totalGrade : 1)) * $this->questions->count() : 0,
            'total_questions' => $this->questions->count()
        ];
    }

}
