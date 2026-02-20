<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        return response()->json(Profile::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'currency_code' => 'required|string|size:3',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $company = Profile::create($validated);
        return response()->json($company, 201);
    }

    public function show($id)
    {
        return response()->json(Profile::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $company = Profile::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'currency_code' => 'required|string|size:3',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $company->update($validated);
        return response()->json($company);
    }
}
