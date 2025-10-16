<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\AnswerKey;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->get('category_id');
        $questions = Question::with(['category.period', 'options', 'answerKey'])
            ->when($categoryId, function($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->latest()
            ->get();
        $categories = Category::with('period')->get();
        return view('admin.questions.index', compact('questions', 'categories', 'categoryId'));
    }

    public function create()
    {
        $categories = Category::with('period')->get();
        return view('admin.questions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Custom validation messages
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'question' => 'required|string',
            'order' => 'required|integer',
            'type' => 'required|in:input,options',
                'grade' => 'required|integer|min:1',
                'image' => 'nullable|image|max:4096',
            'options' => 'required_if:type,options|array|min:2',
            'options.*' => 'required_if:type,options|string',
            'correct_answer' => 'required',
        ], [
            'correct_answer.required' => 'Silakan pilih jawaban yang benar untuk soal ini.',
        ]);

        DB::beginTransaction();
        try {
                $questionData = [
                    'category_id' => $request->category_id,
                    'question' => $request->question,
                    'order' => $request->order,
                    'type' => $request->type,
                    'grade' => $request->grade,
                ];

                // Handle image upload
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('question_images', 'public');
                    $questionData['image'] = $path;
                }

                $question = Question::create($questionData);

            if ($request->type === 'options') {
                $correctOptionId = null;
                foreach ($request->options as $index => $optionText) {
                    $option = QuestionOption::create([
                        'question_id' => $question->id,
                        'option' => $optionText,
                        'order' => $index + 1,
                    ]);

                    // Check if this is the correct answer
                    if ($request->correct_answer == $index) {
                        $correctOptionId = $option->id;
                    }
                }

                // Create answer key with the correct option ID
                if ($correctOptionId) {
                    AnswerKey::create([
                        'question_id' => $question->id,
                        'question_option_id' => $correctOptionId,
                        'key' => (string)$correctOptionId,
                    ]);
                }
            } else {
                AnswerKey::create([
                    'question_id' => $question->id,
                    'question_option_id' => null,
                    'key' => $request->correct_answer,
                ]);
            }

            DB::commit();
            
            $category = Category::find($request->category_id);
            return redirect()->route('admin.periods.show', $category->period_id)->with('success', 'Soal berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $question = Question::with(['category', 'options', 'answerKey'])->findOrFail($id);
        return view('admin.questions.show', compact('question'));
    }

    public function edit(string $id)
    {
        $question = Question::with(['options', 'answerKey'])->findOrFail($id);
        $categories = Category::with('period')->get();
        return view('admin.questions.edit', compact('question', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        $question = Question::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'question' => 'required|string',
            'order' => 'required|integer',
            'type' => 'required|in:input,options',
            'grade' => 'required|integer|min:1',
                'image' => 'nullable|image|max:4096',
            'options' => 'required_if:type,options|array|min:2',
            'options.*' => 'required_if:type,options|string',
            'correct_answer' => 'required',
        ]);

        DB::beginTransaction();
        try {
                $updateData = [
                    'category_id' => $request->category_id,
                    'question' => $request->question,
                    'order' => $request->order,
                    'type' => $request->type,
                    'grade' => $request->grade,
                ];

                // If the client requested to remove the existing image
                if ($request->input('remove_image') == '1') {
                    if ($question->image) {
                        Storage::disk('public')->delete($question->image);
                    }
                    $updateData['image'] = null;
                }

                // If a new image uploaded, delete old and store new
                if ($request->hasFile('image')) {
                    // delete old image if exists
                    if ($question->image) {
                        Storage::disk('public')->delete($question->image);
                    }
                    $updateData['image'] = $request->file('image')->store('question_images', 'public');
                }

                $question->update($updateData);

            // Delete old options and answer key
            QuestionOption::where('question_id', $question->id)->delete();
            AnswerKey::where('question_id', $question->id)->delete();

            if ($request->type === 'options') {
                $correctOptionId = null;
                foreach ($request->options as $index => $optionText) {
                    $option = QuestionOption::create([
                        'question_id' => $question->id,
                        'option' => $optionText,
                        'order' => $index + 1,
                    ]);

                    // Check if this is the correct answer
                    if ($request->correct_answer == $index) {
                        $correctOptionId = $option->id;
                    }
                }

                // Create answer key with the correct option ID
                if ($correctOptionId) {
                    AnswerKey::create([
                        'question_id' => $question->id,
                        'question_option_id' => $correctOptionId,
                        'key' => (string)$correctOptionId,
                    ]);
                }
            } else {
                AnswerKey::create([
                    'question_id' => $question->id,
                    'question_option_id' => null,
                    'key' => $request->correct_answer,
                ]);
            }

            DB::commit();
            
            $category = Category::find($request->category_id);
            return redirect()->route('admin.periods.show', $category->period_id)->with('success', 'Soal berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $question = Question::findOrFail($id);
        $periodId = $question->category->period_id;
            // delete image file if exists
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }
            $question->delete();

        return redirect()->route('admin.periods.show', $periodId)->with('success', 'Soal berhasil dihapus');
    }
}
