<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Company;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    protected $service;

    public function __construct(JournalEntryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'date');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 20);

        $query = JournalEntry::with('items.account');

        if ($search) {
             $query->where(function($q) use ($search) {
                 $q->where('description', 'like', "%{$search}%")
                   ->orWhere('entry_number', 'like', "%{$search}%");
             });
        }
        
        // Allowed sort columns
        if (in_array($sortBy, ['date', 'total_amount', 'entry_number', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }
        
        $query->latest('id');

        $entries = $query->paginate($perPage);

        return response()->json($entries);
    }

    public function getLines(Request $request)
    {
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'date');
        $sortOrder = $request->query('sort_order', 'desc');
        $perPage = $request->query('per_page', 20);

        // Join with Journal Entries to get Date and Entry Number
        $query = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_items.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.company_id', config('app.company_id'))
            ->select(
                'journal_items.*',
                'journal_entries.date',
                'journal_entries.entry_number',
                'journal_entries.description as entry_description',
                'chart_of_accounts.name as account_name',
                'chart_of_accounts.code as account_code'
            );

        if ($search) {
             $query->where(function($q) use ($search) {
                 $q->where('journal_entries.description', 'like', "%{$search}%")
                   ->orWhere('journal_entries.entry_number', 'like', "%{$search}%")
                   ->orWhere('chart_of_accounts.name', 'like', "%{$search}%");
             });
        }

        // Sorting
        if ($sortBy === 'date') {
            $query->orderBy('journal_entries.date', $sortOrder)
                  ->orderBy('journal_entries.id', 'desc'); // Keep lines together
        } elseif ($sortBy === 'amount') {
            // Sort by the max of debit or credit effectively
             $query->orderBy(DB::raw('GREATEST(journal_items.debit, journal_items.credit)'), $sortOrder);
        } else {
            $query->orderBy('journal_entries.date', 'desc')
                  ->orderBy('journal_entries.id', 'desc');
        }

        $lines = $query->paginate($perPage);
        return response()->json($lines);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.debit' => 'nullable|numeric|min:0',
            'items.*.credit' => 'nullable|numeric|min:0',
        ]);

        $validated['company_id'] = config('app.company_id');

        try {
            $entry = $this->service->createEntry($validated);
            return response()->json($entry, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $entry = JournalEntry::with('items.account')->findOrFail($id);
        return response()->json($entry);
    }
}
