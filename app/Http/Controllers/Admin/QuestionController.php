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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

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

    /**
     * Export questions for a period. Each category gets its own sheet.
     * Columns per sheet:
     * 1: order
     * 2: question text
     * 3: type (options/input) - we will provide template values for import
     * 4: options (comma separated) - only for options type
     * 5: answer key (for options: letter or index (A/1))
     */
    public function exportForPeriod($periodId)
    {
        $period = Category::where('period_id', $periodId)->with('questions.options','questions.answerKey')->get()->groupBy('id');

        $spreadsheet = new Spreadsheet();
        $sheetIndex = 0;

        $categories = Category::where('period_id', $periodId)->with('questions.options','questions.answerKey')->get();
        foreach ($categories as $category) {
            if ($sheetIndex == 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet($sheetIndex);
            }
            $sheet->setTitle(substr($category->name, 0, 30));
            // Header row
            $sheet->fromArray(['Order','Question','Type','Options (comma-separated)','Answer Key (A/B/1/2)'], null, 'A1');

            $row = 2;
            foreach ($category->questions as $q) {
                $optionsCell = '';
                if ($q->type === 'options') {
                    // format options as "A. text, B. text, C. text"
                    $parts = [];
                    foreach ($q->options as $idx => $opt) {
                        $letter = chr(65 + $idx);
                        $parts[] = $letter . '. ' . $opt->option;
                    }
                    $optionsCell = implode(', ', $parts);
                }

                $answerKey = '';
                if ($q->answerKey) {
                    if ($q->type === 'options') {
                        // try to map question_option_id to letter
                        $optIndex = $q->options->search(function($o) use ($q) { return $o->id == $q->answerKey->question_option_id; });
                        if ($optIndex !== false) {
                            $answerKey = chr(65 + $optIndex);
                        } else {
                            $answerKey = $q->answerKey->key;
                        }
                    } else {
                        $answerKey = $q->answerKey->key;
                    }
                }

                $sheet->setCellValue("A{$row}", $q->order);
                $sheet->setCellValue("B{$row}", $q->question);
                $sheet->setCellValue("C{$row}", $q->type);
                $sheet->setCellValue("D{$row}", $optionsCell);
                $sheet->setCellValue("E{$row}", $answerKey);
                $row++;
            }

            // autosize columns A..E
            foreach (range('A','E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheetIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'questions_period_' . $periodId . '_' . date('Ymd_His') . '.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    /**
     * Import questions from an Excel file for a period.
     * Expects the same layout as export. Each sheet becomes a category.
     */
    public function importForPeriod(Request $request, $periodId)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $path = $request->file('file')->getRealPath();
        $spreadsheet = IOFactory::load($path);

        DB::beginTransaction();
        try {
            // For each sheet, find or create category by sheet title
            $sheetIndex = 0;
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $title = $sheet->getTitle();
                $sheetOrder = $sheetIndex + 1; // sheet 1 => order 1

                $category = Category::firstOrCreate(
                    ['period_id' => $periodId, 'name' => $title],
                    ['order' => $sheetOrder]
                );

                // Ensure existing category order matches sheet order
                if ($category->order != $sheetOrder) {
                    $category->order = $sheetOrder;
                    $category->save();
                }

                $rows = $sheet->toArray(null, true, true, true);
                // skip header row; rows are A..E
                foreach ($rows as $idx => $row) {
                    if ($idx == 1) continue; // header
                    $order = $row['A'];
                    $questionText = $row['B'];
                    $type = $row['C'];
                    $optionsText = $row['D'];
                    $answerKey = $row['E'];

                    if (empty($questionText)) continue;

                    $question = Question::create([
                        'category_id' => $category->id,
                        'question' => $questionText,
                        'order' => $order ?: 0,
                        'type' => $type === 'options' ? 'options' : 'input',
                        'grade' => 10
                    ]);

                    if ($question->type === 'options' && $optionsText) {
                        $parts = array_map('trim', explode(',', $optionsText));
                        $createdOptions = [];
                        foreach ($parts as $i => $part) {
                            // strip leading 'A. ' if present
                            $clean = preg_replace('/^[A-Z]\.\s*/', '', $part);
                            $opt = QuestionOption::create([
                                'question_id' => $question->id,
                                'option' => $clean,
                                'order' => $i + 1
                            ]);
                            $createdOptions[] = $opt;
                        }

                        // determine answer key if provided
                        if ($answerKey) {
                            // allow letter (A) or number (1)
                            $answerKey = trim($answerKey);
                            if (ctype_alpha($answerKey)) {
                                $idx = ord(strtoupper($answerKey)) - 65; // 0-based
                            } else {
                                $idx = intval($answerKey) - 1;
                            }

                            if (isset($createdOptions[$idx])) {
                                $opt = $createdOptions[$idx];
                                AnswerKey::create([
                                    'question_id' => $question->id,
                                    'question_option_id' => $opt->id,
                                    'key' => (string)$opt->id,
                                ]);
                            }
                        }
                    } else {
                        // input type
                        AnswerKey::create([
                            'question_id' => $question->id,
                            'question_option_id' => null,
                            'key' => $answerKey
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Import berhasil');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Export an import-template XLSX for a period. Each category is a sheet and the Type column has a dropdown (options,input).
     */
    public function exportTemplate($periodId)
    {
        // Create a single, period-independent template sheet that explains the import format.
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('IMPORT_TEMPLATE');
        $sheet->fromArray(['Order','Question','Type (options/input)','Options (comma-separated)','Answer Key (A/B/1/2)'], null, 'A1');

        // Example rows to show how to fill
        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'Example multiple-choice question');
        $sheet->setCellValue('C2', 'options');
        $sheet->setCellValue('D2', 'A. Resistor, B. Transformator, C. Dioda, D. Kapasitor');
        $sheet->setCellValue('E2', 'B');

        $sheet->setCellValue('A3', 1);
        $sheet->setCellValue('B3', 'Example short-answer question');
        $sheet->setCellValue('C3', 'input');
        $sheet->setCellValue('D3', '');
        $sheet->setCellValue('E3', 'photosynthesis');

        // provide empty rows for template (e.g., 100 rows)
        $maxRows = 100;
        for ($r = 4; $r <= $maxRows; $r++) {
            $sheet->setCellValue("A{$r}", '');
            $sheet->setCellValue("B{$r}", '');
            $sheet->setCellValue("C{$r}", 'options');
            $sheet->setCellValue("D{$r}", '');
            $sheet->setCellValue("E{$r}", '');

            // add data validation dropdown for Type column (C)
            $validation = $sheet->getCell("C{$r}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setPromptTitle('Type');
            $validation->setPrompt('Choose question type: options or input');
            $validation->setFormula1('"options,input"');
        }

        foreach (range('A','E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'questions_import_template.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
