<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Period;
use App\Models\UserAnswer;
use App\Models\User;

class ResultController extends Controller
{
    public function index()
    {
        $periods = Period::with('userAnswers.user')->latest()->get();
        return view('admin.results.index', compact('periods'));
    }

    public function show($periodId)
    {
        $period = Period::with(['userAnswers.user', 'categories.questions'])->findOrFail($periodId);
        $userAnswers = UserAnswer::where('period_id', $periodId)
            ->with(['user', 'answerItems.question.category'])
            ->get();

        // Calculate grades for each user
        $results = [];
        foreach ($userAnswers as $userAnswer) {
            $detailedResult = $userAnswer->getDetailedResult();
            $results[] = [
                'user' => $userAnswer->user,
                'user_answer_id' => $userAnswer->id,
                'overall' => $detailedResult['overall'],
                'by_category' => $detailedResult['by_category'],
                'status' => $userAnswer->status
            ];
        }

        return view('admin.results.show', compact('period', 'results'));
    }

    public function detail($userAnswerId)
    {
        $userAnswer = UserAnswer::with([
            'user',
            'period',
            'answerItems.question.category',
            'answerItems.question.answerKey',
            'answerItems.questionOption'
        ])->findOrFail($userAnswerId);

        $detailedResult = $userAnswer->getDetailedResult();

        return view('admin.results.detail', compact('userAnswer', 'detailedResult'));
    }

    public function toggleShowResult($periodId)
    {
        $period = Period::findOrFail($periodId);
        $period->show_result = !$period->show_result;
        $period->save();

        return back()->with('success', 'Pengaturan review hasil berhasil diubah');
    }

    public function toggleShowGrade($periodId)
    {
        $period = Period::findOrFail($periodId);
        $period->show_grade = !$period->show_grade;
        $period->save();

        return back()->with('success', 'Pengaturan tampilan nilai berhasil diubah');
    }
}
