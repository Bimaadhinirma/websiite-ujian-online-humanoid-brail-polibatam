<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Period;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $periodId = $request->get('period_id');
        $categories = Category::with('period')
            ->when($periodId, function($query, $periodId) {
                return $query->where('period_id', $periodId);
            })
            ->latest()
            ->get();
        $periods = Period::all();
        return view('admin.categories.index', compact('categories', 'periods', 'periodId'));
    }

    public function create()
    {
        $periods = Period::all();
        return view('admin.categories.create', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:periods,id',
            'name' => 'required|string',
            'order' => 'required|integer',
            'descriptions' => 'nullable|string',
        ]);

        $category = Category::create($request->all());

        return redirect()->route('admin.periods.show', $category->period_id)->with('success', 'Kategori berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $category = Category::with('questions')->findOrFail($id);
        return view('admin.categories.show', compact('category'));
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        $periods = Period::all();
        return view('admin.categories.edit', compact('category', 'periods'));
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'period_id' => 'required|exists:periods,id',
            'name' => 'required|string',
            'order' => 'required|integer',
            'descriptions' => 'nullable|string',
        ]);

        $category->update($request->all());

        return redirect()->route('admin.periods.show', $category->period_id)->with('success', 'Kategori berhasil diupdate');
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $periodId = $category->period_id;
        $category->delete();

        return redirect()->route('admin.periods.show', $periodId)->with('success', 'Kategori berhasil dihapus');
    }
}
