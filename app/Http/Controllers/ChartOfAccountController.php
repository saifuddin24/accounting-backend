<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = ChartOfAccount::orderBy('code')
            ->get();

        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'normal_balance' => 'required|in:debit,credit',
            'is_restricted' => 'nullable|boolean',
        ]);

        $validated['company_id'] = config('app.company_id');

        $account = ChartOfAccount::create($validated);
        return response()->json($account, 201);
    }
}
