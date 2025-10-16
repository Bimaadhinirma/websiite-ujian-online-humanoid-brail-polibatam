<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Period;
use App\Models\UserAnswer;
use App\Models\UserAnswerItem;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index()
    {
        $activePeriods = Period::where('status', true)
            ->get();

        $userAnswers = UserAnswer::where('user_id', Auth::id())
            ->with('period')
            ->latest()
            ->get();

        return view('participant.exam.index', compact('activePeriods', 'userAnswers'));
    }

    public function start($periodId)
    {
        $period = Period::with(['categories.questions.options'])->findOrFail($periodId);

        // Check if period is active
        if (!$period->status) {
            return back()->with('error', 'Periode ujian tidak aktif');
        }

        // Check if user already has an answer for this period
        $existingAnswer = UserAnswer::where('user_id', Auth::id())
            ->where('period_id', $periodId)
            ->first();

        if ($existingAnswer && $existingAnswer->status) {
            return redirect()->route('participant.exam.result', $existingAnswer->id)
                ->with('info', 'Anda sudah menyelesaikan ujian ini');
        }

        // If period has password, do not auto-create userAnswer here.
        // The blade will show a modal to prompt for password, and the client will call verifyPassword to create the UserAnswer.
        if ($period->exam_password) {
            $userAnswer = $existingAnswer; // may be null
            return view('participant.exam.start', compact('period', 'userAnswer'));
        }

        // No password required: create or get user answer
        $userAnswer = $existingAnswer ?? UserAnswer::create([
            'user_id' => Auth::id(),
            'period_id' => $periodId,
            'status' => false
        ]);

        return view('participant.exam.start', compact('period', 'userAnswer'));
    }

    /**
     * Verify exam password (if set) and create/get user answer, then redirect to start view
     */
    public function verifyPassword(Request $request, $periodId)
    {
        $period = Period::findOrFail($periodId);

        // if no password set, allow
        if (empty($period->exam_password)) {
            return response()->json(['ok' => true, 'redirect' => route('participant.exam.start', $periodId)]);
        }

        $request->validate([
            'exam_password' => 'required|string',
        ]);

        // Plain-text compare (period->exam_password stored as plain text per current implementation)
        if ($request->input('exam_password') !== $period->exam_password) {
            return response()->json(['ok' => false, 'message' => 'Password ujian salah'], 422);
        }

        // password correct: create/get user answer if not exist
        $existingAnswer = UserAnswer::where('user_id', Auth::id())
            ->where('period_id', $periodId)
            ->first();

        $userAnswer = $existingAnswer ?? UserAnswer::create([
            'user_id' => Auth::id(),
            'period_id' => $periodId,
            'status' => false
        ]);

        return response()->json(['ok' => true, 'redirect' => route('participant.exam.start', $periodId)]);
    }

    

    public function submit(Request $request, $userAnswerId)
    {
        $userAnswer = UserAnswer::where('id', $userAnswerId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Delete existing answers
            UserAnswerItem::where('user_answer_id', $userAnswerId)->delete();

            // Save all answers
            foreach ($request->answers as $questionId => $answer) {
                $question = \App\Models\Question::findOrFail($questionId);

                if ($question->type === 'options') {
                    UserAnswerItem::create([
                        'user_answer_id' => $userAnswerId,
                        'question_id' => $questionId,
                        'question_option_id' => $answer,
                        'answer' => null
                    ]);
                } else {
                    UserAnswerItem::create([
                        'user_answer_id' => $userAnswerId,
                        'question_id' => $questionId,
                        'question_option_id' => null,
                        'answer' => $answer
                    ]);
                }
            }

            // Mark as completed
            $userAnswer->status = true;
            $userAnswer->save();

            DB::commit();

            return redirect()->route('participant.exam.result', $userAnswerId)
                ->with('success', 'Ujian berhasil diselesaikan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function result($userAnswerId)
    {
        $userAnswer = UserAnswer::where('id', $userAnswerId)
            ->where('user_id', Auth::id())
            ->with(['period', 'answerItems.question.category'])
            ->firstOrFail();

        $period = $userAnswer->period;
        $detailedResult = $userAnswer->getDetailedResult();

        return view('participant.exam.result', compact('userAnswer', 'period', 'detailedResult'));
    }
}
