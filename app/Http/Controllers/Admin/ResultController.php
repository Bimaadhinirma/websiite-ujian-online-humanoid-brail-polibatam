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
                'status' => $userAnswer->status,
                'elapsed_seconds' => $userAnswer->elapsed_seconds,
                'ended_at' => $userAnswer->ended_at,
            ];
        }

        // Sort results by overall percentage (highest first). If equal, sort by earned points.
        usort($results, function ($a, $b) {
            $pa = isset($a['overall']['percentage']) ? floatval($a['overall']['percentage']) : 0;
            $pb = isset($b['overall']['percentage']) ? floatval($b['overall']['percentage']) : 0;
            if ($pa === $pb) {
                $ea = isset($a['overall']['earned']) ? floatval($a['overall']['earned']) : 0;
                $eb = isset($b['overall']['earned']) ? floatval($b['overall']['earned']) : 0;
                return $eb <=> $ea;
            }
            return $pb <=> $pa;
        });

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
        // Add elapsed seconds and human-readable elapsed columns
        $sheet->fromArray(['Participant', 'Username', 'Elapsed Seconds', 'Elapsed (H:i:s)', 'Total Earned', 'Total Possible', 'Percentage'], null, 'A1');

        // Prepare rows and sort by overall percentage (highest first)
        $rows = [];
        foreach ($userAnswers as $ua) {
            $d = $ua->getDetailedResult();
            $rows[] = ['ua' => $ua, 'd' => $d];
        }

        usort($rows, function ($a, $b) {
            $pa = isset($a['d']['overall']['percentage']) ? floatval($a['d']['overall']['percentage']) : 0;
            $pb = isset($b['d']['overall']['percentage']) ? floatval($b['d']['overall']['percentage']) : 0;
            if ($pa === $pb) {
                $ea = isset($a['d']['overall']['earned']) ? floatval($a['d']['overall']['earned']) : 0;
                $eb = isset($b['d']['overall']['earned']) ? floatval($b['d']['overall']['earned']) : 0;
                return $eb <=> $ea;
            }
            return $pb <=> $pa;
        });

        $row = 2;
        foreach ($rows as $r) {
            $ua = $r['ua'];
            $d = $r['d'];
            $elapsed = $ua->elapsed_seconds;
            $elapsedHuman = ($elapsed !== null && intval($elapsed) >= 0) ? gmdate('H:i:s', intval($elapsed)) : '';

            $sheet->setCellValue("A{$row}", $ua->user->nama);
            $sheet->setCellValue("B{$row}", $ua->user->username);
            $sheet->setCellValue("C{$row}", $elapsed !== null ? intval($elapsed) : '');
            $sheet->setCellValue("D{$row}", $elapsedHuman);
            $sheet->setCellValue("E{$row}", $d['overall']['earned']);
            $sheet->setCellValue("F{$row}", $d['overall']['total']);
            $sheet->setCellValue("G{$row}", round($d['overall']['percentage'], 2));
            $row++;
        }

        // Auto-size columns for the total sheet (A..G)
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Additional sheets per category
        foreach ($period->categories as $index => $category) {
            $sheetIndex = $index + 1; // since 0 is active sheet
            $newSheet = $spreadsheet->createSheet($sheetIndex);
            $newSheet->setTitle(substr($category->name, 0, 30)); // sheet title max length
            // include elapsed columns for reference
            $newSheet->fromArray(['Participant', 'Username', 'Elapsed Seconds', 'Elapsed (H:i:s)', 'Earned', 'Total', 'Percentage'], null, 'A1');

            $r = 2;
            // Use the same sorted $rows order so Excel matches the UI ordering
            foreach ($rows as $rowData) {
                $ua = $rowData['ua'];
                $d = $rowData['d'];
                // find this category grade in by_category
                $catGrade = collect($d['by_category'])->firstWhere('category_id', $category->id);
                if (!$catGrade) {
                    $earned = 0; $total = 0; $percent = 0;
                } else {
                    $earned = $catGrade['earned_grade'];
                    $total = $catGrade['total_grade'];
                    $percent = round($catGrade['percentage'], 2);
                }

                $elapsed = $ua->elapsed_seconds;
                $elapsedHuman = ($elapsed !== null && intval($elapsed) >= 0) ? gmdate('H:i:s', intval($elapsed)) : '';

                $newSheet->setCellValue("A{$r}", $ua->user->nama);
                $newSheet->setCellValue("B{$r}", $ua->user->username);
                $newSheet->setCellValue("C{$r}", $elapsed !== null ? intval($elapsed) : '');
                $newSheet->setCellValue("D{$r}", $elapsedHuman);
                $newSheet->setCellValue("E{$r}", $earned);
                $newSheet->setCellValue("F{$r}", $total);
                $newSheet->setCellValue("G{$r}", $percent);
                $r++;
            }

            // Auto-size columns for this category sheet (A..G)
            foreach (range('A', 'G') as $col) {
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
