<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use Illuminate\Http\Request;

class FiscalYearController extends Controller
{
    public function index()
    {
        return response()->json(FiscalYear::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_closed' => 'boolean',
        ]);

        $fiscalYear = FiscalYear::create($validated);
        return response()->json($fiscalYear, 201);
    }

    public function show($id)
    {
        return response()->json(FiscalYear::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $fiscalYear = FiscalYear::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_closed' => 'boolean',
        ]);

        $fiscalYear->update($validated);
        return response()->json($fiscalYear);
    }
}
