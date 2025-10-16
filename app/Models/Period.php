<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'show_result',
        'show_grade',
        'duration_minutes',
        'exam_password',
    ];

    protected $casts = [
        'status' => 'boolean',
        'show_result' => 'boolean',
        'show_grade' => 'boolean',
    ];

    /**
     * Get all categories for this period
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get all user answers for this period
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
