<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Period;

class PeriodController extends Controller
{
    public function index()
    {
        $periods = Period::latest()->get();
        return view('admin.periods.index', compact('periods'));
    }

    public function create()
    {
        return view('admin.periods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'status' => 'required|boolean',
            'is_random_questions' => 'nullable|boolean',
            'is_random_options' => 'nullable|boolean',
            'duration_minutes' => 'nullable|integer|min:1',
            'exam_password' => 'nullable|string',
        ]);

        $data = $request->only(['name','status','show_grade','show_result','duration_minutes','is_random_questions','is_random_options']);
        if ($request->filled('exam_password')) {
            $data['exam_password'] = $request->input('exam_password');
        }

        Period::create($data);

        return redirect()->route('admin.periods.index')->with('success', 'Periode berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $period = Period::with('categories.questions')->findOrFail($id);
        return view('admin.periods.show', compact('period'));
    }

    public function edit(string $id)
    {
        $period = Period::findOrFail($id);
        return view('admin.periods.edit', compact('period'));
    }

    public function update(Request $request, string $id)
    {
        $period = Period::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'status' => 'required|boolean',
            'is_random_questions' => 'nullable|boolean',
            'is_random_options' => 'nullable|boolean',
            'duration_minutes' => 'nullable|integer|min:1',
            'exam_password' => 'nullable|string',
        ]);

        $data = $request->only(['name','status','show_grade','show_result','duration_minutes','is_random_questions','is_random_options']);
        if ($request->filled('exam_password')) {
            $data['exam_password'] = $request->input('exam_password');
        }

        $period->update($data);

        return redirect()->route('admin.periods.index')->with('success', 'Periode berhasil diupdate');
    }

    public function destroy(string $id)
    {
        $period = Period::findOrFail($id);
        $period->delete();

        return redirect()->route('admin.periods.index')->with('success', 'Periode berhasil dihapus');
    }
}
