<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Period;
use App\Models\Category;
use App\Models\Question;

class DashboardController extends Controller
{
    public function index()
    {
        $totalParticipants = User::where('role', 2)->count();
        $totalPeriods = Period::count();
        $totalCategories = Category::count();
        $totalQuestions = Question::count();
        
        $activePeriods = Period::where('status', true)->get();

        return view('admin.dashboard', compact(
            'totalParticipants',
            'totalPeriods',
            'totalCategories',
            'totalQuestions',
            'activePeriods'
        ));
    }
}
