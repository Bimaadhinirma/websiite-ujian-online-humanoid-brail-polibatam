<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Period;
use App\Models\UserAnswer;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Export results for a period into Excel (PhpSpreadsheet)
     * Sheet1 = Total scores, subsequent sheets = per category
     */
    public function exportExcel($periodId)
    {
        $period = Period::with(['categories.questions'])->findOrFail($periodId);

        $userAnswers = UserAnswer::where('period_id', $periodId)
            ->with(['user', 'answerItems.question.category'])
            ->get();

        $spreadsheet = new Spreadsheet();

        // Sheet 1: Total scores
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Total Scores');
        $sheet->fromArray(['Participant', 'Username', 'Total Earned', 'Total Possible', 'Percentage'], null, 'A1');

    $row = 2;
        foreach ($userAnswers as $ua) {
            $d = $ua->getDetailedResult();
            $sheet->setCellValue("A{$row}", $ua->user->nama);
            $sheet->setCellValue("B{$row}", $ua->user->username);
            $sheet->setCellValue("C{$row}", $d['overall']['earned']);
            $sheet->setCellValue("D{$row}", $d['overall']['total']);
            $sheet->setCellValue("E{$row}", round($d['overall']['percentage'], 2));
            $row++;
        }

        // Auto-size columns for the total sheet (A..E)
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Additional sheets per category
        foreach ($period->categories as $index => $category) {
            $sheetIndex = $index + 1; // since 0 is active sheet
            $newSheet = $spreadsheet->createSheet($sheetIndex);
            $newSheet->setTitle(substr($category->name, 0, 30)); // sheet title max length
            $newSheet->fromArray(['Participant', 'Username', 'Earned', 'Total', 'Percentage'], null, 'A1');

            $r = 2;
            foreach ($userAnswers as $ua) {
                $d = $ua->getDetailedResult();
                // find this category grade in by_category
                $catGrade = collect($d['by_category'])->firstWhere('category_id', $category->id);
                if (!$catGrade) {
                    $earned = 0; $total = 0; $percent = 0;
                } else {
                    $earned = $catGrade['earned_grade'];
                    $total = $catGrade['total_grade'];
                    $percent = round($catGrade['percentage'], 2);
                }

                $newSheet->setCellValue("A{$r}", $ua->user->nama);
                $newSheet->setCellValue("B{$r}", $ua->user->username);
                $newSheet->setCellValue("C{$r}", $earned);
                $newSheet->setCellValue("D{$r}", $total);
                $newSheet->setCellValue("E{$r}", $percent);
                $r++;
            }

            // Auto-size columns for this category sheet (A..E)
            foreach (range('A', 'E') as $col) {
                $newSheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // Create writer and stream response
        $writer = new Xlsx($spreadsheet);

        $fileName = 'results_period_' . $periodId . '_' . date('Ymd_His') . '.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $disposition = 'attachment; filename="' . $fileName . '"';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
