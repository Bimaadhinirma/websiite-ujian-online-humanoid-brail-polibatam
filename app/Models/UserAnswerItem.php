<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAnswerItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_answer_id',
        'question_id',
        'question_option_id',
        'answer',
    ];

    /**
     * Get the user answer that owns this item
     */
    public function userAnswer()
    {
        return $this->belongsTo(UserAnswer::class);
    }

    /**
     * Get the question for this answer item
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the question option for this answer item
     */
    public function questionOption()
    {
        return $this->belongsTo(QuestionOption::class);
    }

    /**
     * Check if this answer is correct
     */
    public function isCorrect()
    {
        $answerKey = $this->question->answerKey;
        
        if (!$answerKey) {
            return false;
        }

        if ($this->question->type === 'options') {
            return $this->question_option_id == $answerKey->question_option_id;
        } else {
            // Normalization: lowercase, remove punctuation, and remove ALL whitespace
            // so that differences in case or extra spaces don't affect matching.
            $normalize = function ($s) {
                if (is_null($s)) return '';
                $s = (string) $s;
                $s = mb_strtolower($s, 'UTF-8');
                // remove anything that's not a letter or number (keeps unicode letters/numbers)
                $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s);
                return $s;
            };

            $a = $normalize($this->answer);
            $b = $normalize($answerKey->key);

            // if either becomes empty after normalization, fail
            if ($a === '' || $b === '') {
                return false;
            }

            // Use similar_text to compute percent similarity on the compact forms
            $percent = 0;
            similar_text($a, $b, $percent);

            // If similarity is 88.8% or greater, accept as correct
            if ($percent >= 88.8) {
                return true;
            }

            return false;
        }
    }
}
