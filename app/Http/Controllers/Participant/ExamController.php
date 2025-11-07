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

            // If userAnswer exists but orders are missing, populate them so reloads don't reshuffle
            if ($userAnswer && (empty($userAnswer->category_order) || empty($userAnswer->question_order) || empty($userAnswer->options_order))) {
                $categoryOrder = [];
                $questionOrder = [];
                $optionsOrder = [];

                $categories = $period->categories->sortBy('order')->values();
                if ($period->is_random_questions) {
                    $categories = $categories->shuffle();
                }

                foreach ($categories as $category) {
                    $categoryOrder[] = $category->id;

                    $questions = $category->questions->sortBy('order')->values();
                    if ($period->is_random_questions) {
                        $questions = $questions->shuffle();
                    }

                    $qIds = [];
                    foreach ($questions as $question) {
                        $qIds[] = $question->id;

                        $opts = $question->options->sortBy('order')->values();
                        if ($period->is_random_options) {
                            $opts = $opts->shuffle();
                        }
                        $optionsOrder[$question->id] = $opts->pluck('id')->all();
                    }

                    $questionOrder[$category->id] = $qIds;
                }

                $userAnswer->category_order = $categoryOrder;
                $userAnswer->question_order = $questionOrder;
                $userAnswer->options_order = $optionsOrder;
                $userAnswer->save();
            }

            return view('participant.exam.start', compact('period', 'userAnswer'));
        }

        // No password required: create or get user answer (record start_ip and heartbeat time)
        $userAnswer = $existingAnswer ?? UserAnswer::create([
            'user_id' => Auth::id(),
            'period_id' => $periodId,
            'status' => false,
            'start_ip' => request()->ip(),
            'last_heartbeat_at' => now()
        ]);

        // Ensure we persist a deterministic order for categories/questions/options so reloads don't reshuffle
        if (empty($userAnswer->category_order) || empty($userAnswer->question_order) || empty($userAnswer->options_order)) {
            $categoryOrder = [];
            $questionOrder = []; // map category_id => [question_id,...]
            $optionsOrder = [];  // map question_id => [option_id,...]

            // Start from canonical category list
            $categories = $period->categories->sortBy('order')->values();

            // If period randomizes questions, shuffle categories (so category order is randomized too)
            if ($period->is_random_questions) {
                $categories = $categories->shuffle();
            }

            foreach ($categories as $category) {
                $categoryOrder[] = $category->id;

                $questions = $category->questions->sortBy('order')->values();
                if ($period->is_random_questions) {
                    $questions = $questions->shuffle();
                }

                $qIds = [];
                foreach ($questions as $question) {
                    $qIds[] = $question->id;

                    $opts = $question->options->sortBy('order')->values();
                    if ($period->is_random_options) {
                        $opts = $opts->shuffle();
                    }
                    $optionsOrder[$question->id] = $opts->pluck('id')->all();
                }

                $questionOrder[$category->id] = $qIds;
            }

            $userAnswer->category_order = $categoryOrder;
            $userAnswer->question_order = $questionOrder;
            $userAnswer->options_order = $optionsOrder;
            $userAnswer->save();
        }

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
            'status' => false,
            'start_ip' => $request->ip(),
            'last_heartbeat_at' => now()
        ]);

        // Persist deterministic order when creating userAnswer or when orders missing
        if (empty($userAnswer->category_order) || empty($userAnswer->question_order) || empty($userAnswer->options_order)) {
            $categoryOrder = [];
            $questionOrder = [];
            $optionsOrder = [];

            $categories = $period->categories->sortBy('order')->values();
            if ($period->is_random_questions) {
                $categories = $categories->shuffle();
            }

            foreach ($categories as $category) {
                $categoryOrder[] = $category->id;

                $questions = $category->questions->sortBy('order')->values();
                if ($period->is_random_questions) {
                    $questions = $questions->shuffle();
                }

                $qIds = [];
                foreach ($questions as $question) {
                    $qIds[] = $question->id;

                    $opts = $question->options->sortBy('order')->values();
                    if ($period->is_random_options) {
                        $opts = $opts->shuffle();
                    }
                    $optionsOrder[$question->id] = $opts->pluck('id')->all();
                }

                $questionOrder[$category->id] = $qIds;
            }

            $userAnswer->category_order = $categoryOrder;
            $userAnswer->question_order = $questionOrder;
            $userAnswer->options_order = $optionsOrder;
            $userAnswer->save();
        }

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
            // Compute elapsed strictly from server timestamps: created_at -> ended_at
            $endedAt = now();
            $elapsed = null;

            if ($userAnswer->created_at) {
                // compute elapsed as created_at -> endedAt and ensure non-negative integer
                try {
                    $elapsed = (int) $userAnswer->created_at->diffInSeconds($endedAt);
                    if ($elapsed < 0) $elapsed = abs($elapsed);
                } catch (\Throwable $ex) {
                    // fallback: use now diff copy
                    $elapsed = (int) now()->diffInSeconds($userAnswer->created_at);
                    if ($elapsed < 0) $elapsed = abs($elapsed);
                }
            }

            // If period has duration_minutes, clamp elapsed to not exceed it
            $periodDuration = $userAnswer->period?->duration_minutes ?? null;
            if (!is_null($elapsed) && !is_null($periodDuration) && is_numeric($periodDuration)) {
                $max = intval($periodDuration) * 60;
                if ($elapsed > $max) $elapsed = $max;
            }

            if (!is_null($elapsed)) {
                $userAnswer->elapsed_seconds = intval($elapsed);
            }
            $userAnswer->ended_at = $endedAt;
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

    /**
     * Heartbeat endpoint for client to report connectivity; server may instruct auto-submit if IP changed or heartbeat gap is large.
     */
    public function heartbeat(Request $request, $userAnswerId)
    {
        $userAnswer = UserAnswer::where('id', $userAnswerId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // If already submitted
        if ($userAnswer->status) {
            return response()->json([
                'submitted' => true,
                'submit' => false,
                'redirect' => route('participant.exam.result', $userAnswer->id)
            ]);
        }

        $now = now();
        $currentIp = $request->ip();

        // Initialize start_ip if not set
        if (empty($userAnswer->start_ip)) {
            $userAnswer->start_ip = $currentIp;
        }

        // compute gap since last heartbeat
        $gap = $userAnswer->last_heartbeat_at ? $now->diffInSeconds($userAnswer->last_heartbeat_at) : 0;

        // Update last_heartbeat_at
        $userAnswer->last_heartbeat_at = $now;
        $userAnswer->save();

        $thresholdSeconds = 10; // tune as needed

        if ($userAnswer->start_ip !== $currentIp || $gap > $thresholdSeconds) {
            return response()->json(['submit' => true, 'reason' => ($userAnswer->start_ip !== $currentIp) ? 'ip_changed' : 'heartbeat_gap']);
        }

        return response()->json(['submit' => false]);
    }
}
