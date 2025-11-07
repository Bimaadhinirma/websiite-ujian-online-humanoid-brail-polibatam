<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_id',
        'status',
        'elapsed_seconds',
        'ended_at',
        'category_order',
        'question_order',
        'options_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'ended_at' => 'datetime',
        'elapsed_seconds' => 'integer',
        'category_order' => 'array',
        'question_order' => 'array',
        'options_order' => 'array',
    ];

    /**
     * Get the user that owns this answer
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the period for this answer
     */
    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Get all answer items
     */
    public function answerItems()
    {
        return $this->hasMany(UserAnswerItem::class);
    }

    /**
     * Calculate total grade for this user answer
     */
    public function calculateGrade()
    {
        $totalGrade = 0;
        $earnedGrade = 0;

        // Sum total grade across all questions in the period
        foreach ($this->period->categories as $category) {
            foreach ($category->questions as $question) {
                $totalGrade += $question->grade;

                // find user's answer for this question
                $userAnswerItem = $this->answerItems->where('question_id', $question->id)->first();
                if ($userAnswerItem && $userAnswerItem->isCorrect()) {
                    $earnedGrade += $question->grade;
                }
            }
        }

        return [
            'total' => $totalGrade,
            'earned' => $earnedGrade,
            'percentage' => $totalGrade > 0 ? ($earnedGrade / $totalGrade) * 100 : 0
        ];
    }

    /**
     * Calculate grade by category
     */
    public function calculateGradeByCategory()
    {
        $categories = $this->period->categories;
        $result = [];

        foreach ($categories as $category) {
            $totalGrade = 0;
            $earnedGrade = 0;

            // Get all questions in this category
            foreach ($category->questions as $question) {
                $totalGrade += $question->grade;

                // Find user's answer for this question
                $userAnswerItem = $this->answerItems()
                    ->where('question_id', $question->id)
                    ->first();

                if ($userAnswerItem) {
                    // Use item's isCorrect() which contains the tolerant matching logic
                    if ($userAnswerItem->isCorrect()) {
                        $earnedGrade += $question->grade;
                    }
                }
            }

            $result[] = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'total_grade' => $totalGrade,
                'earned_grade' => $earnedGrade,
                'percentage' => $totalGrade > 0 ? ($earnedGrade / $totalGrade) * 100 : 0
            ];
        }

        return $result;
    }

    /**
     * Get detailed result with category breakdown
     */
    public function getDetailedResult()
    {
        $overallGrade = $this->calculateGrade();
        $categoryGrades = $this->calculateGradeByCategory();

        // Maintain existing structure
        $result = [
            'overall' => $overallGrade,
            'by_category' => $categoryGrades,
        ];

        // Backwards-compatible keys expected by some admin views
        // totalScore = earned points, totalQuestions = total possible points
        $result['totalScore'] = $overallGrade['earned'] ?? 0;
        $result['totalQuestions'] = $overallGrade['total'] ?? 0;

        // Map categoryGrades to the shape some blades expect: [category, score, total, percentage]
        $mappedCategoryGrades = [];
        foreach ($categoryGrades as $cg) {
            $mappedCategoryGrades[] = [
                'category' => $cg['category_name'] ?? ($cg['category'] ?? 'Kategori'),
                'score' => $cg['earned_grade'] ?? ($cg['score'] ?? 0),
                'total' => $cg['total_grade'] ?? ($cg['total'] ?? 0),
                'percentage' => $cg['percentage'] ?? 0,
            ];
        }

        $result['categoryGrades'] = $mappedCategoryGrades;

        return $result;
    }

}
